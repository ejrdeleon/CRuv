import 'dart:async';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:geolocator/geolocator.dart';
import 'package:image_picker/image_picker.dart';
import 'package:latlong2/latlong.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_theme.dart';
import '../../auth/presentation/auth_provider.dart';
import '../domain/report_models.dart';
import 'report_detail_screen.dart';
import 'report_provider.dart';
import 'widgets/report_photo_picker.dart';

/// Official 26 barangays of Santa Cruz, Laguna, Philippines.
const List<String> kSantaCruzBarangays = [
  'Alipit',
  'Bagumbayan',
  'Bubukal',
  'Calios',
  'Duhat',
  'Gatid',
  'Jasaan',
  'Labuin',
  'Malinao',
  'Oogong',
  'Pagsawitan',
  'Palasan',
  'Patimbao',
  'Poblacion I',
  'Poblacion II',
  'Poblacion III',
  'Poblacion IV',
  'Poblacion V',
  'San Jose',
  'San Juan',
  'San Pablo Norte',
  'San Pablo Sur',
  'Santisima Cruz',
  'Santo Angel Central',
  'Santo Angel Norte',
  'Santo Angel Sur',
];

/// Opens the progressive-disclosure "Report an Issue" bottom sheet over the current screen.
Future<void> showReportIssueBottomSheet(
  BuildContext context, {
  CategoryModel? initialCategory,
}) async {
  HapticFeedback.mediumImpact();
  final isWide = MediaQuery.sizeOf(context).width >= 640;

  await showModalBottomSheet<void>(
    context: context,
    isScrollControlled: true,
    useSafeArea: true,
    constraints: BoxConstraints(
      maxWidth: isWide ? 620 : double.infinity,
    ),
    backgroundColor: Colors.transparent,
    builder: (ctx) => ReportFormBottomSheet(initialCategory: initialCategory),
  );
}

/// Standalone screen wrapper if navigated to directly.
class ReportFormScreen extends StatelessWidget {
  final CategoryModel? initialCategory;

  const ReportFormScreen({super.key, this.initialCategory});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Report an Issue')),
      body: SafeArea(
        child: ReportFormBottomSheet(
          initialCategory: initialCategory,
          isFullScreen: true,
        ),
      ),
    );
  }
}

class ReportFormBottomSheet extends StatefulWidget {
  final CategoryModel? initialCategory;
  final bool isFullScreen;
  final bool disableMapTilesForTest;

  const ReportFormBottomSheet({
    super.key,
    this.initialCategory,
    this.isFullScreen = false,
    this.disableMapTilesForTest = false,
  });

  @override
  State<ReportFormBottomSheet> createState() => _ReportFormBottomSheetState();
}

class _ReportFormBottomSheetState extends State<ReportFormBottomSheet> {
  final _formKey = GlobalKey<FormState>();
  final _mapController = MapController();
  final _imagePicker = ImagePicker();

  late final TextEditingController _titleController;
  late final TextEditingController _descriptionController;
  late final TextEditingController _addressController;
  late final TextEditingController _landmarkController;

  // Scoped ValueNotifier so moving the pin never rebuilds the entire form or text inputs
  late final ValueNotifier<LatLng> _pinnedLocationNotifier;

  String _selectedBarangay = 'Poblacion I';
  int? _selectedCategoryId;
  String _selectedPriority = 'medium';
  bool _hazardAcknowledged = false;
  File? _selectedPhoto;
  bool _isLocatingGps = false;
  bool _mapReady = false;
  String? _locationNotice;
  bool _showLocationSettingsShortcut = false;

  @override
  void initState() {
    super.initState();
    final user = context.read<AuthProvider>().user;
    final reportProvider = context.read<ReportProvider>();
    final draft = reportProvider.draft;

    // Seed draft defaults from authenticated user if draft is fresh
    if (!draft.hasEnteredData) {
      if (user?.barangay != null &&
          kSantaCruzBarangays.contains(user!.barangay)) {
        draft.barangay = user.barangay!;
      }
      if (user?.address != null &&
          user!.address!.isNotEmpty &&
          draft.addressText.isEmpty) {
        draft.addressText = user.address!;
      }
    }

    if (widget.initialCategory != null) {
      draft.categoryId = widget.initialCategory!.id;
      draft.priority = widget.initialCategory!.defaultPriority;
    }

    _selectedCategoryId = draft.categoryId;
    _selectedPriority = draft.priority;
    _selectedBarangay = kSantaCruzBarangays.contains(draft.barangay)
        ? draft.barangay
        : 'Poblacion I';
    _hazardAcknowledged = draft.hazardAcknowledged;
    _selectedPhoto = draft.photo;

    _titleController = TextEditingController(text: draft.title)
      ..addListener(_syncDraftText);
    _descriptionController = TextEditingController(text: draft.description)
      ..addListener(_syncDraftText);
    _addressController = TextEditingController(text: draft.addressText)
      ..addListener(_syncDraftText);
    _landmarkController = TextEditingController(text: draft.landmark)
      ..addListener(_syncDraftText);

    _pinnedLocationNotifier = ValueNotifier<LatLng>(draft.pinnedLocation);

    // Refresh categories from API so newly added admin categories are always available
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted) {
        context.read<ReportProvider>().loadCategories();
      }
    });

    // Lazy-load map after sheet entrance transition for smooth 60fps animation
    if (widget.disableMapTilesForTest) {
      _mapReady = true;
    } else {
      Future<void>.delayed(const Duration(milliseconds: 160), () {
        if (mounted) {
          setState(() {
            _mapReady = true;
          });
        }
      });
    }
  }

  void _syncDraftText() {
    if (!mounted) {
      return;
    }
    final draft = context.read<ReportProvider>().draft;
    draft.title = _titleController.text;
    draft.description = _descriptionController.text;
    draft.addressText = _addressController.text;
    draft.landmark = _landmarkController.text;
  }

  @override
  void dispose() {
    _titleController.dispose();
    _descriptionController.dispose();
    _addressController.dispose();
    _landmarkController.dispose();
    _pinnedLocationNotifier.dispose();
    super.dispose();
  }

  CategoryModel? _resolveSelectedCategory(List<CategoryModel> categories) {
    if (_selectedCategoryId == null) {
      return null;
    }
    for (final cat in categories) {
      if (cat.id == _selectedCategoryId) {
        return cat;
      }
    }
    return null;
  }

  Future<bool> _confirmDiscardIfNeeded() async {
    final reportProvider = context.read<ReportProvider>();
    final user = context.read<AuthProvider>().user;
    if (!reportProvider.draft.hasEnteredData) {
      return true;
    }

    final action = await showDialog<String>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Unsaved Report Draft'),
        content: const SingleChildScrollView(
          child: Text(
            'Would you like to keep your draft in memory for later, or discard your changes?',
          ),
        ),
        actionsOverflowDirection: VerticalDirection.down,
        actionsOverflowButtonSpacing: 6,
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop('keep'),
            child: const Text('Keep Draft & Close'),
          ),
          TextButton(
            onPressed: () => Navigator.of(ctx).pop('cancel'),
            child: const Text('Continue Editing'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: AppTheme.statusHazardDot,
            ),
            onPressed: () => Navigator.of(ctx).pop('discard'),
            child: const Text('Discard'),
          ),
        ],
      ),
    );

    if (action == 'discard') {
      reportProvider.clearDraft(
        defaultBarangay: user?.barangay,
        defaultAddress: user?.address,
      );
      return true;
    }

    return action == 'keep';
  }

  void _updatePinnedLocation(LatLng point) {
    HapticFeedback.selectionClick();
    _pinnedLocationNotifier.value = point;
    final draft = context.read<ReportProvider>().draft;
    draft.pinnedLocation = point;
    draft.locationManuallyChanged = true;
  }

  Future<void> _useCurrentGpsLocation() async {
    setState(() {
      _isLocatingGps = true;
      _locationNotice = null;
      _showLocationSettingsShortcut = false;
    });

    try {
      final serviceEnabled = await Geolocator.isLocationServiceEnabled();
      if (!serviceEnabled) {
        setState(() {
          _locationNotice =
              'GPS is turned off. Turn on Location Services or tap the map to place the pin manually.';
          _showLocationSettingsShortcut = true;
        });
        return;
      }

      var permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
      }

      if (permission == LocationPermission.denied ||
          permission == LocationPermission.deniedForever) {
        setState(() {
          _locationNotice =
              'Location permission denied. Open Settings to allow GPS, or place the pin manually on the map.';
          _showLocationSettingsShortcut = true;
        });
        return;
      }

      final position = await Geolocator.getCurrentPosition(
        locationSettings: const LocationSettings(
          accuracy: LocationAccuracy.high,
          timeLimit: Duration(seconds: 8),
        ),
      );

      final updatedLatLng = LatLng(position.latitude, position.longitude);
      if (mounted) {
        _updatePinnedLocation(updatedLatLng);
        if (_mapReady && !widget.disableMapTilesForTest) {
          _mapController.move(updatedLatLng, 16.5);
        }
      }
    } on TimeoutException {
      if (mounted) {
        setState(() {
          _locationNotice =
              'GPS signal timed out. You can retry or tap the map to pin the location manually.';
        });
      }
    } catch (_) {
      if (mounted) {
        setState(() {
          _locationNotice =
              'Could not acquire GPS fix. Tap the map to place the pin manually.';
        });
      }
    } finally {
      if (mounted) {
        setState(() {
          _isLocatingGps = false;
        });
      }
    }
  }

  Future<void> _submitReport(List<CategoryModel> categories) async {
    final reportProvider = context.read<ReportProvider>();
    if (reportProvider.isSubmitting) {
      return;
    }

    final selectedCategory = _resolveSelectedCategory(categories);
    if (selectedCategory == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please select an issue category.'),
          backgroundColor: AppTheme.statusHazardDot,
        ),
      );
      return;
    }

    if (!_formKey.currentState!.validate()) {
      return;
    }

    if (selectedCategory.isHazardous && !_hazardAcknowledged) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please acknowledge the safety advisory first.'),
          backgroundColor: AppTheme.statusHazardDot,
        ),
      );
      return;
    }

    final authProvider = context.read<AuthProvider>();
    final coords = _pinnedLocationNotifier.value;

    final created = await reportProvider.submitReport(
      categoryId: selectedCategory.id,
      title: _titleController.text.trim(),
      description: _descriptionController.text.trim(),
      priority: _selectedPriority,
      latitude: coords.latitude,
      longitude: coords.longitude,
      addressText: _addressController.text.trim(),
      barangay: _selectedBarangay,
      landmark: _landmarkController.text.trim(),
      isHazardWarningAcknowledged: _hazardAcknowledged,
      photo: _selectedPhoto,
    );

    if (!mounted) {
      return;
    }

    if (created != null) {
      HapticFeedback.heavyImpact();
      reportProvider.clearDraft(
        defaultBarangay: authProvider.user?.barangay,
        defaultAddress: authProvider.user?.address,
      );
      await authProvider.refreshProfile();
      if (!mounted) {
        return;
      }
      final navigator = Navigator.of(context);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            'Report ${created.referenceNumber} submitted to Santa Cruz Operations.',
          ),
          backgroundColor: AppTheme.statusResolvedDot,
        ),
      );
      navigator.pop();
      navigator.push(
        MaterialPageRoute(
          builder: (_) => ReportDetailScreen(reportId: created.id),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final bottomInset = MediaQuery.viewInsetsOf(context).bottom;

    Widget buildResponsiveForm(ScrollController? scrollController) {
      return SafeArea(
        top: false,
        child: LayoutBuilder(
          builder: (context, constraints) {
            final isWide = constraints.maxWidth >= 600;
            final horizontalPadding = isWide
                ? (constraints.maxWidth - 560) / 2
                : AppTheme.s16;

            return Selector<ReportProvider,
                ({
                  List<CategoryModel> categories,
                  bool isLoadingCategories,
                  String? categoriesError,
                  bool isSubmitting,
                  String? errorMessage,
                })>(
              selector: (_, p) => (
                categories: p.categories,
                isLoadingCategories: p.isLoadingCategories,
                categoriesError: p.categoriesError,
                isSubmitting: p.isSubmitting,
                errorMessage: p.errorMessage,
              ),
              builder: (context, state, _) {
                final selectedCategory =
                    _resolveSelectedCategory(state.categories);

                return Form(
                  key: _formKey,
                  child: SingleChildScrollView(
                    controller: scrollController,
                    keyboardDismissBehavior:
                        ScrollViewKeyboardDismissBehavior.onDrag,
                    padding: EdgeInsets.fromLTRB(
                      horizontalPadding,
                      AppTheme.s12,
                      horizontalPadding,
                      AppTheme.s24 + bottomInset,
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                      if (!widget.isFullScreen) ...[
                        Center(
                          child: Container(
                            width: 40,
                            height: 5,
                            margin: const EdgeInsets.only(bottom: 10),
                            decoration: BoxDecoration(
                              color: isDark
                                  ? AppTheme.slate700
                                  : AppTheme.slate300,
                              borderRadius: BorderRadius.circular(3),
                            ),
                          ),
                        ),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Expanded(
                              child: Text(
                                'Report an Issue',
                                style: Theme.of(context).textTheme.titleLarge,
                              ),
                            ),
                            IconButton(
                              tooltip: 'Close',
                              onPressed: state.isSubmitting
                                  ? null
                                  : () async {
                                      if (await _confirmDiscardIfNeeded() &&
                                          context.mounted) {
                                        Navigator.of(context).pop();
                                      }
                                    },
                              icon: const Icon(Icons.close),
                            ),
                          ],
                        ),
                        const SizedBox(height: AppTheme.s8),
                      ],

                      // Upload progress bar when submitting
                      if (state.isSubmitting) ...[
                        const LinearProgressIndicator(),
                        const SizedBox(height: AppTheme.s8),
                        Text(
                          'Uploading report and compressing photo...',
                          style: Theme.of(context).textTheme.bodySmall,
                        ),
                        const SizedBox(height: AppTheme.s12),
                      ],

                      // Retry banner on network / server failure (draft stays intact)
                      if (state.errorMessage != null &&
                          !state.isSubmitting) ...[
                        Container(
                          padding: const EdgeInsets.all(AppTheme.s12),
                          margin: const EdgeInsets.only(bottom: AppTheme.s16),
                          decoration: BoxDecoration(
                            color: isDark
                                ? AppTheme.statusHazardBgDark
                                : AppTheme.statusHazardBgLight,
                            borderRadius: BorderRadius.circular(AppTheme.r8),
                          ),
                          child: Row(
                            children: [
                              const Icon(
                                Icons.error_outline,
                                color: AppTheme.statusHazardDot,
                                size: 20,
                              ),
                              const SizedBox(width: 10),
                              Expanded(
                                child: Text(
                                  state.errorMessage!,
                                  style: TextStyle(
                                    fontSize: 12.5,
                                    fontWeight: FontWeight.w600,
                                    color: isDark
                                        ? AppTheme.statusHazardTextDark
                                        : AppTheme.statusHazardTextLight,
                                  ),
                                ),
                              ),
                              TextButton(
                                onPressed: () =>
                                    _submitReport(state.categories),
                                child: const Text('Retry'),
                              ),
                            ],
                          ),
                        ),
                      ],

                      // STEP 1: Photo Capture (Dedicated, accessible, thumb-friendly picker)
                      ReportPhotoPicker(
                        photo: _selectedPhoto,
                        isSubmitting: state.isSubmitting,
                        imagePicker: _imagePicker,
                        onPhotoChanged: (newPhoto) {
                          setState(() {
                            _selectedPhoto = newPhoto;
                            context.read<ReportProvider>().draft.photo =
                                newPhoto;
                          });
                        },
                      ),
                      const SizedBox(height: AppTheme.s20),

                      // STEP 2: Category Chips
                      Text(
                        '2. Select Category',
                        style: Theme.of(context).textTheme.titleMedium,
                      ),
                      const SizedBox(height: 8),
                      if (state.categories.isEmpty)
                        state.categoriesError != null
                            ? Row(
                                children: [
                                  Expanded(
                                    child: Text(
                                      state.categoriesError!,
                                      style: const TextStyle(
                                        fontSize: 13,
                                        color: AppTheme.statusHazardDot,
                                      ),
                                    ),
                                  ),
                                  TextButton.icon(
                                    onPressed: () => context
                                        .read<ReportProvider>()
                                        .loadCategories(),
                                    icon: const Icon(Icons.refresh, size: 16),
                                    label: const Text('Retry'),
                                  ),
                                ],
                              )
                            : const Padding(
                                padding: EdgeInsets.symmetric(vertical: 12),
                                child: LinearProgressIndicator(),
                              )
                      else
                        Wrap(
                          spacing: 8,
                          runSpacing: 8,
                          children: state.categories.map((cat) {
                            final isSelected = _selectedCategoryId == cat.id;
                            return ChoiceChip(
                              label: Text(cat.name),
                              selected: isSelected,
                              onSelected: state.isSubmitting
                                  ? null
                                  : (_) {
                                      HapticFeedback.selectionClick();
                                      final draft = context
                                          .read<ReportProvider>()
                                          .draft;
                                      setState(() {
                                        _selectedCategoryId = cat.id;
                                        _selectedPriority = cat.defaultPriority;
                                        _hazardAcknowledged = false;
                                        draft.categoryId = cat.id;
                                        draft.priority = cat.defaultPriority;
                                        draft.hazardAcknowledged = false;
                                      });
                                    },
                              selectedColor: AppTheme.primaryTealDark
                                  .withValues(alpha: 0.16),
                              labelStyle: TextStyle(
                                fontFamily: AppTheme.fontFamily,
                                fontWeight: isSelected
                                    ? FontWeight.w700
                                    : FontWeight.w500,
                                color: isSelected
                                    ? (isDark
                                        ? Colors.white
                                        : AppTheme.primaryTealDark)
                                    : null,
                              ),
                            );
                          }).toList(),
                        ),

                      if (selectedCategory?.isHazardous == true) ...[
                        const SizedBox(height: AppTheme.s12),
                        Container(
                          padding: const EdgeInsets.all(AppTheme.s12),
                          decoration: BoxDecoration(
                            color: isDark
                                ? AppTheme.statusHazardBgDark
                                : AppTheme.statusHazardBgLight,
                            borderRadius: BorderRadius.circular(AppTheme.r8),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  Container(
                                    width: 8,
                                    height: 8,
                                    decoration: const BoxDecoration(
                                      color: AppTheme.statusHazardDot,
                                      shape: BoxShape.circle,
                                    ),
                                  ),
                                  const SizedBox(width: 8),
                                  Text(
                                    'Critical Hazard Safety Advisory',
                                    style: TextStyle(
                                      fontWeight: FontWeight.w700,
                                      fontSize: 13,
                                      color: isDark
                                          ? AppTheme.statusHazardTextDark
                                          : AppTheme.statusHazardTextLight,
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 6),
                              Text(
                                selectedCategory!.safetyAdvisory ??
                                    'Keep a safe distance from exposed wires, deep floodwaters, or unstable structures.',
                                style: TextStyle(
                                  fontSize: 12.5,
                                  color: isDark
                                      ? AppTheme.statusHazardTextDark
                                      : AppTheme.statusHazardTextLight,
                                ),
                              ),
                              CheckboxListTile(
                                value: _hazardAcknowledged,
                                onChanged: state.isSubmitting
                                    ? null
                                    : (val) {
                                        HapticFeedback.selectionClick();
                                        setState(() {
                                          _hazardAcknowledged = val ?? false;
                                          context
                                              .read<ReportProvider>()
                                              .draft
                                              .hazardAcknowledged =
                                              _hazardAcknowledged;
                                        });
                                      },
                                contentPadding: EdgeInsets.zero,
                                controlAffinity:
                                    ListTileControlAffinity.leading,
                                title: Text(
                                  'I am at a safe distance from this hazard.',
                                  style: TextStyle(
                                    fontSize: 12.5,
                                    fontWeight: FontWeight.w700,
                                    color: isDark
                                        ? AppTheme.statusHazardTextDark
                                        : AppTheme.statusHazardTextLight,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                      const SizedBox(height: AppTheme.s20),

                      // STEP 3: Issue Summary, Description & Priority
                      Text(
                        '3. Issue Details',
                        style: Theme.of(context).textTheme.titleMedium,
                      ),
                      const SizedBox(height: 8),
                      TextFormField(
                        controller: _titleController,
                        enabled: !state.isSubmitting,
                        textInputAction: TextInputAction.next,
                        decoration: const InputDecoration(
                          labelText: 'Short Summary',
                          hintText:
                              'e.g., Fallen electric post blocking P. Guevara Ave',
                        ),
                        validator: (val) {
                          if (val == null || val.trim().length < 5) {
                            return 'Enter at least 5 characters';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: AppTheme.s12),
                      TextFormField(
                        controller: _descriptionController,
                        enabled: !state.isSubmitting,
                        maxLines: 3,
                        decoration: const InputDecoration(
                          labelText: 'Description',
                          hintText:
                              'Describe the damage and any risk to motorists or residents.',
                          alignLabelWithHint: true,
                        ),
                        validator: (val) {
                          if (val == null || val.trim().length < 10) {
                            return 'Enter at least 10 characters of detail';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: AppTheme.s12),
                      Wrap(
                        spacing: 8,
                        runSpacing: 8,
                        children: [
                          ('low', 'Low Priority'),
                          ('medium', 'Medium'),
                          ('high', 'High'),
                          ('urgent', 'Critical Hazard'),
                        ].map((item) {
                          final isSelected = _selectedPriority == item.$1;
                          final token = AppTheme.resolvePriorityToken(
                            item.$1,
                            isDark: isDark,
                          );
                          return ChoiceChip(
                            avatar: Container(
                              width: 8,
                              height: 8,
                              decoration: BoxDecoration(
                                color: token.dot,
                                shape: BoxShape.circle,
                              ),
                            ),
                            label: Text(item.$2),
                            selected: isSelected,
                            onSelected: state.isSubmitting
                                ? null
                                : (_) {
                                    HapticFeedback.selectionClick();
                                    setState(() {
                                      _selectedPriority = item.$1;
                                      context
                                          .read<ReportProvider>()
                                          .draft
                                          .priority = item.$1;
                                    });
                                  },
                          );
                        }).toList(),
                      ),
                      const SizedBox(height: AppTheme.s20),

                      // STEP 4: Location Pin on Map + Santa Cruz Barangay Selector
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  '4. Pin Incident Location',
                                  style:
                                      Theme.of(context).textTheme.titleMedium,
                                ),
                                Text(
                                  'Santa Cruz, Laguna • Tap map to move pin',
                                  style: Theme.of(context).textTheme.bodySmall,
                                ),
                              ],
                            ),
                          ),
                          OutlinedButton.icon(
                            onPressed: (_isLocatingGps || state.isSubmitting)
                                ? null
                                : _useCurrentGpsLocation,
                            icon: _isLocatingGps
                                ? const SizedBox(
                                    width: 14,
                                    height: 14,
                                    child: CircularProgressIndicator(
                                      strokeWidth: 2,
                                    ),
                                  )
                                : const Icon(Icons.my_location, size: 16),
                            label: const Text('Use GPS'),
                          ),
                        ],
                      ),
                      if (_locationNotice != null) ...[
                        const SizedBox(height: 8),
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 12,
                            vertical: 8,
                          ),
                          decoration: BoxDecoration(
                            color: isDark
                                ? AppTheme.statusProgressBgDark
                                : AppTheme.statusProgressBgLight,
                            borderRadius: BorderRadius.circular(AppTheme.r8),
                          ),
                          child: Row(
                            children: [
                              const Icon(
                                Icons.location_off_outlined,
                                size: 18,
                                color: AppTheme.statusProgressDot,
                              ),
                              const SizedBox(width: 8),
                              Expanded(
                                child: Text(
                                  _locationNotice!,
                                  style: TextStyle(
                                    fontSize: 12,
                                    color: isDark
                                        ? AppTheme.statusProgressTextDark
                                        : AppTheme.statusProgressTextLight,
                                  ),
                                ),
                              ),
                              if (_showLocationSettingsShortcut)
                                TextButton(
                                  onPressed: () =>
                                      Geolocator.openLocationSettings(),
                                  child: const Text('Settings'),
                                ),
                            ],
                          ),
                        ),
                      ],
                      const SizedBox(height: 8),
                      ClipRRect(
                        borderRadius: BorderRadius.circular(AppTheme.r10),
                        child: SizedBox(
                          height: 190,
                          child: !_mapReady
                              ? Container(
                                  color: isDark
                                      ? AppTheme.slate800
                                      : AppTheme.slate100,
                                  alignment: Alignment.center,
                                  child: const CircularProgressIndicator(
                                    strokeWidth: 2,
                                  ),
                                )
                              : ValueListenableBuilder<LatLng>(
                                  valueListenable: _pinnedLocationNotifier,
                                  builder: (context, pinnedPos, _) {
                                    if (widget.disableMapTilesForTest) {
                                      return GestureDetector(
                                        key: const Key('test_map_canvas'),
                                        onTap: () {
                                          if (!state.isSubmitting) {
                                            _updatePinnedLocation(
                                              const LatLng(14.2825, 121.4155),
                                            );
                                          }
                                        },
                                        child: Container(
                                          color: AppTheme.slate200,
                                          alignment: Alignment.center,
                                          child: const Icon(
                                            Icons.location_on,
                                            color: AppTheme.statusHazardDot,
                                            size: 40,
                                          ),
                                        ),
                                      );
                                    }
                                    return FlutterMap(
                                      mapController: _mapController,
                                      options: MapOptions(
                                        initialCenter: pinnedPos,
                                        initialZoom: 15.5,
                                        onTap: (tapPosition, point) {
                                          if (!state.isSubmitting) {
                                            _updatePinnedLocation(point);
                                          }
                                        },
                                      ),
                                      children: [
                                        TileLayer(
                                          urlTemplate:
                                              Theme.of(context).brightness == Brightness.dark ? 'https://a.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}.png' : 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                                          userAgentPackageName:
                                              'ph.gov.barangay.community_report_app',
                                        ),
                                        MarkerLayer(
                                          markers: [
                                            Marker(
                                              point: pinnedPos,
                                              width: 44,
                                              height: 44,
                                              child: const Icon(
                                                Icons.location_on,
                                                color:
                                                    AppTheme.statusHazardDot,
                                                size: 40,
                                              ),
                                            ),
                                          ],
                                        ),
                                      ],
                                    );
                                  },
                                ),
                        ),
                      ),
                      const SizedBox(height: 6),
                      ValueListenableBuilder<LatLng>(
                        valueListenable: _pinnedLocationNotifier,
                        builder: (context, pos, _) {
                          return Text(
                            'Pinned Coordinates: ${pos.latitude.toStringAsFixed(5)}, ${pos.longitude.toStringAsFixed(5)}',
                            style: Theme.of(context).textTheme.bodySmall,
                          );
                        },
                      ),
                      const SizedBox(height: AppTheme.s12),

                      // Santa Cruz Barangay Dropdown
                      DropdownButtonFormField<String>(
                        initialValue: _selectedBarangay,
                        isExpanded: true,
                        decoration: const InputDecoration(
                          labelText: 'Barangay (Santa Cruz, Laguna)',
                          prefixIcon: Icon(Icons.location_city_outlined),
                        ),
                        items: kSantaCruzBarangays
                            .map(
                              (b) => DropdownMenuItem<String>(
                                value: b,
                                child: Text(b),
                              ),
                            )
                            .toList(),
                        onChanged: state.isSubmitting
                            ? null
                            : (val) {
                                if (val != null) {
                                  setState(() {
                                    _selectedBarangay = val;
                                    context
                                        .read<ReportProvider>()
                                        .draft
                                        .barangay = val;
                                  });
                                }
                              },
                      ),
                      const SizedBox(height: AppTheme.s12),

                      TextFormField(
                        controller: _addressController,
                        enabled: !state.isSubmitting,
                        textInputAction: TextInputAction.next,
                        decoration: const InputDecoration(
                          labelText: 'Street / Sitio / Purok',
                          hintText:
                              'e.g., P. Guevara Ave near Municipal Plaza',
                          prefixIcon: Icon(Icons.place_outlined),
                        ),
                        validator: (val) {
                          if (val == null || val.trim().isEmpty) {
                            return 'Enter the street or location';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: AppTheme.s12),
                      TextFormField(
                        controller: _landmarkController,
                        enabled: !state.isSubmitting,
                        textInputAction: TextInputAction.done,
                        decoration: const InputDecoration(
                          labelText: 'Nearest Landmark (Optional)',
                          prefixIcon: Icon(Icons.flag_outlined),
                        ),
                      ),
                      const SizedBox(height: AppTheme.s24),

                      // STEP 5: Submit Button (Disabled while sending to prevent double submit)
                      ElevatedButton.icon(
                        key: const Key('submit_report_button'),
                        onPressed: state.isSubmitting
                            ? null
                            : () => _submitReport(state.categories),
                        icon: state.isSubmitting
                            ? const SizedBox(
                                width: 18,
                                height: 18,
                                child: CircularProgressIndicator(
                                  strokeWidth: 2,
                                  color: Colors.white,
                                ),
                              )
                            : const Icon(Icons.send_rounded, size: 18),
                        label: Text(
                          state.isSubmitting
                              ? 'Submitting Report...'
                              : 'Submit Official Report',
                        ),
                      ),
                    ],
                  ),
                ),
                );
              },
            );
          },
        ),
      );
    }

    if (widget.isFullScreen) {
      return buildResponsiveForm(null);
    }

    return PopScope(
      canPop: false,
      onPopInvokedWithResult: (didPop, _) async {
        if (didPop) {
          return;
        }
        if (await _confirmDiscardIfNeeded() && context.mounted) {
          Navigator.of(context).pop();
        }
      },
      child: DraggableScrollableSheet(
        initialChildSize: 0.88,
        minChildSize: 0.55,
        maxChildSize: 0.96,
        expand: false,
        builder: (context, scrollController) {
          return Container(
            decoration: BoxDecoration(
              color: Theme.of(context).scaffoldBackgroundColor,
              borderRadius: const BorderRadius.vertical(
                top: Radius.circular(20),
              ),
            ),
            child: buildResponsiveForm(scrollController),
          );
        },
      ),
    );
  }
}

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

/// Opens the progressive-disclosure "Report an Issue" bottom sheet over the current screen.
Future<void> showReportIssueBottomSheet(
  BuildContext context, {
  CategoryModel? initialCategory,
}) async {
  HapticFeedback.mediumImpact();
  await showModalBottomSheet<void>(
    context: context,
    isScrollControlled: true,
    useSafeArea: true,
    backgroundColor: Colors.transparent,
    builder: (ctx) => ReportFormBottomSheet(initialCategory: initialCategory),
  );
}

/// Standalone screen wrapper if navigated to directly from empty states.
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

  const ReportFormBottomSheet({
    super.key,
    this.initialCategory,
    this.isFullScreen = false,
  });

  @override
  State<ReportFormBottomSheet> createState() => _ReportFormBottomSheetState();
}

class _ReportFormBottomSheetState extends State<ReportFormBottomSheet> {
  final _formKey = GlobalKey<FormState>();
  final _mapController = MapController();
  final _imagePicker = ImagePicker();

  final _titleController = TextEditingController();
  final _descriptionController = TextEditingController();
  final _addressController = TextEditingController();
  final _barangayController = TextEditingController();
  final _landmarkController = TextEditingController();

  CategoryModel? _selectedCategory;
  String _selectedPriority = 'medium';
  bool _hazardAcknowledged = false;
  File? _selectedPhoto;

  // Santa Cruz, Laguna default coordinates (Municipal Town Hall)
  LatLng _pinnedLocation = const LatLng(14.2816, 121.4146);
  bool _isLocatingGps = false;
  bool _locationManuallyChanged = false;

  @override
  void initState() {
    super.initState();
    final user = context.read<AuthProvider>().user;
    _barangayController.text = user?.barangay ?? 'Poblacion I';
    if (user?.address != null && user!.address!.isNotEmpty) {
      _addressController.text = user.address!;
    }

    final reportProvider = context.read<ReportProvider>();
    if (reportProvider.categories.isEmpty) {
      reportProvider.loadCategories();
    }

    if (widget.initialCategory != null) {
      _selectedCategory = widget.initialCategory;
      _selectedPriority = widget.initialCategory!.defaultPriority;
    }
  }

  @override
  void dispose() {
    _titleController.dispose();
    _descriptionController.dispose();
    _addressController.dispose();
    _barangayController.dispose();
    _landmarkController.dispose();
    super.dispose();
  }

  /// Checks whether the user entered any data before allowing a swipe-down dismiss.
  bool get _hasEnteredData {
    return _selectedPhoto != null ||
        _selectedCategory != null ||
        _titleController.text.trim().isNotEmpty ||
        _descriptionController.text.trim().isNotEmpty ||
        _landmarkController.text.trim().isNotEmpty ||
        _locationManuallyChanged;
  }

  Future<bool> _confirmDiscardIfNeeded() async {
    if (!_hasEnteredData) {
      return true;
    }

    final shouldDiscard = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Discard Report Draft?'),
        content: const Text(
          'You have entered report details. Swiping down will discard your unsaved changes.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(false),
            child: const Text('Keep Editing'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: AppTheme.statusHazardDot,
            ),
            onPressed: () => Navigator.of(ctx).pop(true),
            child: const Text('Discard'),
          ),
        ],
      ),
    );

    return shouldDiscard ?? false;
  }

  Future<void> _pickPhoto(ImageSource source) async {
    try {
      final pickedFile = await _imagePicker.pickImage(
        source: source,
        maxWidth: 1600,
        maxHeight: 1600,
        imageQuality: 85,
      );
      if (pickedFile != null && mounted) {
        HapticFeedback.mediumImpact();
        setState(() {
          _selectedPhoto = File(pickedFile.path);
        });
      }
    } catch (_) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Could not access camera or photo gallery.'),
          ),
        );
      }
    }
  }

  Future<void> _useCurrentGpsLocation() async {
    setState(() {
      _isLocatingGps = true;
    });

    try {
      final serviceEnabled = await Geolocator.isLocationServiceEnabled();
      if (!serviceEnabled) {
        throw Exception('Location services are disabled on this device.');
      }

      var permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
      }

      if (permission == LocationPermission.denied ||
          permission == LocationPermission.deniedForever) {
        throw Exception('Location permission was denied.');
      }

      final position = await Geolocator.getCurrentPosition(
        locationSettings: const LocationSettings(
          accuracy: LocationAccuracy.high,
          timeLimit: Duration(seconds: 10),
        ),
      );

      final updatedLatLng = LatLng(position.latitude, position.longitude);
      if (mounted) {
        HapticFeedback.selectionClick();
        setState(() {
          _pinnedLocation = updatedLatLng;
          _locationManuallyChanged = true;
        });
        _mapController.move(updatedLatLng, 16.5);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(e.toString().replaceFirst('Exception: ', '')),
          ),
        );
      }
    } finally {
      if (mounted) {
        setState(() {
          _isLocatingGps = false;
        });
      }
    }
  }

  Future<void> _submitReport() async {
    if (_selectedCategory == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please select an issue category chip.'),
          backgroundColor: AppTheme.statusHazardDot,
        ),
      );
      return;
    }

    if (!_formKey.currentState!.validate()) {
      return;
    }

    if (_selectedCategory!.isHazardous && !_hazardAcknowledged) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text(
            'Please acknowledge the safety advisory for hazardous issues.',
          ),
          backgroundColor: AppTheme.statusHazardDot,
        ),
      );
      return;
    }

    final reportProvider = context.read<ReportProvider>();
    final authProvider = context.read<AuthProvider>();

    final created = await reportProvider.submitReport(
      categoryId: _selectedCategory!.id,
      title: _titleController.text.trim().isNotEmpty
          ? _titleController.text
          : '${_selectedCategory!.name} at ${_addressController.text}',
      description: _descriptionController.text,
      priority: _selectedPriority,
      latitude: _pinnedLocation.latitude,
      longitude: _pinnedLocation.longitude,
      addressText: _addressController.text,
      barangay: _barangayController.text,
      landmark: _landmarkController.text,
      isHazardWarningAcknowledged: _hazardAcknowledged,
      photo: _selectedPhoto,
    );

    if (!mounted) {
      return;
    }

    if (created != null) {
      HapticFeedback.heavyImpact();
      await authProvider.refreshProfile();
      if (!mounted) {
        return;
      }
      final navigator = Navigator.of(context);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            'Report ${created.referenceNumber} submitted successfully.',
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
    } else if (reportProvider.errorMessage != null) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(reportProvider.errorMessage!),
          backgroundColor: AppTheme.statusHazardDot,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final reportProvider = context.watch<ReportProvider>();
    final categories = reportProvider.categories;
    final isDark = Theme.of(context).brightness == Brightness.dark;

    Widget buildSheetBody(ScrollController? scrollController) {
      return Form(
        key: _formKey,
        child: ListView(
          controller: scrollController,
          padding: const EdgeInsets.fromLTRB(
            AppTheme.s16,
            AppTheme.s8,
            AppTheme.s16,
            AppTheme.s32,
          ),
          children: [
            if (!widget.isFullScreen) ...[
              Center(
                child: Container(
                  width: 40,
                  height: 5,
                  margin: const EdgeInsets.only(bottom: 12),
                  decoration: BoxDecoration(
                    color: isDark ? AppTheme.slate700 : AppTheme.slate300,
                    borderRadius: BorderRadius.circular(3),
                  ),
                ),
              ),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'Report an Issue',
                    style: Theme.of(context).textTheme.titleLarge,
                  ),
                  IconButton(
                    tooltip: 'Close',
                    onPressed: () async {
                      if (await _confirmDiscardIfNeeded() && context.mounted) {
                        Navigator.of(context).pop();
                      }
                    },
                    icon: const Icon(Icons.close),
                  ),
                ],
              ),
              const SizedBox(height: AppTheme.s12),
            ],

            // STEP 1: Photo Capture (Camera or Gallery)
            Text(
              '1. Photo Evidence',
              style: Theme.of(context).textTheme.titleMedium,
            ),
            const SizedBox(height: 6),
            if (_selectedPhoto != null) ...[
              ClipRRect(
                borderRadius: BorderRadius.circular(AppTheme.r10),
                child: Stack(
                  children: [
                    Image.file(
                      _selectedPhoto!,
                      height: 180,
                      width: double.infinity,
                      fit: BoxFit.cover,
                    ),
                    Positioned(
                      top: 8,
                      right: 8,
                      child: IconButton.filled(
                        onPressed: () {
                          HapticFeedback.lightImpact();
                          setState(() {
                            _selectedPhoto = null;
                          });
                        },
                        icon: const Icon(Icons.delete_outline),
                        style: IconButton.styleFrom(
                          backgroundColor: Colors.black87,
                          foregroundColor: Colors.white,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: AppTheme.s8),
            ],
            Row(
              children: [
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () => _pickPhoto(ImageSource.camera),
                    icon: const Icon(Icons.camera_alt_outlined, size: 18),
                    label: const Text('Camera'),
                  ),
                ),
                const SizedBox(width: AppTheme.s8),
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () => _pickPhoto(ImageSource.gallery),
                    icon: const Icon(Icons.photo_library_outlined, size: 18),
                    label: const Text('Gallery'),
                  ),
                ),
              ],
            ),
            const SizedBox(height: AppTheme.s20),

            // STEP 2: Category Chips
            Text(
              '2. Select Category',
              style: Theme.of(context).textTheme.titleMedium,
            ),
            const SizedBox(height: 8),
            if (categories.isEmpty)
              reportProvider.errorMessage != null
                  ? Row(
                      children: [
                        Expanded(
                          child: Text(
                            'Could not load categories: ${reportProvider.errorMessage}',
                            style: const TextStyle(
                              fontSize: 13,
                              color: AppTheme.statusHazardDot,
                            ),
                          ),
                        ),
                        TextButton.icon(
                          onPressed: () => reportProvider.loadCategories(),
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
                children: categories.map((cat) {
                  final isSelected = _selectedCategory?.id == cat.id;
                  return ChoiceChip(
                    label: Text(cat.name),
                    selected: isSelected,
                    onSelected: (_) {
                      HapticFeedback.selectionClick();
                      setState(() {
                        _selectedCategory = cat;
                        _selectedPriority = cat.defaultPriority;
                        _hazardAcknowledged = false;
                      });
                    },
                    selectedColor:
                        AppTheme.primaryTealDark.withValues(alpha: 0.16),
                    labelStyle: TextStyle(
                      fontFamily: AppTheme.fontFamily,
                      fontWeight:
                          isSelected ? FontWeight.w700 : FontWeight.w500,
                      color: isSelected
                          ? (isDark ? Colors.white : AppTheme.primaryTealDark)
                          : null,
                    ),
                  );
                }).toList(),
              ),

            if (_selectedCategory?.isHazardous == true) ...[
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
                      _selectedCategory!.safetyAdvisory ??
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
                      onChanged: (val) {
                        HapticFeedback.selectionClick();
                        setState(() {
                          _hazardAcknowledged = val ?? false;
                        });
                      },
                      contentPadding: EdgeInsets.zero,
                      controlAffinity: ListTileControlAffinity.leading,
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

            // STEP 3: Short Description & Priority
            Text(
              '3. Issue Details',
              style: Theme.of(context).textTheme.titleMedium,
            ),
            const SizedBox(height: 8),
            TextFormField(
              controller: _titleController,
              textInputAction: TextInputAction.next,
              decoration: const InputDecoration(
                labelText: 'Short Summary',
                hintText: 'e.g., Fallen electric post blocking Purok 3 road',
              ),
              validator: (val) {
                if (val == null || val.trim().length < 5) {
                  return 'Enter a short summary (at least 5 characters)';
                }
                return null;
              },
            ),
            const SizedBox(height: AppTheme.s12),
            TextFormField(
              controller: _descriptionController,
              maxLines: 3,
              decoration: const InputDecoration(
                labelText: 'Description',
                hintText:
                    'Describe what happened and any immediate risk to residents.',
                alignLabelWithHint: true,
              ),
              validator: (val) {
                if (val == null || val.trim().length < 15) {
                  return 'Please provide at least 15 characters of detail';
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
                  onSelected: (_) {
                    HapticFeedback.selectionClick();
                    setState(() {
                      _selectedPriority = item.$1;
                    });
                  },
                );
              }).toList(),
            ),
            const SizedBox(height: AppTheme.s20),

            // STEP 4: Location Pin on Map + "Use my location"
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        '4. Pin Incident Location',
                        style: Theme.of(context).textTheme.titleMedium,
                      ),
                      Text(
                        'Tap or drag map to place pin (Haptic confirmation)',
                        style: Theme.of(context).textTheme.bodySmall,
                      ),
                    ],
                  ),
                ),
                OutlinedButton.icon(
                  onPressed: _isLocatingGps ? null : _useCurrentGpsLocation,
                  icon: _isLocatingGps
                      ? const SizedBox(
                          width: 14,
                          height: 14,
                          child: CircularProgressIndicator(strokeWidth: 2),
                        )
                      : const Icon(Icons.my_location, size: 16),
                  label: const Text('Use my location'),
                ),
              ],
            ),
            const SizedBox(height: 8),
            ClipRRect(
              borderRadius: BorderRadius.circular(AppTheme.r10),
              child: SizedBox(
                height: 190,
                child: FlutterMap(
                  mapController: _mapController,
                  options: MapOptions(
                    initialCenter: _pinnedLocation,
                    initialZoom: 15.5,
                    onTap: (tapPosition, point) {
                      HapticFeedback.selectionClick();
                      setState(() {
                        _pinnedLocation = point;
                        _locationManuallyChanged = true;
                      });
                    },
                  ),
                  children: [
                    TileLayer(
                      urlTemplate:
                          'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                      userAgentPackageName:
                          'ph.gov.barangay.community_report_app',
                    ),
                    MarkerLayer(
                      markers: [
                        Marker(
                          point: _pinnedLocation,
                          width: 44,
                          height: 44,
                          child: const Icon(
                            Icons.location_on,
                            color: AppTheme.statusHazardDot,
                            size: 40,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 6),
            Text(
              'Coordinates: ${_pinnedLocation.latitude.toStringAsFixed(5)}, ${_pinnedLocation.longitude.toStringAsFixed(5)}',
              style: Theme.of(context).textTheme.bodySmall,
            ),
            const SizedBox(height: AppTheme.s12),
            TextFormField(
              controller: _addressController,
              textInputAction: TextInputAction.next,
              decoration: const InputDecoration(
                labelText: 'Street / Exact Location',
                hintText: 'e.g., Purok 3, Rizal St. near Barangay Covered Court',
                prefixIcon: Icon(Icons.place_outlined),
              ),
              validator: (val) {
                if (val == null || val.trim().isEmpty) {
                  return 'Please enter the street or location';
                }
                return null;
              },
            ),
            const SizedBox(height: AppTheme.s12),
            TextFormField(
              controller: _landmarkController,
              textInputAction: TextInputAction.done,
              decoration: const InputDecoration(
                labelText: 'Nearest Landmark (Optional)',
                prefixIcon: Icon(Icons.flag_outlined),
              ),
            ),
            const SizedBox(height: AppTheme.s24),

            // STEP 5: Submit
            ElevatedButton.icon(
              onPressed: reportProvider.isSubmitting ? null : _submitReport,
              icon: reportProvider.isSubmitting
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
                reportProvider.isSubmitting
                    ? 'Submitting Report...'
                    : 'Submit Official Report',
              ),
            ),
          ],
        ),
      );
    }

    if (widget.isFullScreen) {
      return Padding(
        padding: EdgeInsets.only(
          bottom: MediaQuery.of(context).viewInsets.bottom,
        ),
        child: buildSheetBody(null),
      );
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
      child: Padding(
        padding: EdgeInsets.only(
          bottom: MediaQuery.of(context).viewInsets.bottom,
        ),
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
              child: buildSheetBody(scrollController),
            );
          },
        ),
      ),
    );
  }
}

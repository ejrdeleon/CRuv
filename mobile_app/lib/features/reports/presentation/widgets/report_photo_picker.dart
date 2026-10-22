import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:geolocator/geolocator.dart';
import 'package:image_picker/image_picker.dart';
import '../../../../core/theme/app_theme.dart';

/// Reusable photo capture & preview component for Community Reports.
/// Provides direct camera capture, gallery selection, thumbnail preview,
/// haptic feedback, 48dp touch targets, and resilient permission error recovery.
class ReportPhotoPicker extends StatefulWidget {
  final File? photo;
  final ValueChanged<File?> onPhotoChanged;
  final bool isSubmitting;
  final ImagePicker? imagePicker;

  const ReportPhotoPicker({
    super.key,
    required this.photo,
    required this.onPhotoChanged,
    this.isSubmitting = false,
    this.imagePicker,
  });

  @override
  State<ReportPhotoPicker> createState() => _ReportPhotoPickerState();
}

class _ReportPhotoPickerState extends State<ReportPhotoPicker> {
  late final ImagePicker _picker;
  bool _isPicking = false;

  @override
  void initState() {
    super.initState();
    _picker = widget.imagePicker ?? ImagePicker();
    _checkAndroidLostData();
  }

  /// On Android, if the camera activity causes the host process to be destroyed by the OS,
  /// retrieveLostData() recovers the captured image when the app resumes.
  Future<void> _checkAndroidLostData() async {
    try {
      final LostDataResponse response = await _picker.retrieveLostData();
      if (response.isEmpty) {
        return;
      }
      if (response.file != null && mounted) {
        HapticFeedback.mediumImpact();
        widget.onPhotoChanged(File(response.file!.path));
      } else if (response.exception != null && mounted) {
        _handlePickerError(response.exception!);
      }
    } catch (_) {
      // Platform doesn't require or support retrieveLostData
    }
  }

  Future<void> _pickPhoto(ImageSource source) async {
    if (_isPicking || widget.isSubmitting) {
      return;
    }

    setState(() {
      _isPicking = true;
    });

    try {
      // Cap at 1600px and 80% JPEG quality for fast mobile uploads
      final XFile? picked = await _picker.pickImage(
        source: source,
        maxWidth: 1600,
        maxHeight: 1600,
        imageQuality: 80,
      );

      if (picked != null && mounted) {
        HapticFeedback.mediumImpact();
        widget.onPhotoChanged(File(picked.path));
      }
    } on PlatformException catch (e) {
      if (mounted) {
        _handlePickerError(e);
      }
    } catch (_) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Could not access camera or photo library.'),
          ),
        );
      }
    } finally {
      if (mounted) {
        setState(() {
          _isPicking = false;
        });
      }
    }
  }

  void _handlePickerError(PlatformException error) {
    final code = error.code.toLowerCase();
    final message = (error.message ?? '').toLowerCase();
    final isPermissionDenied = code.contains('denied') ||
        code.contains('permission') ||
        message.contains('permission') ||
        code == 'camera_access_denied';

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          isPermissionDenied
              ? 'Camera or photo permission was denied. Please allow access in Settings.'
              : 'Camera error (${error.message ?? error.code}).',
        ),
        action: isPermissionDenied
            ? SnackBarAction(
                label: 'Settings',
                onPressed: () => Geolocator.openAppSettings(),
              )
            : null,
      ),
    );
  }

  void _removePhoto() {
    HapticFeedback.lightImpact();
    widget.onPhotoChanged(null);
  }

  @override
  Widget build(BuildContext context) {
    final colorScheme = Theme.of(context).colorScheme;
    final textTheme = Theme.of(context).textTheme;
    final hasPhoto = widget.photo != null;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        // Section header with attachment counter
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Expanded(
              child: Text(
                '1. Photo Evidence',
                style: textTheme.titleMedium,
              ),
            ),
            if (hasPhoto)
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                decoration: BoxDecoration(
                  color: AppTheme.statusResolvedDot.withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(AppTheme.r10),
                ),
                child: const Text(
                  '1/1 Attached',
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w700,
                    color: AppTheme.statusResolvedDot,
                  ),
                ),
              ),
          ],
        ),
        const SizedBox(height: 8),

        // Thumbnail Preview with retake and remove actions
        if (hasPhoto) ...[
          Container(
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(AppTheme.r10),
              border: Border.all(
                color: colorScheme.outlineVariant.withValues(alpha: 0.5),
              ),
            ),
            clipBehavior: Clip.antiAlias,
            child: Stack(
              children: [
                Image.file(
                  widget.photo!,
                  height: 150,
                  width: double.infinity,
                  fit: BoxFit.cover,
                  errorBuilder: (_, _, _) => Container(
                    height: 120,
                    color: colorScheme.surfaceContainerHighest,
                    alignment: Alignment.center,
                    child: Text(
                      'Photo attached',
                      style: textTheme.bodyMedium,
                    ),
                  ),
                ),
                Positioned(
                  top: 8,
                  right: 8,
                  child: Material(
                    color: Colors.black87,
                    shape: const CircleBorder(),
                    child: InkWell(
                      customBorder: const CircleBorder(),
                      onTap: (widget.isSubmitting || _isPicking)
                          ? null
                          : _removePhoto,
                      child: const Padding(
                        padding: EdgeInsets.all(8.0),
                        child: Icon(
                          Icons.delete_outline_rounded,
                          size: 20,
                          color: Colors.white,
                        ),
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 8),
        ],

        // Action Buttons: Primary Camera (>=48dp touch target) & Secondary Gallery
        LayoutBuilder(
          builder: (context, constraints) {
            final textScale = MediaQuery.textScalerOf(context).scale(1.0);
            final isSmall = constraints.maxWidth < 340 || textScale >= 1.25;

            final cameraButton = FilledButton.icon(
              key: const Key('report_camera_button'),
              onPressed: (widget.isSubmitting || _isPicking)
                  ? null
                  : () => _pickPhoto(ImageSource.camera),
              icon: _isPicking
                  ? const SizedBox(
                      width: 18,
                      height: 18,
                      child: CircularProgressIndicator(
                        strokeWidth: 2,
                        color: Colors.white,
                      ),
                    )
                  : const Icon(
                      Icons.camera_alt_rounded,
                      size: 20,
                    ),
              label: Text(
                hasPhoto ? 'Retake Photo' : 'Take Photo',
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
              style: FilledButton.styleFrom(
                minimumSize: const Size.fromHeight(48),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(AppTheme.r10),
                ),
              ),
            );

            final galleryButton = OutlinedButton.icon(
              key: const Key('report_gallery_button'),
              onPressed: (widget.isSubmitting || _isPicking)
                  ? null
                  : () => _pickPhoto(ImageSource.gallery),
              icon: const Icon(
                Icons.photo_library_outlined,
                size: 20,
              ),
              label: Text(
                hasPhoto ? 'Replace from Gallery' : 'Gallery',
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
              style: OutlinedButton.styleFrom(
                minimumSize: const Size.fromHeight(48),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(AppTheme.r10),
                ),
              ),
            );

            if (isSmall) {
              return Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  cameraButton,
                  const SizedBox(height: 8),
                  galleryButton,
                ],
              );
            }

            return Row(
              children: [
                Expanded(
                  flex: hasPhoto ? 1 : 3,
                  child: cameraButton,
                ),
                const SizedBox(width: 8),
                Expanded(
                  flex: hasPhoto ? 1 : 2,
                  child: galleryButton,
                ),
              ],
            );
          },
        ),
      ],
    );
  }
}

import 'package:flutter/material.dart';
import '../../../../core/theme/app_theme.dart';
import '../../domain/report_models.dart';

/// Single shared image component for all report photos on mobile.
///
/// Ensures:
/// - True orientation and aspect ratio (BoxFit.contain, no forced crops or stretching)
/// - Neutral theme-aware letterbox container background
/// - Inline pinch-to-zoom and pan via InteractiveViewer
/// - Full-screen viewing with black backdrop, zoom, and close button
class ReportPhoto extends StatelessWidget {
  final String url;
  final String? thumbnailUrl;
  final int? width;
  final int? height;
  final String? caption;
  final double? maxHeight;
  final double? fixedWidth;
  final double? fixedHeight;
  final BorderRadius? borderRadius;
  final bool enableZoom;
  final bool enableFullScreen;

  const ReportPhoto({
    super.key,
    required this.url,
    this.thumbnailUrl,
    this.width,
    this.height,
    this.caption,
    this.maxHeight,
    this.fixedWidth,
    this.fixedHeight,
    this.borderRadius,
    this.enableZoom = true,
    this.enableFullScreen = true,
  });

  /// Factory constructor for report detail screen evidence display
  factory ReportPhoto.detail({
    Key? key,
    required ReportImageModel image,
    double maxHeight = 340.0,
  }) {
    return ReportPhoto(
      key: key,
      url: image.url,
      thumbnailUrl: image.thumbnailUrl,
      width: image.width,
      height: image.height,
      caption: image.caption,
      maxHeight: maxHeight,
      enableZoom: true,
      enableFullScreen: true,
    );
  }

  /// Factory constructor for list items and cards
  factory ReportPhoto.thumbnail({
    Key? key,
    required String url,
    String? thumbnailUrl,
    double size = 64.0,
    BorderRadius? borderRadius,
  }) {
    return ReportPhoto(
      key: key,
      url: url,
      thumbnailUrl: thumbnailUrl,
      fixedWidth: size,
      fixedHeight: size,
      borderRadius: borderRadius ?? BorderRadius.circular(AppTheme.radiusSm),
      enableZoom: false,
      enableFullScreen: true,
    );
  }

  void _openFullScreen(BuildContext context) {
    Navigator.of(context).push(
      PageRouteBuilder(
        opaque: false,
        barrierColor: Colors.black.withValues(alpha: 0.95),
        barrierDismissible: true,
        pageBuilder: (context, animation, secondaryAnimation) {
          return _FullScreenImageViewer(
            url: url,
            caption: caption,
          );
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final neutralBg = isDark ? AppTheme.slate800 : AppTheme.slate100;
    final radius = borderRadius ?? BorderRadius.circular(AppTheme.radiusSm);

    // If fixed dimensions are provided (thumbnail mode)
    if (fixedWidth != null && fixedHeight != null) {
      return GestureDetector(
        onTap: enableFullScreen ? () => _openFullScreen(context) : null,
        child: Container(
          width: fixedWidth,
          height: fixedHeight,
          clipBehavior: Clip.antiAlias,
          decoration: BoxDecoration(
            color: neutralBg,
            borderRadius: radius,
            border: Border.all(
              color: isDark ? AppTheme.slate700 : AppTheme.slate200,
              width: 1,
            ),
          ),
          child: Image.network(
            thumbnailUrl ?? url,
            fit: BoxFit.contain,
            loadingBuilder: (context, child, loadingProgress) {
              if (loadingProgress == null) return child;
              return Center(
                child: SizedBox(
                  width: 18,
                  height: 18,
                  child: CircularProgressIndicator(
                    strokeWidth: 2,
                    value: loadingProgress.expectedTotalBytes != null
                        ? loadingProgress.cumulativeBytesLoaded /
                            loadingProgress.expectedTotalBytes!
                        : null,
                  ),
                ),
              );
            },
            errorBuilder: (context, error, stackTrace) => Center(
              child: Icon(
                Icons.broken_image_outlined,
                size: 20,
                color: isDark ? AppTheme.slate400 : AppTheme.slate500,
              ),
            ),
          ),
        ),
      );
    }

    // Detail mode: full aspect ratio with pinch-to-zoom and max height constraint
    double? aspectRatio;
    if (width != null && height != null && width! > 0 && height! > 0) {
      aspectRatio = width! / height!;
    }

    Widget imageWidget = Image.network(
      url,
      fit: BoxFit.contain,
      loadingBuilder: (context, child, loadingProgress) {
        if (loadingProgress == null) return child;
        return Center(
          child: Padding(
            padding: const EdgeInsets.all(AppTheme.spacingLg),
            child: CircularProgressIndicator(
              strokeWidth: 2,
              value: loadingProgress.expectedTotalBytes != null
                  ? loadingProgress.cumulativeBytesLoaded /
                      loadingProgress.expectedTotalBytes!
                  : null,
            ),
          ),
        );
      },
      errorBuilder: (context, error, stackTrace) => Container(
        height: 160,
        alignment: Alignment.center,
        color: neutralBg,
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              Icons.broken_image_outlined,
              size: 32,
              color: isDark ? AppTheme.slate400 : AppTheme.slate500,
            ),
            const SizedBox(height: 6),
            Text(
              'Evidence photo unavailable',
              style: TextStyle(
                fontFamily: AppTheme.fontFamily,
                fontSize: 12,
                color: isDark ? AppTheme.slate300 : AppTheme.slate700,
              ),
            ),
          ],
        ),
      ),
    );

    if (enableZoom) {
      imageWidget = InteractiveViewer(
        minScale: 1.0,
        maxScale: 3.5,
        child: imageWidget,
      );
    }

    Widget content = Container(
      width: double.infinity,
      constraints: BoxConstraints(
        maxHeight: maxHeight ?? 360.0,
      ),
      clipBehavior: Clip.antiAlias,
      decoration: BoxDecoration(
        color: neutralBg,
        borderRadius: radius,
        border: Border.all(
          color: isDark ? AppTheme.slate700 : AppTheme.slate200,
          width: 1,
        ),
      ),
      alignment: Alignment.center,
      child: aspectRatio != null
          ? AspectRatio(
              aspectRatio: aspectRatio,
              child: imageWidget,
            )
          : imageWidget,
    );

    if (enableFullScreen) {
      content = GestureDetector(
        onDoubleTap: () => _openFullScreen(context),
        child: Stack(
          children: [
            content,
            Positioned(
              right: 8,
              bottom: 8,
              child: Material(
                color: Colors.black.withValues(alpha: 0.6),
                borderRadius: BorderRadius.circular(AppTheme.radiusSm),
                child: InkWell(
                  borderRadius: BorderRadius.circular(AppTheme.radiusSm),
                  onTap: () => _openFullScreen(context),
                  child: const Padding(
                    padding: EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(
                          Icons.fullscreen,
                          size: 16,
                          color: Colors.white,
                        ),
                        SizedBox(width: 4),
                        Text(
                          'Full Screen',
                          style: TextStyle(
                            fontFamily: AppTheme.fontFamily,
                            color: Colors.white,
                            fontSize: 11,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ),
          ],
        ),
      );
    }

    if (caption != null && caption!.trim().isNotEmpty) {
      return Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisSize: MainAxisSize.min,
        children: [
          content,
          const SizedBox(height: 6),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 4),
            child: Text(
              caption!,
              style: TextStyle(
                fontFamily: AppTheme.fontFamily,
                fontSize: 12,
                color: isDark ? AppTheme.slate400 : AppTheme.slate600,
              ),
            ),
          ),
        ],
      );
    }

    return content;
  }
}

class _FullScreenImageViewer extends StatelessWidget {
  final String url;
  final String? caption;

  const _FullScreenImageViewer({
    required this.url,
    this.caption,
  });

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      body: SafeArea(
        child: Stack(
          children: [
            // Center full interactive viewer with zoom and pan
            Center(
              child: InteractiveViewer(
                minScale: 0.8,
                maxScale: 5.0,
                child: Image.network(
                  url,
                  fit: BoxFit.contain,
                  loadingBuilder: (context, child, progress) {
                    if (progress == null) return child;
                    return Center(
                      child: CircularProgressIndicator(
                        strokeWidth: 2,
                        color: Colors.white,
                        value: progress.expectedTotalBytes != null
                            ? progress.cumulativeBytesLoaded /
                                progress.expectedTotalBytes!
                            : null,
                      ),
                    );
                  },
                  errorBuilder: (context, error, stackTrace) => const Center(
                    child: Text(
                      'Failed to load high-resolution image',
                      style: TextStyle(color: Colors.white70),
                    ),
                  ),
                ),
              ),
            ),

            // Top action bar with Close button
            Positioned(
              top: 8,
              right: 12,
              child: CircleAvatar(
                backgroundColor: Colors.white.withValues(alpha: 0.2),
                child: IconButton(
                  icon: const Icon(Icons.close, color: Colors.white),
                  tooltip: 'Close full screen',
                  onPressed: () => Navigator.of(context).pop(),
                ),
              ),
            ),

            // Bottom caption if present
            if (caption != null && caption!.trim().isNotEmpty)
              Positioned(
                bottom: 16,
                left: 16,
                right: 16,
                child: Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 14,
                    vertical: 8,
                  ),
                  decoration: BoxDecoration(
                    color: Colors.black.withValues(alpha: 0.75),
                    borderRadius: BorderRadius.circular(AppTheme.radiusSm),
                  ),
                  child: Text(
                    caption!,
                    textAlign: TextAlign.center,
                    style: const TextStyle(
                      fontFamily: AppTheme.fontFamily,
                      color: Colors.white,
                      fontSize: 13,
                    ),
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }
}

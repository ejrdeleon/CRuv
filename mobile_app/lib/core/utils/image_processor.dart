import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:image/image.dart' as img;

/// Utility to normalize image orientation and resize by longest side.
/// Bakes EXIF orientation directly into the pixel buffer so photos never appear
/// sideways or upside down regardless of platform, CDN, or browser rendering.
class ImageProcessor {
  static const int maxDimension = 1600;
  static const int jpegQuality = 80;

  /// Reads [sourceFile], bakes any EXIF orientation tag into the raw pixels,
  /// resizes proportionally by the longest side (capped at [maxDimension]),
  /// and writes the normalized JPEG bytes to disk.
  static Future<({File file, int width, int height})> processAndOrientImage(
    File sourceFile, {
    int maxSide = maxDimension,
    int quality = jpegQuality,
  }) async {
    final bytes = await sourceFile.readAsBytes();
    
    // Process on background isolate when possible to avoid dropping frames
    final result = await compute(_bakeAndResize, (
      bytes: bytes,
      maxSide: maxSide,
      quality: quality,
    ));

    final targetPath =
        '${sourceFile.parent.path}/norm_${DateTime.now().microsecondsSinceEpoch}.jpg';
    final outputFile = File(targetPath);
    await outputFile.writeAsBytes(result.bytes);

    return (
      file: outputFile,
      width: result.width,
      height: result.height,
    );
  }

  /// Internal worker function for isolate execution
  static ({Uint8List bytes, int width, int height}) _bakeAndResize(
    ({Uint8List bytes, int maxSide, int quality}) params,
  ) {
    final decoded = img.decodeImage(params.bytes);
    if (decoded == null) {
      return (bytes: params.bytes, width: 0, height: 0);
    }

    // 1. Bake EXIF orientation directly into the pixels.
    // This physically rotates or flips pixel rows/columns and resets EXIF orientation to 1,
    // eliminating any chance of sideways rotation in browsers or mobile viewports.
    final oriented = img.bakeOrientation(decoded);

    // 2. Resize by the longest side while strictly preserving the aspect ratio.
    img.Image resized = oriented;
    if (oriented.width > params.maxSide || oriented.height > params.maxSide) {
      if (oriented.width >= oriented.height) {
        resized = img.copyResize(
          oriented,
          width: params.maxSide,
          interpolation: img.Interpolation.linear,
        );
      } else {
        resized = img.copyResize(
          oriented,
          height: params.maxSide,
          interpolation: img.Interpolation.linear,
        );
      }
    }

    // 3. Encode to JPEG with specified quality (default 80)
    final encoded = img.encodeJpg(resized, quality: params.quality);

    return (
      bytes: Uint8List.fromList(encoded),
      width: resized.width,
      height: resized.height,
    );
  }
}

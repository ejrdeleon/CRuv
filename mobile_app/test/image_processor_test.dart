import 'dart:io';
import 'package:flutter_test/flutter_test.dart';
import 'package:image/image.dart' as img;
import 'package:community_report_app/core/utils/image_processor.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  late Directory tempDir;

  setUp(() async {
    tempDir = await Directory.systemTemp.createTemp('image_orient_test_');
  });

  tearDown(() async {
    if (await tempDir.exists()) {
      await tempDir.delete(recursive: true);
    }
  });

  test('Bakes portrait image and preserves aspect ratio', () async {
    // 600 wide x 900 high (true portrait)
    final image = img.Image(width: 600, height: 900);
    final rawBytes = img.encodeJpg(image);
    final file = File('${tempDir.path}/portrait.jpg');
    await file.writeAsBytes(rawBytes);

    final result = await ImageProcessor.processAndOrientImage(file);
    expect(result.width, 600);
    expect(result.height, 900);
    expect(result.height > result.width, isTrue); // Stays portrait
  });

  test('Bakes landscape image and preserves aspect ratio', () async {
    // 1200 wide x 800 high (true landscape)
    final image = img.Image(width: 1200, height: 800);
    final rawBytes = img.encodeJpg(image);
    final file = File('${tempDir.path}/landscape.jpg');
    await file.writeAsBytes(rawBytes);

    final result = await ImageProcessor.processAndOrientImage(file);
    expect(result.width, 1200);
    expect(result.height, 800);
    expect(result.width > result.height, isTrue); // Stays landscape
  });

  test('Rotates EXIF orientation 6 (phone camera portrait on landscape sensor) to upright portrait', () async {
    // Smartphone camera sensor stores 1200x800 with orientation tag 6 (rotate 90 CW to view upright)
    final image = img.Image(width: 1200, height: 800);
    image.exif.imageIfd.orientation = 6;
    final rawBytes = img.encodeJpg(image);
    final file = File('${tempDir.path}/exif6.jpg');
    await file.writeAsBytes(rawBytes);

    final result = await ImageProcessor.processAndOrientImage(file);
    // After bakeOrientation, the pixels are physically rotated 90 deg clockwise:
    // width becomes 800, height becomes 1200!
    expect(result.width, 800);
    expect(result.height, 1200);
    expect(result.height > result.width, isTrue);
  });

  test('Rotates EXIF orientation 8 (rotate 270 CW) to upright portrait', () async {
    final image = img.Image(width: 1200, height: 800);
    image.exif.imageIfd.orientation = 8;
    final rawBytes = img.encodeJpg(image);
    final file = File('${tempDir.path}/exif8.jpg');
    await file.writeAsBytes(rawBytes);

    final result = await ImageProcessor.processAndOrientImage(file);
    expect(result.width, 800);
    expect(result.height, 1200);
    expect(result.height > result.width, isTrue);
  });

  test('Resizes oversized image by longest side preserving aspect ratio', () async {
    // 3000 x 2000 (aspect ratio 1.5)
    final image = img.Image(width: 3000, height: 2000);
    final rawBytes = img.encodeJpg(image);
    final file = File('${tempDir.path}/large.jpg');
    await file.writeAsBytes(rawBytes);

    final result = await ImageProcessor.processAndOrientImage(file, maxSide: 1600);
    expect(result.width, 1600);
    expect(result.height, (2000 * 1600 / 3000).round()); // ~1067
    expect((result.width / result.height - 1.5).abs() < 0.01, isTrue);
  });
}

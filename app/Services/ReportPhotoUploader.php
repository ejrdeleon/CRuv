<?php

namespace App\Services;

use App\Models\Report;
use App\Models\ReportImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ReportPhotoUploader
{
    private const MAX_DIMENSION = 1600;

    private const THUMB_MAX_DIMENSION = 400;

    private const JPEG_QUALITY = 82;

    private ImageManager $imageManager;

    public function __construct(?ImageManager $imageManager = null)
    {
        $this->imageManager = $imageManager ?? new ImageManager(new Driver);
    }

    /**
     * Auto-orient, proportionally resize by longest side, generate thumbnail,
     * and persist image record linked to report.
     */
    public function storeAndAttach(
        Report $report,
        UploadedFile $file,
        bool $isResolutionProof = false,
        string $caption = 'Resident photo attachment'
    ): ReportImage {
        $stored = $this->resizeAndStore($file);

        return ReportImage::create([
            'report_id' => $report->id,
            'file_path' => $stored['file_path'],
            'original_name' => $stored['original_name'],
            'mime_type' => $stored['mime_type'],
            'file_size' => $stored['file_size'],
            'is_resolution_proof' => $isResolutionProof,
            'caption' => $caption,
        ]);
    }

    /**
     * Auto-orient, scale down by longest side preserving aspect ratio (no crop),
     * generate proportional thumbnail, and store on public disk.
     *
     * @return array{file_path: string, original_name: string, mime_type: string, file_size: int}
     */
    public function resizeAndStore(UploadedFile $file): array
    {
        $originalName = $file->getClientOriginalName();
        $realPath = $file->getRealPath();

        try {
            // 1. Decode uploaded image using Intervention Image v4
            $image = $this->imageManager->decode($file->getContent());

            // 2. Auto-orient: physically transforms pixel buffer according to EXIF rotation/mirror tags
            // and strips orientation tag to prevent double rotation in modern browsers.
            $image = $image->orient();

            // 3. Proportionally resize by longest side (max 1600px), strictly preserving aspect ratio (no crop)
            $image = $image->scaleDown(width: self::MAX_DIMENSION, height: self::MAX_DIMENSION);

            // 4. Generate proportional thumbnail (max 400px), strictly preserving aspect ratio
            $thumb = clone $image;
            $thumb = $thumb->scaleDown(width: self::THUMB_MAX_DIMENSION, height: self::THUMB_MAX_DIMENSION);

            $uuid = Str::uuid()->toString();
            $extension = 'jpg';
            $mimeType = 'image/jpeg';

            $mainEncoded = (string) $image->encodeUsingFileExtension('jpg', quality: self::JPEG_QUALITY);
            $thumbEncoded = (string) $thumb->encodeUsingFileExtension('jpg', quality: self::JPEG_QUALITY);

            $filePath = 'reports/'.$uuid.'.'.$extension;
            $thumbPath = 'reports/thumbnails/'.$uuid.'.'.$extension;

            Storage::disk('public')->put($filePath, $mainEncoded);
            Storage::disk('public')->put($thumbPath, $thumbEncoded);

            return [
                'file_path' => $filePath,
                'original_name' => $originalName,
                'mime_type' => $mimeType,
                'file_size' => strlen($mainEncoded),
            ];
        } catch (\Throwable) {
            // Fallback for non-image or corrupt payloads
            $fallbackPath = $file->store('reports', 'public');

            return [
                'file_path' => (string) $fallbackPath,
                'original_name' => $originalName,
                'mime_type' => $file->getMimeType() ?? 'image/jpeg',
                'file_size' => (int) $file->getSize(),
            ];
        }
    }
}

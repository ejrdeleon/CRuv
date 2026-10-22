<?php

namespace App\Services;

use App\Models\Report;
use App\Models\ReportImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReportPhotoUploader
{
    private const MAX_DIMENSION = 1600;

    private const JPEG_QUALITY = 85;

    /**
     * Resize and store an uploaded report image on the public disk.
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
     * Resize image if larger than MAX_DIMENSION and persist via Laravel Storage.
     *
     * @return array{file_path: string, original_name: string, mime_type: string, file_size: int}
     */
    public function resizeAndStore(UploadedFile $file): array
    {
        $originalName = $file->getClientOriginalName();
        $mimeType = $file->getMimeType() ?? 'image/jpeg';
        $rawContents = @file_get_contents($file->getRealPath());

        if ($rawContents !== false && function_exists('imagecreatefromstring')) {
            $sourceImage = @imagecreatefromstring($rawContents);

            if ($sourceImage !== false) {
                $width = imagesx($sourceImage);
                $height = imagesy($sourceImage);

                if ($width > self::MAX_DIMENSION || $height > self::MAX_DIMENSION) {
                    $ratio = min(self::MAX_DIMENSION / $width, self::MAX_DIMENSION / $height);
                    $targetWidth = max(1, (int) round($width * $ratio));
                    $targetHeight = max(1, (int) round($height * $ratio));

                    $resizedImage = imagecreatetruecolor($targetWidth, $targetHeight);

                    if ($resizedImage !== false) {
                        imagealphablending($resizedImage, false);
                        imagesavealpha($resizedImage, true);
                        imagecopyresampled(
                            $resizedImage,
                            $sourceImage,
                            0,
                            0,
                            0,
                            0,
                            $targetWidth,
                            $targetHeight,
                            $width,
                            $height
                        );
                        imagedestroy($sourceImage);
                        $sourceImage = $resizedImage;
                    }
                }

                ob_start();
                if ($mimeType === 'image/png') {
                    imagepng($sourceImage, null, 7);
                    $extension = 'png';
                } elseif ($mimeType === 'image/webp' && function_exists('imagewebp')) {
                    imagewebp($sourceImage, null, self::JPEG_QUALITY);
                    $extension = 'webp';
                } else {
                    imagejpeg($sourceImage, null, self::JPEG_QUALITY);
                    $mimeType = 'image/jpeg';
                    $extension = 'jpg';
                }
                $encodedBytes = (string) ob_get_clean();
                imagedestroy($sourceImage);

                if ($encodedBytes !== '') {
                    $filePath = 'reports/'.Str::uuid()->toString().'.'.$extension;
                    Storage::disk('public')->put($filePath, $encodedBytes);

                    return [
                        'file_path' => $filePath,
                        'original_name' => $originalName,
                        'mime_type' => $mimeType,
                        'file_size' => strlen($encodedBytes),
                    ];
                }
            }
        }

        $fallbackPath = $file->store('reports', 'public');

        return [
            'file_path' => (string) $fallbackPath,
            'original_name' => $originalName,
            'mime_type' => $mimeType,
            'file_size' => (int) $file->getSize(),
        ];
    }
}

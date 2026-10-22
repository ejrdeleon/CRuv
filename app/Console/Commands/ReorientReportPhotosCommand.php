<?php

namespace App\Console\Commands;

use App\Models\ReportImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ReorientReportPhotosCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:reorient-photos {--dry-run : Check and list photos without modifying files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-orient stored report photos to fix sideways/upside-down orientations and create missing thumbnails';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = (bool) $this->option('dry-run');
        $images = ReportImage::all();

        $this->info("Scanning {$images->count()} report image records...".($isDryRun ? ' [DRY RUN]' : ''));

        $manager = new ImageManager(new Driver);
        $disk = Storage::disk('public');
        $processed = 0;
        $missing = 0;
        $errors = 0;

        foreach ($images as $reportImage) {
            $filePath = $reportImage->file_path;

            if (! $disk->exists($filePath)) {
                $missing++;
                $this->warn("File missing on disk: {$filePath} (Report #{$reportImage->report_id})");

                continue;
            }

            try {
                $image = $manager->decode($disk->get($filePath));

                $origWidth = $image->width();
                $origHeight = $image->height();

                // Orient according to EXIF and reset orientation flag
                $oriented = $image->orient();
                $newWidth = $oriented->width();
                $newHeight = $oriented->height();

                $thumbPath = str_replace('reports/', 'reports/thumbnails/', $filePath);
                $needsReorient = ($origWidth !== $newWidth || $origHeight !== $newHeight);
                $needsThumb = ! $disk->exists($thumbPath);

                if ($needsReorient || $needsThumb) {
                    $processed++;
                    $this->line("Report #{$reportImage->report_id} image [{$reportImage->id}]: ".($needsReorient ? 'needs re-orient; ' : '').($needsThumb ? 'needs thumbnail' : ''));

                    if (! $isDryRun) {
                        $encoded = (string) $oriented->encodeUsingFileExtension('jpg', quality: 82);
                        $disk->put($filePath, $encoded);

                        $thumb = clone $oriented;
                        $thumb = $thumb->scaleDown(width: 400, height: 400);
                        $disk->put($thumbPath, (string) $thumb->encodeUsingFileExtension('jpg', quality: 82));
                    }
                }
            } catch (\Throwable $e) {
                $errors++;
                $this->error("Error processing [{$filePath}]: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info("Complete! Total: {$images->count()}, Modified: {$processed}, Missing: {$missing}, Errors: {$errors}");

        return self::SUCCESS;
    }
}

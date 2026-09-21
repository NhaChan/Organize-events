<?php

namespace App\Console\Commands;

use App\Models\CategoryContentBlock;
use App\Models\CategoryPage;
use App\Models\CategoryPageImage;
use App\Models\Event;
use App\Models\EventImage;
use App\Support\ImageOptimizer;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

class OptimizeStoredImages extends Command
{
    protected $signature = 'images:optimize-existing';

    protected $description = 'Create smaller WebP copies of uploaded images and update their database paths';

    public function handle(): int
    {
        $changed = 0;

        Event::query()->whereNotNull('thumbnail')->eachById(function (Event $event) use (&$changed) {
            $changed += $this->optimizeField($event, 'thumbnail', 1600);
            $content = $event->content ?? '';

            preg_match_all('#/storage/(content-images/[a-zA-Z0-9/_\\.-]+)#', $content, $matches);
            foreach (array_unique($matches[1] ?? []) as $oldPath) {
                $newPath = ImageOptimizer::optimizeStored($oldPath);
                if ($newPath !== $oldPath) {
                    $content = str_replace('/storage/'.$oldPath, '/storage/'.$newPath, $content);
                    $changed++;
                }
            }

            if ($content !== ($event->content ?? '')) {
                $event->forceFill(['content' => $content])->save();
            }
        });

        EventImage::query()->eachById(function (EventImage $image) use (&$changed) {
            $changed += $this->optimizeField($image, 'image_path');
        });
        CategoryPage::query()->eachById(function (CategoryPage $page) use (&$changed) {
            $changed += $this->optimizeField($page, 'service_image', 1280);
            $changed += $this->optimizeField($page, 'banner_image');
        });
        CategoryPageImage::query()->eachById(function (CategoryPageImage $image) use (&$changed) {
            $changed += $this->optimizeField($image, 'image_path');
        });
        CategoryContentBlock::query()->eachById(function (CategoryContentBlock $block) use (&$changed) {
            $changed += $this->optimizeField($block, 'image');
        });

        $this->info("Optimized {$changed} images. Original files were kept as backups.");

        return self::SUCCESS;
    }

    private function optimizeField(Model $model, string $field, int $maxDimension = 1920): int
    {
        $oldPath = $model->getAttribute($field);

        if (! is_string($oldPath) || ! str_contains($oldPath, '/')) {
            return 0;
        }

        $newPath = ImageOptimizer::optimizeStored($oldPath, $maxDimension);

        if ($newPath === $oldPath) {
            return 0;
        }

        $model->forceFill([$field => $newPath])->save();

        return 1;
    }
}

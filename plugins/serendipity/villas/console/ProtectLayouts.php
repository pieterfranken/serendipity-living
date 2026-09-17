<?php namespace Serendipity\Villas\Console;

use Illuminate\Console\Command;
use Serendipity\Villas\Models\Villa;
use System\Models\File;
use RuntimeException;

/** Moves existing public layout attachments into October's protected storage. */
class ProtectLayouts extends Command
{
    protected $signature = 'villas:protect-layouts {--apply : Apply the migration; otherwise only inspect}';
    protected $description = 'Protect existing villa layouts, retaining originals and a rollback manifest';

    public function handle(): int
    {
        if (config('filesystems.disks.uploads.driver') !== 'local') {
            throw new RuntimeException('This migration requires local upload storage.');
        }
        $root = realpath(config('filesystems.disks.uploads.root', storage_path('app/uploads')));
        if (!$root) {
            throw new RuntimeException('Upload storage could not be found.');
        }
        $root = str_replace('\\', '/', $root);
        $files = File::where('attachment_type', (new Villa)->getMorphClass())
            ->where('field', 'layouts')->where('is_public', true)->get();
        $plan = [];
        foreach ($files as $file) {
            $source = str_replace('\\', '/', $file->getLocalPath());
            $privateFile = clone $file;
            $privateFile->is_public = false;
            $target = str_replace('\\', '/', $privateFile->getLocalPath());
            if (!str_starts_with($source, $root.'/public/') || !is_file($source)
                || !str_starts_with($target, $root.'/protected/')) {
                throw new RuntimeException('Invalid or missing local layout file '.$file->id);
            }
            if (is_file($target) && hash_file('sha256', $source) !== hash_file('sha256', $target)) {
                throw new RuntimeException('A different protected file already exists for '.$file->id);
            }
            $plan[] = ['id' => $file->id, 'source' => $source, 'target' => $target,
                'old_is_public' => true, 'sha256' => hash_file('sha256', $source)];
        }
        $this->info(count($plan).' public layout attachments need protection.');
        if (!$this->option('apply') || !$plan) {
            return 0;
        }

        $backup = $root.'/protected/layout-migration-backups/'.date('Ymd-His').'-'.bin2hex(random_bytes(4));
        $this->makeDirectory($backup);
        $manifest = $backup.'/manifest.json';
        file_put_contents($manifest, json_encode($plan, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR), LOCK_EX);

        foreach ($plan as $item) {
            $this->makeDirectory(dirname($item['target']));
            if (!is_file($item['target']) && !copy($item['source'], $item['target'])) {
                throw new RuntimeException('Failed to copy layout '.$item['id']);
            }
            if (hash_file('sha256', $item['target']) !== $item['sha256']) {
                throw new RuntimeException('Layout copy verification failed for '.$item['id']);
            }
            $file = $files->firstWhere('id', $item['id']);
            $file->is_public = false;
            $file->save();

            // Retain originals outside public storage, including generated previews.
            $fileBackup = $backup.'/'.$item['id'];
            $this->makeDirectory($fileBackup);
            $sources = array_merge([$item['source']], glob(dirname($item['source']).'/thumb_'.$item['id'].'_*') ?: []);
            foreach ($sources as $source) {
                if (!rename($source, $fileBackup.'/'.basename($source))) {
                    throw new RuntimeException('Could not protect old public file '.$source.'. Rollback manifest: '.$manifest);
                }
            }
            $this->line('Protected layout attachment '.$item['id']);
        }
        $this->info('Originals and rollback manifest: '.$backup);
        return 0;
    }

    private function makeDirectory(string $path): void
    {
        if (!is_dir($path) && !mkdir($path, 0755, true) && !is_dir($path)) {
            throw new RuntimeException('Could not create directory '.$path);
        }
    }
}

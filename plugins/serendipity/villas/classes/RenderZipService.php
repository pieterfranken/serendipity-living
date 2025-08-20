<?php namespace Serendipity\Villas\Classes;

use Serendipity\Villas\Models\Villa;
use ZipArchive;
use Storage;
use File;
use Config;
use Exception;

class RenderZipService
{
    protected $disk;
    protected $zipDir;
    protected $maxZipSizeMb;

    public function __construct()
    {
        $this->disk = Config::get('serendipity.villas::renders.disk', 'local');
        $this->zipDir = trim(Config::get('serendipity.villas::renders.zip_dir', 'media/renders/zips'), '/');
        $this->maxZipSizeMb = (int) Config::get('serendipity.villas::renders.max_zip_size_mb', 1024);
    }

    public function ensureZip(Villa $villa, bool $force = false): ?array
    {
        $meta = $this->readMeta($villa);
        $signature = $this->computeSignature($villa);

        if (!$force && $meta && isset($meta['signature']) && $meta['signature'] === $signature) {
            // No change
            return $meta;
        }

        return $this->buildZip($villa, $signature);
    }

    public function purge(Villa $villa): void
    {
        $meta = $this->readMeta($villa);
        if ($meta && isset($meta['path'])) {
            $full = storage_path('app/'.$meta['path']);
            if (File::exists($full)) {
                @File::delete($full);
            }
        }
        $this->writeMeta($villa, null);
    }

    protected function computeSignature(Villa $villa): string
    {
        $parts = [
            $villa->id,
            (string) $villa->updated_at,
            (string) $villa->enable_renders_download,
        ];
        foreach ($villa->renders as $file) {
            $parts[] = $file->id;
            $parts[] = (string) $file->file_size;
            $parts[] = (string) $file->updated_at;
        }
        return hash('sha256', implode('|', $parts));
    }

    protected function buildZip(Villa $villa, string $signature): ?array
    {
        if (!$villa->renders || !$villa->renders->count()) {
            $this->writeMeta($villa, null);
            return null;
        }

        // Prepare paths
        $slug = $villa->slug ?: ('villa-'.$villa->id);
        $date = date('Ymd');
        $filename = sprintf('serendipity-villa-%s-renders-%s.zip', $slug, $date);
        $relativeDir = $this->zipDir.'/'.intval($villa->id);
        $relativePath = $relativeDir.'/'.$filename;

        // Ensure directory exists on local disk
        $fullDir = storage_path('app/'.$relativeDir);
        if (!File::exists($fullDir)) {
            File::makeDirectory($fullDir, 0755, true);
        }
        $fullPath = storage_path('app/'.$relativePath);

        // Build zip
        $zip = new ZipArchive;
        if ($zip->open($fullPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception('Unable to create ZIP at '.$fullPath);
        }

        $addedSize = 0;
        foreach ($villa->renders as $file) {
            $path = $file->getLocalPath();
            if (!$path || !File::exists($path)) {
                continue;
            }
            $basename = basename($path);
            $zip->addFile($path, $basename);
            $addedSize += (int) File::size($path);
            if (($addedSize / (1024*1024)) > $this->maxZipSizeMb) {
                $zip->close();
                @File::delete($fullPath);
                throw new Exception('ZIP exceeds configured size limit');
            }
        }

        $zip->close();

        $meta = [
            'path' => $relativePath,
            'filename' => $filename,
            'signature' => $signature,
            'built_at' => time(),
            'size' => File::exists($fullPath) ? File::size($fullPath) : 0,
        ];
        $this->writeMeta($villa, $meta);
        return $meta;
    }

    protected function metaPath(Villa $villa): string
    {
        $relativeDir = $this->zipDir.'/'.intval($villa->id);
        return storage_path('app/'.$relativeDir.'/.meta.json');
    }

    protected function readMeta(Villa $villa): ?array
    {
        $path = $this->metaPath($villa);
        if (!File::exists($path)) return null;
        $json = @File::get($path);
        if (!$json) return null;
        $data = @json_decode($json, true);
        return is_array($data) ? $data : null;
    }

    protected function writeMeta(Villa $villa, ?array $data): void
    {
        $path = $this->metaPath($villa);
        $dir = dirname($path);
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        if ($data === null) {
            if (File::exists($path)) File::delete($path);
            return;
        }
        File::put($path, json_encode($data));
    }

    public function getZipAbsolutePath(array $meta): ?string
    {
        if (!isset($meta['path'])) return null;
        $fullPath = storage_path('app/'.$meta['path']);
        return File::exists($fullPath) ? $fullPath : null;
    }
}


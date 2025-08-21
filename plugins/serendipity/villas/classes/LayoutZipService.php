<?php namespace Serendipity\Villas\Classes;

use Serendipity\Villas\Models\Villa;
use ZipArchive;
use Exception;
use File;
use Config;

class LayoutZipService
{
    protected $disk;
    protected $zipDir;
    protected $maxZipSizeMb;

    public function __construct()
    {
        $this->disk = Config::get('serendipity.villas::layouts.disk', 'local');
        $this->zipDir = trim(Config::get('serendipity.villas::layouts.zip_dir', 'media/layouts/zips'), '/');
        $this->maxZipSizeMb = (int) Config::get('serendipity.villas::layouts.max_zip_size_mb', 1024);
    }

    public function ensureZip(Villa $villa, bool $force = false): ?array
    {
        $meta = $this->readMeta($villa);
        $signature = $this->computeSignature($villa);

        if (!$force && $meta && isset($meta['signature']) && $meta['signature'] === $signature) {
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
            (string) $villa->enable_layouts_download,
        ];
        foreach ($villa->layouts as $file) {
            $parts[] = $file->id;
            $parts[] = (string) $file->file_size;
            $parts[] = (string) $file->updated_at;
        }
        return hash('sha256', implode('|', $parts));
    }

    protected function buildZip(Villa $villa, string $signature): ?array
    {
        if (!$villa->layouts || !$villa->layouts->count()) {
            $this->writeMeta($villa, null);
            return null;
        }

        $slug = $villa->slug ?: ('villa-'.$villa->id);
        $date = date('Ymd');
        $filename = sprintf('serendipity-villa-%s-layouts-%s.zip', $slug, $date);
        $relativeDir = $this->zipDir.'/'.intval($villa->id);
        $relativePath = $relativeDir.'/'.$filename;

        $fullDir = storage_path('app/'.$relativeDir);
        if (!File::exists($fullDir)) {
            File::makeDirectory($fullDir, 0755, true);
        }
        $fullPath = storage_path('app/'.$relativePath);

        $zip = new ZipArchive;
        if ($zip->open($fullPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception('Unable to create ZIP at '.$fullPath);
        }

        $addedSize = 0;
        foreach ($villa->layouts as $file) {
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


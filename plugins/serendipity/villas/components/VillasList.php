<?php namespace Serendipity\Villas\Components;

use Cms\Classes\ComponentBase;
use Serendipity\Villas\Models\Villa;

class VillasList extends ComponentBase
{
    public $villas;

    public function componentDetails()
    {
        return [
            'name'        => 'Villas List',
            'description' => 'Displays a list of villas with basic filtering.'
        ];
    }

    public function onRun()
    {
        $villas = Villa::query()
            ->with(['thumbnail', 'gallery'])
            ->where(function($q){
                $q->whereNull('project_id')->orWhere('visible_in_catalog', true);
            })
            ->orderByDesc('featured_in_catalog')
            ->orderByDesc('id')
            ->take(12)
            ->get();

        // Apply chronological sort (earliest delivery first; undated last; image presence secondary)
        $this->villas = $this->sortVillasByDelivery($villas);
    }

    /**
     * Two-tier sorting for villas:
     * 1) Primary: delivery_date ascending (undated after dated)
     * 2) Secondary within dated/undated: villas with real images first, placeholders last
     * Keep chronological order for dated, original order for undated.
     */
    protected function sortVillasByDelivery($villas)
    {
        $indexed = [];
        foreach ($villas as $i => $v) {
            $key = $this->parseDeliverySortKey((string)($v->delivery_date ?? ''));
            $hasImg = $this->villaHasRealImage($v);
            $hasDate = $key !== null;
            $group = ($hasDate ? 0 : 2) + ($hasImg ? 0 : 1); // 0..3 priority
            $indexed[] = ['i' => $i, 'key' => $key, 'villa' => $v, 'group' => $group];
        }
        usort($indexed, function($a, $b) {
            if ($a['group'] !== $b['group']) return $a['group'] <=> $b['group'];
            $datedGroup = ($a['group'] === 0 || $a['group'] === 1);
            if ($datedGroup) {
                if ($a['key'] === $b['key']) return $a['i'] <=> $b['i'];
                return ($a['key'] < $b['key']) ? -1 : 1;
            }
            return $a['i'] <=> $b['i'];
        });
        $models = array_map(function($row){ return $row['villa']; }, $indexed);
        if ($villas instanceof \Illuminate\Database\Eloquent\Collection) {
            return new \Illuminate\Database\Eloquent\Collection($models);
        }
        return collect($models);
    }

    /**
     * True if villa has a real image (thumbnail attachment, any gallery, or non-placeholder external URL)
     */
    protected function villaHasRealImage($villa): bool
    {
        try {
            if (!empty($villa->thumbnail)) return true;
            if (!empty($villa->gallery) && count($villa->gallery) > 0) return true;
            $url = (string)($villa->thumbnail_url ?? '');
            if ($url !== '' && stripos($url, 'placeholder-villa.svg') === false) return true;
        } catch (\Throwable $e) {
            // tolerant of missing relations
        }
        return false;
    }

    /**
     * Parse flexible delivery_date strings into an integer yyyymmdd sort key.
     * Supports: Q1 2026, Spring 2027, January 2026, 2026, etc.
     */
    protected function parseDeliverySortKey(string $text): ?int
    {
        // Normalize NBSP to space and trim
        $t = trim(str_replace("\xC2\xA0", ' ', $text));
        if ($t === '') return null;

        if (preg_match('/Q([1-4])\s*(\d{4})/iu', $t, $m)) {
            $q = (int)$m[1]; $y = (int)$m[2];
            $month = [1=>1,2=>4,3=>7,4=>10][$q] ?? 1;
            return $y*10000 + $month*100 + 1;
        }
        if (preg_match('/\b(Spring|Summer|Autumn|Fall|Winter)\b\s*(\d{4})/iu', $t, $m)) {
            $season = strtolower($m[1]); $y = (int)$m[2];
            $map = [ 'winter'=>1, 'spring'=>4, 'summer'=>7, 'autumn'=>10, 'fall'=>10 ];
            $month = $map[$season] ?? 6;
            return $y*10000 + $month*100 + 1;
        }
        if (preg_match('/\b(Jan(?:uary)?|Feb(?:ruary)?|Mar(?:ch)?|Apr(?:il)?|May|Jun(?:e)?|Jul(?:y)?|Aug(?:ust)?|Sep(?:t|tember)?|Oct(?:ober)?|Nov(?:ember)?|Dec(?:ember)?)\b\s*(\d{4})/iu', $t, $m)) {
            $monName = strtolower($m[1]); $y = (int)$m[2];
            $monMap = [
                'jan'=>1,'january'=>1,
                'feb'=>2,'february'=>2,
                'mar'=>3,'march'=>3,
                'apr'=>4,'april'=>4,
                'may'=>5,
                'jun'=>6,'june'=>6,
                'jul'=>7,'july'=>7,
                'aug'=>8,'august'=>8,
                'sep'=>9,'sept'=>9,'september'=>9,
                'oct'=>10,'october'=>10,
                'nov'=>11,'november'=>11,
                'dec'=>12,'december'=>12,
            ];
            $month = $monMap[$monName] ?? 6;
            return $y*10000 + $month*100 + 1;
        }
        if (preg_match('/\b(\d{4})\b/u', $t, $m)) {
            $y = (int)$m[1];
            return $y*10000 + 6*100 + 1;
        }
        return null;
    }
}



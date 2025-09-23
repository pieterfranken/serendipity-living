<?php namespace Serendipity\Villas\Components;

use Cms\Classes\ComponentBase;
use Serendipity\Villas\Models\Project;

class ProjectsList extends ComponentBase
{
    public $projectsCurrent;
    public $projectsPrevious;

    public function componentDetails()
    {
        return [
            'name'        => 'Projects List',
            'description' => 'Displays current and/or previous projects with their villas.'
        ];
    }

    public function defineProperties()
    {
        return [
            'showPrevious' => [
                'title' => 'Show Previous Projects',
                'type' => 'checkbox',
                'default' => true
            ],
            'showCurrent' => [
                'title' => 'Show Current Projects',
                'type' => 'checkbox',
                'default' => true
            ]
        ];
    }

    public function onRun()
    {
        if ($this->property('showCurrent')) {
            $this->projectsCurrent = Project::with(['villas', 'villas.gallery', 'villas.thumbnail'])
                ->current()
                ->orderBy('id', 'desc')
                ->get();
            $this->projectsCurrent = $this->sortProjectsVillas($this->projectsCurrent);
        }
        if ($this->property('showPrevious')) {
            $this->projectsPrevious = Project::with(['villas', 'villas.gallery', 'villas.thumbnail'])
                ->previous()
                ->orderBy('id', 'desc')
                ->get();
            $this->projectsPrevious = $this->sortProjectsVillas($this->projectsPrevious);
        }
    }

    protected function sortProjectsVillas($projects)
    {
        return $projects->map(function($project){
            if ($project->villas) {
                $project->villas = $this->sortVillasByDelivery($project->villas);
            }
            return $project;
        });
    }

    /**
     * Two-tier sorting for villas:
     * 1) Primary: delivery_date ascending (undated after dated)
     * 2) Secondary within dated/undated groups: real images first, placeholders last
     * Keep chronological order for dated items within their groups, and original order for undated.
     */
    protected function sortVillasByDelivery($villas)
    {
        $indexed = [];
        foreach ($villas as $i => $v) {
            $key = $this->parseDeliverySortKey((string)($v->delivery_date ?? ''));
            $hasImg = $this->villaHasRealImage($v);
            $hasDate = $key !== null;
            $group = ($hasDate ? 0 : 2) + ($hasImg ? 0 : 1); // 0..3 as per priority
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
     * Determines if a villa has a real image (thumbnail attachment, any gallery image,
     * or a thumbnail_url that is not the placeholder-villa.svg)
     */
    protected function villaHasRealImage($villa): bool
    {
        try {
            if (!empty($villa->thumbnail)) return true;
            if (!empty($villa->gallery) && count($villa->gallery) > 0) return true;
            $url = (string)($villa->thumbnail_url ?? '');
            if ($url !== '' && stripos($url, 'placeholder-villa.svg') === false) return true;
        } catch (\Throwable $e) {
            // tolerate missing relations
        }
        return false;
    }

    /**
     * Parse flexible delivery_date strings into an integer yyyymmdd sort key.
     * See ProjectDetail component for supported formats.
     */
    protected function parseDeliverySortKey(string $text): ?int
    {
        // Normalize non-breaking spaces to regular spaces, then trim
        $t = trim(str_replace("\xC2\xA0", ' ', $text));
        if ($t === '') return null;

        // Use Unicode-aware patterns (u) and accept both regular and NBSP-like spaces via \s after normalization
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



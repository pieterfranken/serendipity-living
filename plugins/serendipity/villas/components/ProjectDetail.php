<?php namespace Serendipity\Villas\Components;

use Cms\Classes\ComponentBase;
use Serendipity\Villas\Models\Project;

class ProjectDetail extends ComponentBase
{
    public $project;

    public function componentDetails()
    {
        return [
            'name'        => 'Project Detail',
            'description' => 'Displays a single project and its villas.'
        ];
    }

    public function defineProperties()
    {
        return [
            'slug' => [
                'title'       => 'Slug',
                'description' => 'URL slug used to find the project',
                'type'        => 'string',
                'default'     => '{{ :slug }}'
            ],
        ];
    }

    public function onRun()
    {
        $slug = $this->property('slug');
        $project = Project::with(['villas', 'villas.gallery', 'villas.thumbnail'])->where('slug', $slug)->first();
        if (!$project) {
            return \Response::make('Project not found', 404);
        }
        // Sort villas by delivery_date chronology (earliest first). Villas without delivery_date go after, preserving original order.
        if ($project->villas) {
            $project->villas = $this->sortVillasByDelivery($project->villas);
        }
        $this->page['project'] = $project;
    }

    /**
     * Sort a collection of villas by parsed delivery_date (earliest first).
     * Villas with no parsable delivery_date are placed after, keeping original order.
     */
    protected function sortVillasByDelivery($villas)
    {
        // Snapshot original order index for stable sort
        $indexed = [];
        foreach ($villas as $i => $v) {
            $key = $this->parseDeliverySortKey((string)($v->delivery_date ?? ''));
            $indexed[] = ['i' => $i, 'key' => $key, 'villa' => $v];
        }
        usort($indexed, function($a, $b) {
            $ka = $a['key']; $kb = $b['key'];
            $aHas = $ka !== null; $bHas = $kb !== null;
            if ($aHas && $bHas) {
                // Earlier first
                if ($ka === $kb) return $a['i'] <=> $b['i'];
                return ($ka < $kb) ? -1 : 1;
            }
            if ($aHas && !$bHas) return -1;
            if (!$aHas && $bHas) return 1;
            return $a['i'] <=> $b['i'];
        });
        $models = array_map(function($row){ return $row['villa']; }, $indexed);
        // Preserve Eloquent collection type when possible
        if ($villas instanceof \Illuminate\Database\Eloquent\Collection) {
            return new \Illuminate\Database\Eloquent\Collection($models);
        }
        return collect($models);
    }

    /**
     * Parse flexible delivery_date strings into an integer yyyymmdd sort key.
     * Supports formats like "Q1 2025", "Q4 2026", "Spring 2025", "March 2025", or just "2025".
     * Returns null if not parsable.
     */
    protected function parseDeliverySortKey(string $text): ?int
    {
        $t = trim($text);
        if ($t === '') return null;

        // Quarter pattern anywhere in string
        if (preg_match('/Q([1-4])\s*(\d{4})/i', $t, $m)) {
            $q = (int)$m[1]; $y = (int)$m[2];
            $month = [1=>1,2=>4,3=>7,4=>10][$q] ?? 1;
            return $y*10000 + $month*100 + 1;
        }

        // Season pattern
        if (preg_match('/\b(Spring|Summer|Autumn|Fall|Winter)\b\s*(\d{4})/i', $t, $m)) {
            $season = strtolower($m[1]); $y = (int)$m[2];
            $map = [ 'winter'=>1, 'spring'=>4, 'summer'=>7, 'autumn'=>10, 'fall'=>10 ];
            $month = $map[$season] ?? 6;
            return $y*10000 + $month*100 + 1;
        }

        // Month name + year
        if (preg_match('/\b(Jan(?:uary)?|Feb(?:ruary)?|Mar(?:ch)?|Apr(?:il)?|May|Jun(?:e)?|Jul(?:y)?|Aug(?:ust)?|Sep(?:t|tember)?|Oct(?:ober)?|Nov(?:ember)?|Dec(?:ember)?)\b\s*(\d{4})/i', $t, $m)) {
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

        // Year only
        if (preg_match('/\b(\d{4})\b/', $t, $m)) {
            $y = (int)$m[1];
            return $y*10000 + 6*100 + 1; // mid-year
        }

        return null;
    }

}


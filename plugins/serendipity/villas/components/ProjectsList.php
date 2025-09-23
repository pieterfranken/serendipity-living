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
     * Sort a collection of villas by parsed delivery_date (earliest first).
     * Villas with no parsable delivery_date are placed after, keeping original order.
     */
    protected function sortVillasByDelivery($villas)
    {
        $indexed = [];
        foreach ($villas as $i => $v) {
            $key = $this->parseDeliverySortKey((string)($v->delivery_date ?? ''));
            $indexed[] = ['i' => $i, 'key' => $key, 'villa' => $v];
        }
        usort($indexed, function($a, $b) {
            $ka = $a['key']; $kb = $b['key'];
            $aHas = $ka !== null; $bHas = $kb !== null;
            if ($aHas && $bHas) {
                if ($ka === $kb) return $a['i'] <=> $b['i'];
                return ($ka < $kb) ? -1 : 1;
            }
            if ($aHas && !$bHas) return -1;
            if (!$aHas && $bHas) return 1;
            return $a['i'] <=> $b['i'];
        });
        $models = array_map(function($row){ return $row['villa']; }, $indexed);
        if ($villas instanceof \Illuminate\Database\Eloquent\Collection) {
            return new \Illuminate\Database\Eloquent\Collection($models);
        }
        return collect($models);
    }

    /**
     * Parse flexible delivery_date strings into an integer yyyymmdd sort key.
     * See ProjectDetail component for supported formats.
     */
    protected function parseDeliverySortKey(string $text): ?int
    {
        $t = trim($text);
        if ($t === '') return null;

        if (preg_match('/Q([1-4])\s*(\d{4})/i', $t, $m)) {
            $q = (int)$m[1]; $y = (int)$m[2];
            $month = [1=>1,2=>4,3=>7,4=>10][$q] ?? 1;
            return $y*10000 + $month*100 + 1;
        }
        if (preg_match('/\b(Spring|Summer|Autumn|Fall|Winter)\b\s*(\d{4})/i', $t, $m)) {
            $season = strtolower($m[1]); $y = (int)$m[2];
            $map = [ 'winter'=>1, 'spring'=>4, 'summer'=>7, 'autumn'=>10, 'fall'=>10 ];
            $month = $map[$season] ?? 6;
            return $y*10000 + $month*100 + 1;
        }
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
        if (preg_match('/\b(\d{4})\b/', $t, $m)) {
            $y = (int)$m[1];
            return $y*10000 + 6*100 + 1;
        }
        return null;
    }
}



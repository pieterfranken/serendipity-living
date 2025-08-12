<?php namespace Serendipity\Villas\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class PreviousProjects extends Controller
{
    public $implement = [
        'Backend.Behaviors.ListController',
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ReorderController',
        'Backend.Behaviors.RelationController',
    ];

    public $requiredPermissions = ['serendipity.villas.manage'];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';
    public $relationConfig = 'config_relation.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Serendipity.Villas', 'villas', 'previousprojects');
    }

    public function listExtendQuery($query)
    {
        $query->where('is_previous', true);
    }
}


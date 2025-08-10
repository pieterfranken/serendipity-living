<?php namespace Serendipity\Villas\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Villas extends Controller
{
    public $implement = [
        'Backend.Behaviors.ListController',
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ReorderController',
    ];

    public $reorderConfig = 'config_reorder.yaml';

    public $requiredPermissions = ['serendipity.villas.manage'];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Serendipity.Villas', 'villas', 'villas');
    }

    // ListController delegates
    public function index()
    {
        $this->asExtension('ListController')->index();
    }

    // FormController delegates
    public function create()
    {
        $this->asExtension('FormController')->create();
    }

    public function update($recordId = null)
    {
        $this->asExtension('FormController')->update($recordId);
    }

    public function preview($recordId = null)
    {
        $this->asExtension('FormController')->preview($recordId);
    }
}


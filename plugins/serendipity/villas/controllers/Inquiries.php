<?php namespace Serendipity\Villas\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

class Inquiries extends Controller
{
    public $implement = [
        'Backend\\Behaviors\\ListController',
        'Backend\\Behaviors\\FormController',
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = ['serendipity.villas.manage'];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Serendipity.Villas', 'villas', 'inquiries');
    }

    public function index()
    {
        $this->asExtension('ListController')->index();
    }

    // FormController delegates
    public function update($recordId = null)
    {
        $this->asExtension('FormController')->update($recordId);
    }

    public function preview($recordId = null)
    {
        $this->asExtension('FormController')->preview($recordId);
    }
}


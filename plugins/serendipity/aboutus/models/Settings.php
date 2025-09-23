<?php namespace Serendipity\AboutUs\Models;

use Model;

class Settings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'serendipity_aboutus_settings';
    public $settingsFields = 'fields.yaml';

    protected $jsonable = ['team_members'];
}


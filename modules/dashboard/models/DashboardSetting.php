<?php namespace Dashboard\Models;

use App;
use System\Models\SettingModel;
use System\Helpers\Preset as PresetHelper;

/**
 * DashboardSetting model
 *
 * @package october\dashboard
 * @author Alexey Bobkov, Samuel Georges
 */
class DashboardSetting extends SettingModel
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string settingsCode is a unique code for these settings
     */
    public $settingsCode = 'dashboard_settings';

    /**
     * @var array rules for validation
     */
    public $rules = [];

    /**
     * initSettingsData for this model. This only executes when the
     * model is first created or reset to default.
     * @return void
     */
    public function initSettingsData()
    {
        $config = App::make('config');
        $this->traffic_stats_enabled = $config->get('dashboard.internal_traffic_statistics.enabled', true);
        $this->traffic_stats_timezone = $config->get('dashboard.internal_traffic_statistics.timezone');
        $this->traffic_stats_retention = $config->get('dashboard.internal_traffic_statistics.retention');
    }

    /**
     * getTimezoneOptions returns all available timezone options.
     * @return array
     */
    public function getTrafficStatsTimezoneOptions()
    {
        return [
            '' => '- '.__('Use Default').' -',
        ] + PresetHelper::timezones();
    }
}

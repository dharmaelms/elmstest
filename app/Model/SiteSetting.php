<?php

namespace App\Model;

use Cache;
use Moloquent;

class SiteSetting extends Moloquent
{
    /**
     * The table/collection associated with the model.
     *
     * @var string
     */
    protected $table = 'site_settings';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
    * The attributes that should be mutated to dates.
    *
    * @var array
    */
    protected $dates = ['updated_at'];

    /**
     * Name of the setting cache key
     * @var string
     */
    const CACHE_KEY = 'site-settings';

    /**
     * Expire time of setting cache in minutes
     * @var integer
     */
    const CACHE_EXPIRE = 60;

    /**
     * Get all settings
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getSettings()
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_EXPIRE, function () {
            return self::get();
        });
    }

    /**
     * Get module specific settings
     *
     * @param string $module
     * @param string|null $name
     * @param mixed|null $default
     * @return mixed|null
     */
    public static function module($module, $name = null, $default = null)
    {
        $result = self::getSettings()->where('module', $module)->first();

        if (!is_null($name) && !is_null($result)) {
            return collect($result->setting)->get($name, $default);
        }

        return $result;
    }

    /**
     * Update site settings site settings
     *
     * @param string $module
     * @param array $settings
     * @return integer
     */
    public static function updateModule($module, array $settings)
    {
        $response = self::where('module', $module)
            ->update(['setting' => $settings], ['upsert' => true]);

        Cache::forget(self::CACHE_KEY);

        return $response;
    }
}

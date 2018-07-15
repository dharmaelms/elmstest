<?php
use App\Model\SiteSetting;

/**
 * Check if all the values exist in array
 * @param array $array
 * @param array $values
 * @return bool
 */
function are_values_exist($array, $values)
{
    return (count($values) === count(array_intersect($array, $values)));
}

/**
 * @param string $module
 * @return boolean
 */
function is_certificate_enable($module)
{	
	$site_setting_module = SiteSetting::where('module', $module)->first(['setting']);
	if ((isset($site_setting_module->setting['visibility'])) && ($site_setting_module->setting['visibility'] == 'true')) {
		return true;
	}
    return false;
}

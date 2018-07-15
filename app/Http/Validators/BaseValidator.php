<?php

namespace App\Http\Validators;

use Validator;

abstract class BaseValidator
{
    private static function extendValidator()
    {
        Validator::extend("valid_end_date", function ($attribute, $endDate, $parameters) {
            if (empty($parameters) || empty($parameters[0])) {
                return true;
            }
            return $endDate > $parameters[0];
        });
    }

    protected static function getValidator($input, $rules, $options = [])
    {
        if (array_key_exists("filters", $options) && !empty($options["filters"])) {
            $filteredInputKeys = array_intersect(array_keys($rules), $options["filters"]);
            $rules = array_filter($rules, function ($key) use ($filteredInputKeys) {
                if (in_array($key, $filteredInputKeys)) {
                    return true;
                } else {
                    return false;
                }
            }, ARRAY_FILTER_USE_KEY);
        }

        if (array_key_exists("custom_attributes", $options) && !empty($options["custom_attributes"])) {
            $customAttributes = $options["custom_attributes"];
            if (isset($filteredInputKeys) && is_array($filteredInputKeys) && !empty($filteredInputKeys)) {
                $customAttributes = array_filter($options["custom_attributes"], function ($key) use ($filteredInputKeys) {
                    if (in_array($key, $filteredInputKeys)) {
                        return true;
                    } else {
                        return false;
                    }
                }, ARRAY_FILTER_USE_KEY);
            }
        }

        if (array_key_exists("custom_rules", $options) && !empty($options["custom_rules"])) {
            $rules = array_replace($rules, $options["custom_rules"]);
        }

        $messages = (array_key_exists("custom_messages", $options) && !empty($options["custom_messages"])) ? $options["custom_messages"] : [];

        self::extendValidator();

        $validator = Validator::make($input, $rules, $messages);
        if (isset($customAttributes) && is_array($customAttributes) && !empty($customAttributes)) {
            $validator->setAttributeNames($customAttributes);
        }
        return $validator;
    }
}

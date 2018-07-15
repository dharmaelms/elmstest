<?php

namespace App\Enums;

/**
 * Abstract class for Base Enumeration
 * Reference: http://stackoverflow.com/questions/254514/php-and-enumerations
 *
 * @package \App\Enums
 */
abstract class BaseEnum
{
    /**
     * @var array
     */
    protected static $cacheArray = null;

    /**
     * Get constant values of the class in array
     *
     * @return  array
     */
    public static function toArray()
    {
        return array_values(static::getConstants());
    }

    /**
     * Get constant values of the class in array
     *
     * @return  array
     */
    public static function getConstants()
    {
        if (is_null(static::$cacheArray)) {
            static::$cacheArray = [];
        }

        $calledClass = get_called_class();
        if (!array_key_exists($calledClass, static::$cacheArray)) {
            $reflect = new \ReflectionClass($calledClass);
            static::$cacheArray[$calledClass] = $reflect->getConstants();
        }

        return static::$cacheArray[$calledClass];
    }

    /**
     * Get constants values of the class in string
     *
     * @return string
     */
    public static function toString()
    {
        return implode(',', array_values(static::getConstants()));
    }
}

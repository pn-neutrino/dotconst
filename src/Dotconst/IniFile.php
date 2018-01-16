<?php

namespace Neutrino\Dotconst;

use Neutrino\Dotconst\Exception\InvalidFileException;

/**
 * Class IniFile
 *
 * @package Neutrino\Dotconst
 */
class IniFile
{

    public static function parse($file)
    {
        $config = parse_ini_file($file, true, INI_SCANNER_TYPED);

        if ($config === false) {
            throw new InvalidFileException('Failed parse file : ' . $file);
        }

        return array_change_key_case(self::definable($config), CASE_UPPER);
    }

    public static function mergeWith($config, $file)
    {
        foreach (self::parse($file) as $section => $value) {
            if (isset($config[$section]) && is_array($value)) {
                $config[$section] = array_merge($config[$section], $value);
            } else {
                $config[$section] = $value;
            }
        }

        return $config;
    }

    private static function definable($config)
    {
        $flatten = [];
        foreach ($config as $section => $value) {
            if (is_array($value)) {
                $value = self::definable($value);
                foreach ($value as $k => $v) {
                    $flatten["{$section}_{$k}"] = $v;
                }
            } else {
                $flatten[$section] = $value;
            }
        }

        return $flatten;
    }
}

<?php

namespace Neutrino\Dotconst;

use Neutrino\Dotconst\Exception\InvalidFileException;

/**
 * Class Loader
 *
 * @package Neutrino\Dotenv
 */
class Loader
{
    /**
     * Loads environment variables from .env.php to getenv(), $_ENV, and $_SERVER automatically.
     *
     * @param string $path Path to ".const.ini" files
     * @param null   $compilePath
     */
    public static function load($path, $compilePath = null)
    {
        if (!$compilePath || !self::fromCompile($compilePath)) {
            foreach (self::fromFiles($path) as $const => $value) {
                define($const, $value);
            };
        }
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public static function fromCompile($path)
    {
        if (file_exists($compilePath = $path . '/consts.php')) {
            require $compilePath;

            return true;
        }

        return false;
    }

    /**
     * @param string $path
     *
     * @return array
     */
    public static function fromFiles($path)
    {
        $pathEnv = $path . DIRECTORY_SEPARATOR . '.const';

        if (!file_exists($pathEnv . '.ini')) {
            return [];
        }

        $config = self::parse($pathEnv . '.ini');

        $definable = self::definable($config);

        if (isset($definable['APP_ENV']) && ($env = $definable['APP_ENV']) && !empty($env) && file_exists($pathEnv . '.' . $env . '.ini')) {
            foreach (self::parse($pathEnv . '.' . $env . '.ini') as $section => $value) {
                if (isset($config[$section]) && is_array($value)) {
                    $config[$section] = array_merge($config[$section], $value);
                } else {
                    $config[$section] = $value;
                }
            }

            $definable = self::definable($config);
        }

        return $definable;
    }

    /**
     * @param $file
     *
     * @return array
     * @throws \Neutrino\Dotconst\Exception\InvalidFileException
     */
    private static function parse($file)
    {
        if (($config = parse_ini_file($file, true, INI_SCANNER_TYPED)) === false) {
            throw new InvalidFileException('Failed parse file : ' . $file);
        }

        $config = self::upperKeys($config);

        $dir = dirname($file);

        array_walk_recursive($config, function (&$value) use ($dir) {
            $value = self::variabilize('const:([\w:\\\\]+)', $value, function ($match) {
                return constant($match[1]);
            });
            $value = self::variabilize('dir(?::(/[\w\-. ]+))?', $value, function ($match) use ($dir) {
                return self::normalizePath($dir . (isset($match[1]) ? $match[1] : ''));
            });
        });

        return $config;
    }

    /**
     * @param $array
     *
     * @return array
     */
    private static function upperKeys($array)
    {
        return array_map(function ($item) {
            if (is_array($item)) {
                $item = self::upperKeys($item);
            }

            return $item;
        }, array_change_key_case($array, CASE_UPPER));
    }

    /**
     * @param $pattern
     * @param $str
     * @param $by
     *
     * @return mixed
     */
    private static function variabilize($pattern, $str, $by)
    {
        if (preg_match('#^@php/' . $pattern . '@?#', $str, $match)) {
            $str = preg_replace('#^@php/' . $pattern . '@?#', $by($match), $str);
        }

        return $str;
    }

    /**
     * @param array $config
     *
     * @return array
     */
    private static function definable(array $config)
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

    /**
     * @param $path
     *
     * @return string
     */
    private static function normalizePath($path)
    {
        // Process the components
        $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);

        $startWithSlash = strpos($path, '/') === 0;

        $parts = explode('/', str_replace(DIRECTORY_SEPARATOR, '/', $path));
        $safe  = [];
        foreach ($parts as $idx => $part) {
            if (empty($part) || ('.' == $part)) {
                continue;
            } elseif ('..' == $part) {
                array_pop($safe);
                continue;
            } else {
                $safe[] = $part;
            }
        }

        return ($startWithSlash ? DIRECTORY_SEPARATOR : '') . implode(DIRECTORY_SEPARATOR, $safe);
    }
}

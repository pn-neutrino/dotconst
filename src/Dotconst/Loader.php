<?php

namespace Neutrino\Dotconst;

use Neutrino\Dotconst;

/**
 * Class Loader
 *
 * @package Neutrino\Dotconst
 */
class Loader
{
    /**
     * Load Compiled contants file
     *
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
     * Load & parse .const.ini & .const.{env}.ini files
     *
     * {env} is matched by [APP_ENV] Parameter
     *
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

        $raw = self::loadRaw($path);

        $config = self::parse($raw, $pathEnv . '.ini');

        return $config;
    }

    public static function loadRaw($path)
    {
        $basePath = $path . DIRECTORY_SEPARATOR . '.const';

        $path = $basePath . '.ini';

        if (!file_exists($path)) {
            return [];
        }

        $raw = IniFile::parse($path);

        $config = self::parse($raw, $path);

        if (!empty($config['APP_ENV']) && file_exists($pathEnv = $basePath . '.' . $config['APP_ENV'] . '.ini')) {
            $raw = IniFile::mergeWith($raw, $pathEnv);
        }

        return $raw;
    }

    /**
     * @param array $config
     * @param string $file
     *
     * @return array
     * @throws \Neutrino\Dotconst\Exception\InvalidFileException
     */
    private static function parse($config, $file)
    {
        return self::dynamize($config, dirname($file));
    }

    private static function dynamize($config, $dir)
    {
        foreach (Dotconst::getExtensions() as $extension) {
            foreach ($config as &$value) {
                if ($extension->identify($value)) {
                    $value = $extension->parse($value, $dir);
                }
            }
        }

        foreach ($config as &$value) {
            $value = self::variabilize('\{(\w+)\}', $value, function ($match) use ($config) {
                $key = strtoupper($match[1]);

                return isset($config[$key]) ? $config[$key] : $match[1];
            });
        }

        return $config;
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
        if (preg_match('#^@' . $pattern . '@?#', $str, $match)) {
            $str = preg_replace('#^@' . $pattern . '@?#', $by($match), $str);
        }

        return $str;
    }

    /**
     * @param $path
     *
     * @return string
     */
    public static function normalizePath($path)
    {
        if (empty($path)) {
            return '';
        }

        $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);

        $parts = explode('/', $path);

        $safe = [];
        foreach ($parts as $idx => $part) {
            if (($idx == 0 && empty($part))) {
                $safe[] = '';
            } elseif (trim($part) == "" || $part == '.') {
            } elseif ('..' == $part) {
                if (null === array_pop($safe) || empty($safe)) {
                    $safe[] = '';
                }
            } else {
                $safe[] = $part;
            }
        }

        if (count($safe) === 1 && $safe[0] === '') {
            return DIRECTORY_SEPARATOR;
        }

        return implode(DIRECTORY_SEPARATOR, $safe);
    }
}

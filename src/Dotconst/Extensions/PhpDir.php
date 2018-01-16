<?php

namespace Neutrino\Dotconst\Extensions;

use Neutrino\Dotconst\Loader;
use Neutrino\Support\Str;

/**
 * Class PhpDir
 *
 * @package Neutrino\Dotconst\Extensions
 */
class PhpDir extends Extension
{
    protected $identifier = 'php/dir(?::(/[\w\-. ]+))?(?:@(.+))?';

    /**
     * @param string $value
     * @param string $path
     *
     * @return string
     */
    public function parse($value, $path)
    {
        $match = $this->match($value);

        return Loader::normalizePath($path . DIRECTORY_SEPARATOR . (isset($match[1]) ? $match[1] : '') . (isset($match[2]) ? $match[2] : ''));
    }

    /**
     * @param string $value
     * @param string $path
     *
     * @return string
     */
    public function compile($value, $path)
    {
        $match = $this->match($value);

        return "'" . addslashes(Loader::normalizePath($path . DIRECTORY_SEPARATOR . (isset($match[1]) ? $match[1] : ''))) . "'";
    }
}

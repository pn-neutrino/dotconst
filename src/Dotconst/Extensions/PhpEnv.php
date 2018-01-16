<?php

namespace Neutrino\Dotconst\Extensions;

/**
 * Class PhpEnv
 *
 * @package Neutrino\Dotconst\Extensions
 */
class PhpEnv extends Extension
{
    protected $identifier = 'php/env:(\w+)(?::(\w+))?';

    /**
     * @param string $value
     * @param string $path
     *
     * @return string
     */
    public function parse($value, $path)
    {
        $match = $this->match($value);

        $value = getenv($match[1]);

        return $value === false ? (isset($match[2]) ? $match[2] : null) : $value;
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

        if(isset($match[2])){
            return "(\$_ = getenv('{$match[1]}')) === false ? " . (isset($match[2]) ? "'{$match[2]}'" : 'null') . " : \$_";
        }

        return "getenv('{$match[1]}')";
    }
}

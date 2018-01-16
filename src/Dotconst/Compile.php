<?php

namespace Neutrino\Dotconst;

use Neutrino\Dotconst;
use Neutrino\Dotconst\Exception\InvalidFileException;
use Neutrino\Dotconst\Extensions\PhpConst;
use Neutrino\Dotconst\Extensions\PhpDir;
use Neutrino\Dotconst\Extensions\PhpEnv;

/**
 * Class Compile
 *
 * @package Neutrino\Dotconst
 */
class Compile
{

    /**
     * Compile loaded & parsed ini files to php files.
     *
     * @param $path
     * @param $compilePath
     *
     * @throws \Neutrino\Dotconst\Exception\InvalidFileException
     */
    public static function compile($path, $compilePath)
    {
        $extensions = Dotconst::getExtensions();

        $raw = Loader::loadRaw($path);

        $config = Loader::fromFiles($path);

        $r = fopen($compilePath . '/consts.php', 'w');

        if ($r === false) {
            throw new InvalidFileException('Can\'t create file : ' . $compilePath);
        }

        fwrite($r, "<?php" . PHP_EOL);

        $deferred = [];

        foreach ($raw as $const => $value) {
            foreach ($extensions as $k => $extension) {
                if(is_string($extension)){
                    $extensions[$k] = $extension = new $extension;
                }

                if ($extension->identify($value)) {
                    fwrite($r, "define('$const', " . $extension->compile($value, $path) . ");" . PHP_EOL);

                    continue 2;
                }
            }

            if (preg_match('#^@\{(\w+)\}@?#', $value, $match)) {
                $key = strtoupper($match[1]);

                $value = preg_replace('#^@\{(\w+)\}@?#', '', $value);

                $draw = '';
                if(isset($config[$key])){
                    $draw .= $key;
                } else {
                    $draw .= $match[1] ;
                }
                if(!empty($value)){
                    $draw .= " . '$value'";
                }

                $deferred[] = "define('$const', $draw);";

                continue;
            }

            fwrite($r, "define('$const', " . var_export($value, true) . ");" . PHP_EOL);
        }

        foreach ($deferred as $item) {
            fwrite($r, $item . PHP_EOL);
        }

        fclose($r);
    }
}

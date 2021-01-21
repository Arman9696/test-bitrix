<?php

namespace IQDEV;

use Pimple\Container;
use Pimple\Psr11;


/**
 * Class App
 * @package IQDEV
 */
class App
{
    /**
     * @param Container $container
     * @return Psr11\Container
     */
    public static function services(Container $container = null): Psr11\Container
    {
        static $services;
        if ($container) $services = new Psr11\Container($container);
        if (empty($services)) $services = new Psr11\Container(new Container());
        return $services;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws \Exception
     */
    public static function __callStatic(string $name, array $arguments)
    {
        if (static::services()->has($name)) {
            return static::services()->get($name);
        }
        throw new \Exception('Undefined service "'.$name.'"');
    }
}

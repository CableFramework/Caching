<?php

namespace Cable\Caching;


use Cable\Container\ContainerInterface;
use Cable\Container\Factory;

/**
 * Class Caching
 * @package Cable\Caching
 */
class Caching
{
    /**
     * create a new cache instance
     *
     * @param ContainerInterface|null $container
     * @param array $configs
     * @return Cache
     */
    public static function create(
        ContainerInterface $container = null,
        array $configs = []
    ){
        if (null === $container) {
            $container = Factory::create();
        }

        return new Cache(
          $container,
          new SerializerManager(),
          new CompressorManager(),
          $configs
        );
    }
}
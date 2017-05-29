<?php

namespace Cable\Components\Caching;


use Cable\Container\ContainerInterface;

class Cache
{

    /**
     * @var ContainerInterface
     */
    private $container;


    /**
     * @var array
     */
    protected $drivers = [

    ];


    protected $comprosser;

    /**
     * @var array
     */
    private $configs = [];

    /**
     * @var string
     */
    protected $defaultDriver;

    /**
     * Cache constructor.
     * @param ContainerInterface $container
     * @param array $configs
     */
    public function __construct(ContainerInterface $container, array $configs = [])
    {
        $this->container = $container;

        $this->dispatchConfigs($configs);
    }

    /**
     * @param array $configs
     * @return void returns nothings
     */
    private function dispatchConfigs(array $configs)
    {
        if (isset($configs['default'])) {
            $this->defaultDriver = $configs['default'];
            unset($configs['default']);
        }

        if (isset($configs['drivers'])) {
            $this->drivers = $configs['drivers'];
            unset($configs['drivers']);
        }

        $this->configs = $configs;
    }

}

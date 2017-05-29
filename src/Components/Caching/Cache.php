<?php

namespace Cable\Components\Caching;


use Cable\Components\Caching\Compressor\CompressorInterface;
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
    private $drivers = [

    ];


    private $compress = true;

    /**
     * @var CompressorInterface
     */
    private $compressor;

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
     * @return CompressorInterface
     */
    public function getCompressor()
    {
        if (null === $this->compressor) {
            $this->setComprossor(
                $this->resolveCompressor()
            );
        }

        return $this->compressor;
    }

    private function resolveCompressor(){
        return $this->container->resolve('Cable\Components\Caching\Compressor\CompressorInterface');
    }

    /**
     * @param CompressorInterface $compressor
     * @return Cache
     */
    public function setCompressor(CompressorInterface $compressor)
    {
        $this->compressor = $compressor;

        return $this;
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

        if (isset($configs['compress'])) {
            $this->compress = $configs['compress'];
            unset($configs['compress']);
        }


        $this->configs = $configs;
    }


}

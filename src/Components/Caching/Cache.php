<?php

namespace Cable\Components\Caching;


use Cable\Components\Caching\Compressor\BootableCompressorInterface;
use Cable\Components\Caching\Driver\DriverInterface;
use Cable\Components\Caching\Driver\FlushableDriverInterface;
use Cable\Components\Caching\Driver\TimeableDriverInterface;
use Cable\Components\Caching\Exceptions\DriverNotFlushableException;
use Cable\Container\ContainerInterface;
use Cable\Container\ExpectationException;
use Cable\Container\NotFoundException;
use Cable\Container\Resolver\ResolverException;

class Cache implements FlushableDriverInterface, TimeableDriverInterface, DriverInterface
{

    /**
     * @var ContainerInterface
     */
    private $container;


    /**
     * @var bool
     */
    private $compress = true;


    /**
     * @var array
     */
    private $drivers = [];

    /**
     * @var array
     */
    private $configs = [];

    /**
     * @var string
     */
    protected $defaultDriver;

    /**
     * @var string
     */
    private static $compressorInterface = '\Cable\Components\Caching\Compressor\BootableCompressorInterface';

    /**
     * @var string
     */
    private static $compressorContainer = 'caching.compressor';

    /**
     * @var string
     */
    private static $driverContainer = 'caching.driver';

    /**
     * @var string
     */
    private static $driverInterface = '\Cable\Components\Caching\DriveInterface';

    /**
     * Cache constructor.
     * @param ContainerInterface $container
     * @param array $configs
     */
    public function __construct(ContainerInterface $container, array $configs = [])
    {
        $this->container = $container;

        $this->dispatchConfigs($configs);

        $this->addExpectations();
    }


    /**
     * @param string $driver
     * @param mixed $callback
     * @return $this
     */
    public function addDriver($driver, $callback)
    {
        $this->container->add(
            $name = static::$driverContainer.'.'.$driver,
            $callback
        );

        $this->container->expect($name, static::$driverInterface);


        return $this;
    }

    private function addExpectations()
    {
        $this->container->expect(
            static::$compressorContainer,
            static::$compressorInterface
        );

    }

    /**
     * @return BootableCompressorInterface
     *
     * @throws \ReflectionException
     * @throws ResolverException
     * @throws NotFoundException
     * @throws ExpectationException
     */
    public function getCompressor()
    {
        $class = $this->container->resolve(
            static::$compressorContainer
        );

        $this->container->method($class, 'boot');

        return $class;
    }


    /**
     * @param BootableCompressorInterface $compressor
     * @return Cache
     */
    public function setCompressor($compressor)
    {
        $this->container->add(
            static::$compressorContainer,
            $compressor
        );

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

    /**
     * @param string $name
     * @throws ResolverException
     * @throws NotFoundException
     * @throws ExpectationException
     * @return DriverInterface
     */
    public function driver($name)
    {
        if ( ! isset($this->drivers[$name])) {
            $this->drivers[$name] = $this
                ->container
                ->resolve($this->buildDriverName($name));
        }

        return $this->drivers[$name];
    }

    /**
     * @param string $name
     * @return string
     */
    private function buildDriverName($name)
    {
        return static::$driverContainer.'.'.$name;
    }

    /**
     * @throws NotFoundException
     * @throws ExpectationException
     * @throws ResolverException
     * @return DriverInterface
     */
    private function getDefautlDriver()
    {
        return $this->driver($this->defaultDriver);
    }

    /**
     * @param $name
     * @param null $default
     * @return mixed
     */
    public function get($name, $default = null)
    {
        return $this->getDefautlDriver()->get($name, $default);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function delete($name)
    {
        return $this->getDefautlDriver()->delete($name);
    }

    /**
     * @throws DriverNotFlushableException
     * @return $this
     */
    public function flush()
    {
        $driver = $this->getDefautlDriver();

        if ( ! $driver instanceof FlushableDriverInterface) {
            throw new DriverNotFlushableException(
                sprintf(
                    'Your default %s driver is not flushable',
                    $this->defaultDriver
                )
            );
        }

        $driver->flush();

        return $this;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @param int $time
     * @return mixed
     */
    public function set($name, $value, $time)
    {
         $driver = $this->getDefautlDriver();

        if ($driver instanceof TimeableDriverInterface) {
            $driver->set($name, $value, $time);
        }else{
            $driver->set($name, $value);
        }

        return $this;
    }
}

<?php

namespace Cable\Caching;


use Cable\Caching\Compressor\CompressorInterface;
use Cable\Caching\Driver\DriverInterface;
use Cable\Caching\Driver\FlushableDriverInterface;
use Cable\Caching\Driver\TimeableDriverInterface;
use Cable\Caching\Exceptions\DriverNotFlushableException;
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
    private static $compressorInterface = '\Cable\Caching\Compressor\CompressorInterface';


    /**
     * @var string
     */
    private static $driverContainer = 'caching.driver';

    /**
     * @var string
     */
    private static $driverInterface = 'Cable\Caching\Driver\DriverInterface';


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
            $name = $this->buildDriverName($driver),
            $callback
        );


        $this->container->addMethod($name, 'boot')
            ->withArgs([
                'configs' => $this->configs
            ]);



        $this->container->expect($name, static::$driverInterface);


        return $this;
    }

    private function addExpectations()
    {
        $this->container->expect(
            static::$compressorInterface,
            static::$compressorInterface
        );

    }

    /**
     * @return CompressorInterface
     *
     * @throws \ReflectionException
     * @throws ResolverException
     * @throws NotFoundException
     * @throws ExpectationException
     */
    public function getCompressor()
    {
        return $this->container
            ->resolve(
                static::$compressorInterface
            );
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

        if (isset($configs['compress']['status'])) {
            $this->compress = $configs['compress']['status'];
            unset($configs['compress']['status']);
        }


        $this->configs = $configs;
    }

    /**
     * @param string $name
     * @throws ResolverException
     * @throws NotFoundException
     * @throws ExpectationException
     * @throws \ReflectionException
     * @return DriverInterface
     */
    public function driver($name)
    {
        if (!isset($this->drivers[$name])) {

            $driver = $this
                ->container
                ->resolve(
                    $alias = $this->buildDriverName($name)
                );


            $this->container
                ->method($alias, 'boot');

            $this->drivers[$name] = $driver;

        }

        return $this->drivers[$name];
    }

    /**
     * @param string $name
     * @return string
     */
    private function buildDriverName($name)
    {
        return static::$driverContainer . '.' . $name;
    }

    /**
     *
     * @throws ExpectationException
     * @throws NotFoundException
     * @return CompressorInterface
     */
    public function resolveCompressor()
    {
        return $this->container->resolve(static::$compressorInterface, array(
            'configs' => $this->configs
        ));
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
        $value = $this->getDefautlDriver()->get($name, $default);

        if ($this->compress === true) {
            return $this->resolveCompressor()->uncompress($value);
        }

        return $value;
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

        if (!$driver instanceof FlushableDriverInterface) {
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
     * @throws ExpectationException
     * @throws ResolverException
     * @throws NotFoundException
     * @return mixed
     */
    public function set($name, $value, $time)
    {
        $driver = $this->getDefautlDriver();

        if (true === $this->compress) {
            $value = $this->resolveCompressor()->compress($value);
        }

        if ($driver instanceof TimeableDriverInterface) {
            $driver->set($name, $value, $time);
        } else {
            $driver->set($name, $value);
        }

        return $this;
    }
}

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
    protected $driver;

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
     * @var string
     */
    private static $serializerInterface = 'Cable\Caching\Serializer\SerializerInterface';

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
            ->withArgs(array(
                'configs' => $this->configs
            ));
        $this->container->expect($name, static::$driverInterface);
        return $this;
    }

    /**
     * @param string $alias
     * @param mixed $callback
     * @return $this
     */
    public function addSerializer($alias, $callback)
    {
        $this->container->add(
            $name = $this->prepareSerializerName($alias),
            $callback
        )->withArgs(array(
            'marks' => $alias
        ));

        $this->container->expect(
            $name,
            static::$serializerInterface
        );

        return $this;
    }

    /**
     * @param string $alias
     * @return string
     */
    private function prepareSerializerName($alias)
    {
        return 'caching.serailizer' . $alias;
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
            $this->driver = $configs['default'];
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
        $this->driver = $name;

        return $this;
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
     * @return DriverInterface
     * @throws ExpectationException
     * @throws ResolverException
     * @throws NotFoundException
     */
    private function resolveDriver()
    {
        if (is_string($this->driver)) {
            $this->driver = $this->container
                ->resolve($this->buildDriverName(
                    $this->driver
                ));
        }

        return $this->driver;
    }

    /**
     *
     * @throws ExpectationException
     * @throws ResolverException
     * @throws NotFoundException
     * @param $name
     * @param null $default
     * @return mixed
     */
    public function get($name, $default = null)
    {
        $value = $this->resolveDriver()->get($name, $default);

        if ($this->compress === true) {
            return $this->resolveCompressor()->uncompress($value);
        }

        return $value;
    }

    /**
     * @throws ExpectationException
     * @throws ResolverException
     * @throws NotFoundException
     * @param string $name
     * @return mixed
     */
    public function delete($name)
    {
        return $this->resolveDriver()->delete($name);
    }

    /**
     * @throws DriverNotFlushableException
     * @throws ExpectationException
     * @throws ResolverException
     * @throws NotFoundException
     * @return $this
     */
    public function flush()
    {
        $driver = $this->resolveDriver();

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
        $driver = $this->resolveDriver();

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

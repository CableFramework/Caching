<?php

namespace Cable\Caching;


use Cable\Caching\Compressor\CompressorInterface;
use Cable\Caching\Driver\DriverInterface;
use Cable\Caching\Driver\FlushableDriverInterface;
use Cable\Caching\Driver\TimeableDriverInterface;
use Cable\Caching\Exceptions\DriverNotFlushableException;
use Cable\Caching\Serializer\SerializerException;
use Cable\Caching\Serializer\SerializerInterface;
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
    private $configs = [];

    /**
     * @var string
     */
    protected $driver;


    /**
     * @var SerializerManager
     */
    private $serializerManager;

    /**
     * @var CompressorManager
     */
    private $compressorManager;

    /**
     * @var string
     */
    private static $driverContainer = 'caching.driver';


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

        $this->container->expect($name, DriverInterface::class);
        return $this;
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
     * @throws SerializerException
     * @throws CompressorException
     * @return mixed
     */
    public function set($name, $value, $time)
    {
        $driver = $this->resolveDriver();


        $value = $this->compressIsEnabled(
            $this->serializeIsNeeded($value)
        );

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

    /**
     * @param $value
     * @throws CompressorException
     * @return string
     */
    private function compressIsEnabled($value)
    {
        if (false === $this->compress) {
            return $value;
        }


        $minLength = isset($this->configs['compress']['min_length']) ?
            $this->configs['compress']['min_length'] :
            1024;

        if (strlen($value) < $minLength) {
            return $value;
        }

        list($compresor, $mark) = $this->compressorManager->getFirstCompressor();

        return $mark . $compresor->compress($value);
    }

    /**
     * @param $value
     * @return string
     * @throws SerializerException
     * @throws ExpectationException
     * @throws NotFoundException
     */
    private function serializeIsNeeded($value)
    {
        if (!is_object($value) || !is_array($value)) {
            return $value;
        }

        list($serializer, $mark) = $this->serializerManager->getFirstSerializer();


        $serializer = $this->resolveSerializer($serializer);


        /**
         * @var SerializerInterface $serializer
         */

        return $mark . $serializer->serialize($value);

    }

    /**
     * @param string $name
     * @throws ExpectationException
     * @throws NotFoundException
     * @return SerializerInterface
     */
    private function resolveSerializer($name)
    {

        $this->container->expect(
            $name = $this->serializerManager->prepareSerializerName($name),
            SerializerInterface::class
        );


        return $this->container->resolve($name);
    }

    /**
     *
     * @throws ExpectationException
     * @throws NotFoundException
     * @return CompressorInterface
     */
    public function resolveCompressor($name)
    {
        $this->container->expect(
            $name = $this->compressorManager->prepareCompressorName($name),
            CompressorInterface::class
        );


        return $this->container->resolve($name);
    }
}

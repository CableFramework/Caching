<?php

namespace Cable\Caching;


use Cable\Caching\Compressor\CompressorInterface;
use Cable\Caching\Driver\BootableDriverInterface;
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
     * @param SerializerManager $serializerManager
     * @param CompressorManager $compressorManager
     * @param array $configs
     */
    public function __construct(
        ContainerInterface $container,
        SerializerManager $serializerManager,
        CompressorManager $compressorManager,
        array $configs = [])
    {
        $this->container = $container;
        $this->serializerManager = $serializerManager;
        $this->compressorManager = $compressorManager;
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
     * @throws DriverException
     * @return void returns nothings
     */
    private function dispatchConfigs(array $configs)
    {
        if (!isset($configs['default'])) {
            throw new DriverException(
                'you didnot give an driver name to default'
            );
        }

        $this->driver = $configs['default'];
        unset($configs['default']);

        if (isset($configs['compress']['status'])) {
            $this->compress = $configs['compress']['status'];
            unset($configs['compress']['status']);
        }

        if (!isset($configs['compress']['default'])) {
            $configs['compress']['default'] = 'gz';
        }

        if (!isset($configs['serialize']['default'])) {
            $configs['compress']['default'] = 'json';
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

            $this->driver = $this->buildDriver();
        }

        return $this->driver;
    }

    /**
     *  builds selected driver
     *
     * @return mixed
     */
    private function buildDriver()
    {
        $driver = $this->container
            ->resolve($this->buildDriverName(
                $this->driver
            ));

        if ($driver instanceof BootableDriverInterface) {
            $driver->boot($this->configs);
        }

        return $driver;
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


        $value = $this->unSerialize(
            $this->unCompress($value)
        );

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

        list($compresor, $mark) = $this->getDefaultCompressor();

        return $mark . $compresor->compress($value);
    }

    /**
     * @param $value
     * @return mixed
     */
    private function unSerialize($value)
    {
        if (strpos($value, '{{s.') === false) {
            return $value;
        }

        $serializers = $this->serializerManager
            ->getSerializers();

        foreach ($serializers as list($name, $mark)) {

            if (strpos($value, $mark) !== false) {
                return $this->resolveSerializer($name)
                    ->unserialize($value);
            }
        }
    }

    /**
     * @param string $value
     * @return mixed
     */
    private function unCompress($value)
    {
        if (strpos($value, '{{c.') === false) {
            return $value;
        }

        $compressors = $this->compressorManager->getCompressors();

        foreach ($compressors as list($name, $mark)) {

            if (strpos($value, $mark) !== false) {
                return $this->resolveCompressor($name)
                    ->uncompress($value);
            }
        }

        return $value;
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

        list($serializer, $mark) = $this->getDefaultSerializer();


        $serializer = $this->resolveSerializer($serializer);


        /**
         * @var SerializerInterface $serializer
         */

        return $mark . $serializer->serialize($value);

    }

    /**
     * @return array
     * @throws CompressorException
     */
    private function getDefaultCompressor()
    {
        $default = $this->configs['compress']['default'];

        if (!$this->compressorManager->has($default)) {
            throw new CompressorException(
                sprintf(
                    '%s compressor driver not found',
                    $default
                )
            );
        }

        return $this->compressorManager->getCompressor($default);
    }


    /**
     * @return array
     * @throws SerializerException
     */
    private function getDefaultSerializer()
    {
        $default = $this->configs['serializer']['default'];

        if (!$this->serializerManager->has($default)) {
            throw new SerializerException(
                sprintf(
                    '%s serializer driver not found',
                    $default
                )
            );
        }

        return $this->serializerManager->getSerializer($default);
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

        $this->container->getArgumentManager()
            ->setClassArgs($name, array(
                'configs' => $this->configs
            ));


        return $this->container->resolve($name);
    }
}

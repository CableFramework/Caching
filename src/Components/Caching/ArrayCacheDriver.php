<?php
namespace Cable\Caching;


use Cable\Caching\Driver\DriverInterface;
use Cable\Caching\Driver\FlushableDriverInterface;
use Cable\Caching\Driver\TimeableDriverInterface;

class ArrayCacheDriver implements DriverInterface, FlushableDriverInterface, TimeableDriverInterface
{

    protected static $vars;


    /**
     * @param $name
     * @param null $default
     * @return mixed
     */
    public function get($name, $default = null)
    {
        if (isset(static::$vars[$name])) {
            $var = static::$vars[$name];


            // if cache is expired, we will delete it
            if (time() > $var['time']) {
                $this->delete($name);

                return $default;
            }


            return $var['value'];
        }

        return $default;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function delete($name)
    {
        unset(static::$vars[$name]);

        return $this;
    }

    /**
     * @return $this
     */
    public function flush()
    {
        static::$vars = [];

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
        static::$vars[$name] = [
            'value' => $value,
            'time' => time() + $time
        ];

        return $this;
    }
}
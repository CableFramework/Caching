<?php

namespace Cable\Caching;


class SerializerManager
{

    /**
     * @var array[]
     */
    private static $serializers;

    /**
     * @param string $alias
     * @return string
     */
    public function prepareSerializerName($alias)
    {
        return 'caching.serailizer.' . $alias;
    }

    /**
     * @param $name
     * @return bool
     */
    public function has($name)
    {
        return isset(static::$serializers[$name]);
    }
    /**
     * @param string $name
     * @return array
     */
    public function getSerializer($name)
    {
        return static::$serializers[$name];
    }
    /**
     * @param string $name
     * @return array
     */
    public function getSerializers()
    {
        return static::$serializers;
    }

    /**
     * @param string $alias
     * @param mixed $callback
     */
    public static function addSerializer($alias, $callback)
    {
        static::$serializers[$alias] = array($alias, '{{s.' . $callback . '}}');
    }

}

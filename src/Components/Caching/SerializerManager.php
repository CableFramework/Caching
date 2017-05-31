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
     * @return mixed
     */
    public function getFirstSerializer()
    {

        if (count(static::$serializers) === 0) {
            throw new SerializerException('You did not add any serializer');
        }

        return static::$serializer[0];
    }

    /**
     * @param string $alias
     * @param mixed $callback
     */
    public static function addSerializer($alias, $callback)
    {
        static::$serializers[] = array($alias, '{{s.' . $callback . '}}');
    }

}

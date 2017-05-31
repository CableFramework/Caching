<?php

namespace Cable\Caching;


class CompressorManager
{

    /**
     * @var array[]
     */
    private static $compressors;


    /**
     * @param string $alias
     * @return string
     */
    public function prepareCompressorName($alias)
    {
        return 'caching.compressor.' . $alias;
    }


    /**
     * @param string $alias
     * @param mixed $callback
     */
    public static function addCompressor($alias, $callback)
    {
        static::$compressors[$alias] = array($alias, '{{c.' . $callback . '}}');
    }

    /**
     * @return array[]
     */
    public function getCompressor($name)
    {
        return self::$compressors[$name];
    }

    /**
     * @param $name
     * @return bool
     */
    public function has($name)
    {
        return isset(static::$compressors[$name]);
    }

    /**
     * @return array[]
     */
    public function getCompressors()
    {
        return self::$compressors;
    }

    /**
     * @param \array[] $compressors
     * @return  $this
     */
    public function setCompressors($compressors)
    {
        self::$compressors = $compressors;

        return $this;
    }

}

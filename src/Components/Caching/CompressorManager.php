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
     * @return array
     * @throws CompressorException
     */
    public function getFirstCompressor(){
        if (count(static::$compressors) === 0) {
            throw new CompressorException(
                'You did not added any compressor'
            );
        }

        return static::$compressors[0];
    }


    /**
     * @param string $alias
     * @param mixed $callback
     */
    public static function addCompressor($alias, $callback)
    {
        static::$compressors[] = array($alias, '{{c.' . $callback . '}}');
    }
}

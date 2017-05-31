<?php

namespace Cable\Caching\Serializer;

/**
 * Interface SerializerInterface
 * @package Cable\Caching\Serializer
 */
interface SerializerInterface
{

    /**
     * @param mixed $value
     * @return string
     */
    public function serialize($value);

    /**
     * @param string $value
     * @return mixed
     */
    public function unserialize($value);
}

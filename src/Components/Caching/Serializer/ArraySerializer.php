<?php

namespace Cable\Caching\Serializer;


class ArraySerializer extends Serializer implements SerializerInterface
{
    /**
     * @param mixed $value
     * @return string
     */
    public function serialize($value)
    {
        return serialize($value);
    }

    /**
     * @param string $value
     * @return mixed
     */
    public function unserialize($value)
    {
        return $this->unserialize($value);
    }
}
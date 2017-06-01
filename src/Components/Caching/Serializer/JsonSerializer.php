<?php

namespace Cable\Caching\Serializer;


class JsonSerializer extends Serializer implements SerializerInterface
{


    /**
     * @param mixed $value
     * @return string
     */
    public function serialize($value)
    {
        return json_encode($value);
    }

    /**
     * @param string $value
     * @return mixed
     */
    public function unserialize($value)
    {
        return json_decode($value);
    }
}

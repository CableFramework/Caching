<?php
namespace Cable\Components\Caching\Driver;


interface TimeableDriverInterface
{

    /**
     * @param string $name
     * @param mixed $value
     * @param int $time
     * @return mixed
     */
    public function set($name, $value, $time);

}

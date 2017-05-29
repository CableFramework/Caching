<?php

namespace Cable\Components\Caching\Driver;


abstract class DriverAbstraction
{

    /**
     * @param $name
     * @param null $default
     * @return mixed
     */
    abstract public function get($name, $default = null);

    /**
     * @param string $name
     * @return mixed
     */
    abstract public function delete($name);

}

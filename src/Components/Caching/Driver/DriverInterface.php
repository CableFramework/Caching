<?php

namespace Cable\Caching\Driver;

/**
 * interface DriverAbstraction
 * @package Cable\Components\Caching\Driver
 */
interface DriverInterface
{

    /**
     * @param $name
     * @param null $default
     * @return mixed
     */
    public function get($name, $default = null);

    /**
     * @param string $name
     * @return mixed
     */
    public function delete($name);

}

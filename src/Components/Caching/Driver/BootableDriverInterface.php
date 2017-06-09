<?php
namespace Cable\Caching\Driver;


interface BootableDriverInterface
{

    /**
     * @param array $configs
     * @return mixed
     */
    public function boot($configs = array());
}

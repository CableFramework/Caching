<?php
namespace Cable\Components\Caching;


interface BootableDriverInterface
{

    /**
     * @param array $configs
     * @return mixed
     */
    public function boot($configs = array());
}

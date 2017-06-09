<?php

namespace Cable\Caching;


use Cable\Container\ServiceProvider;

class ArrayCacheProvider extends ServiceProvider
{
    /**
     *  no need here
     */
    public function boot()
    {}


    /**
     *  added array cache driver
     */
    public function register()
    {
        $this->getContainer()->resolve('caching')->addDriver('array', ArrayCacheDriver::class);
    }

}
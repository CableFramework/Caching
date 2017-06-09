<?php

namespace Cable\Caching\Compressor;


use Cable\Caching\CompressorManager;
use Cable\Container\ServiceProvider;

class CompressorProvider extends ServiceProvider
{

    /**
     * register new providers or something
     *
     * @return mixed
     */
    public function boot()
    {

    }

    /**
     * register the content
     *
     * @return mixed
     */
    public function register()
    {
        $this->getContainer()
            ->add('caching.compressor.gz', GzCompressor::class);

        CompressorManager::addCompressor('gz', 'gz');
    }
}
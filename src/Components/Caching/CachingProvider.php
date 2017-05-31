<?php
namespace Cable\Caching;


use Cable\Caching\Compressor\CompressorProvider;
use Cable\Caching\Serializer\ArraySerializerProvider;
use Cable\Caching\Serializer\JsonSerializerProvider;
use Cable\Container\ProviderException;
use Cable\Container\ServiceProvider;

class CachingProvider extends ServiceProvider
{

    /**
     * register new providers or something
     *
     * @throws ProviderException
     * @return mixed
     */
    public function boot()
    {
        $this->getContainer()
            ->addProvider(ArraySerializerProvider::class)
            ->addProvider(JsonSerializerProvider::class)
            ->addProvider(CompressorProvider::class);
    }

    /**
     * register the content
     *
     * @return mixed
     */
    public function register()
    {
      
    }
}

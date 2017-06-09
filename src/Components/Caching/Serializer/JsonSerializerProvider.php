<?php

namespace Cable\Caching\Serializer;


use Cable\Caching\SerializerManager;
use Cable\Container\ServiceProvider;

class JsonSerializerProvider extends ServiceProvider
{

    /**
     * register new providers or something
     *
     * @return mixed
     */
    public function boot()
    {
        // do nothing
    }

    /**
     * register the content
     *
     * @throws ResolverException
     * @return mixed
     */
    public function register()
    {
        $this->getContainer()
            ->add($alias = 'caching.serializer.json', JsonSerializer::class);

        SerializerManager::addSerializer('json', 'json');
    }
}
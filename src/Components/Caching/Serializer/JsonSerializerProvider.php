<?php

namespace Cable\Caching\Serializer;


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
     * @return mixed
     */
    public function register()
    {
        $caching = $this->getContainer()
            ->resolve('caching');

        $caching->addSerializer('json', JsonSerializer::class);
    }
}
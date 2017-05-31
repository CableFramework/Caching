<?php

namespace Cable\Caching\Serializer;


use Cable\Container\Resolver\ResolverException;
use Cable\Container\ServiceProvider;

class ArraySerializerProvider extends ServiceProvider
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
            ->add($alias = 'caching.serializer.array', function () {
                return new ArraySerializer('array');
            });
    }
}
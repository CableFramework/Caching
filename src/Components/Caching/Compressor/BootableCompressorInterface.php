<?php

namespace Cable\Caching\Compressor;


interface BootableCompressorInterface
{
    /**
     * @param array $configs
     * @throws CompressorExtensionNotSupportedException
     * @return mixed
     */
    public function boot(array $configs = []);
}
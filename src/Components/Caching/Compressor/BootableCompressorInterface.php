<?php
namespace Cable\Caching\Compressor;


interface BootableCompressorInterface extends CompressorInterface
{

    /**
     * @param array $configs
     * @return mixed
     */
    public function boot(array $configs = []);
}

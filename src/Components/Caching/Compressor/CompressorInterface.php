<?php

namespace Cable\Components\Caching\Compressor;

/**
 * Interface CompressorInterface
 * @package Cable\Components\Caching\Compressor
 */
interface CompressorInterface
{

    /**
     * @param mixed $data
     * @return string
     */
    public function compress($data);

    /**
     * @param string $data
     * @return mixed
     */
    public function uncompress($data);
}

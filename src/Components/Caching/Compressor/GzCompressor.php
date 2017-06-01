<?php

namespace Cable\Caching\Compressor;

/**
 * Class GzCompressor
 * @package Cable\Caching\Compressor
 */
class GzCompressor implements BootableCompressorInterface, CompressorInterface
{

    /**
     * @var integer
     */
    private $compressLevel;

    /**
     * @param array $configs
     * @throws CompressorExtensionNotSupportedException
     * @return mixed
     */
    public function boot(array $configs = [])
    {
        if ( ! extension_loaded('ob_gzhandler') || ! function_exists('gzencode')) {
            throw new CompressorExtensionNotSupportedException(
                'Gz extension is not loaded in your system, please check it'
            );
        }

        $this->compressLevel = isset($configs['compress']['level']) ?
            $configs['compress']['level'] :
            9;
    }

    /**
     * @param mixed $data
     * @return string
     */
    public function compress($data)
    {
        return gzcompress($data, $this->compressLevel);
    }

    /**
     * @param string $data
     * @return mixed
     */
    public function uncompress($data)
    {
        return gzuncompress($data);
    }
}

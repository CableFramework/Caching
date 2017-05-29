<?php
namespace Cable\Components\Caching\Driver;

/**
 * Interface NotTimeableDriverInterface
 * @package Cable\Components\Caching\Driver
 */
interface NotTimeableDriverInterface
{

    /**
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public function set($name, $value);
}

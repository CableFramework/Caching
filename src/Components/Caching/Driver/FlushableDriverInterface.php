<?php
namespace Cable\Components\Caching\Driver;


interface FlushableDriverInterface
{

    /**
     * @return $this
     */
    public function flush();
}

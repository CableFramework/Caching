<?php
namespace Cable\Caching\Driver;


interface FlushableDriverInterface
{

    /**
     * @return $this
     */
    public function flush();
}

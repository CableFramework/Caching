<?php


namespace Cable\Components\Caching;


interface FlushableDriverInterface
{

    /**
     * @return $this
     */
    public function flush();
}

<?php

namespace Cable\Caching\Serializer;

class Serializer
{

    /**
     * @var string
     */
    protected $mark;

    /**
     * Serializer constructor.
     * @param null $mark
     */
    public function __construct($mark = null)
    {
        if (null !== $mark) {
            $this->setMark($mark);
        }
    }

    /**
     * @return string
     */
    public function getMark()
    {
        return $this->mark;
    }

    /**
     * @param string $mark
     * @return Serializer
     */
    public function setMark($mark)
    {
        $this->mark = '{{s.' . $mark . '}}';
        return $this;
    }

}

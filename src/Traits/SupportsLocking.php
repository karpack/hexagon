<?php

namespace Karpack\Hexagon\Traits;

trait SupportsLocking
{
    /**
     * Returns the DB lock sttaus of the model.
     * 
     * @var bool
     */
    protected $locked;

    /**
     * Sets the db lock status of the model entry
     * 
     * @param bool $locked
     * @return
     */
    public function setLocked($locked = true)
    {
        $this->locked = $locked;

        return $this;
    }

    /**
     * Returns the DB lock status of the model
     * 
     * @return bool
     */
    public function isLocked()
    {
        return !!$this->locked;
    }
}
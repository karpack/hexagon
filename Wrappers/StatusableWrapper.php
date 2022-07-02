<?php

namespace App\Modules\Abstracts\Wrappers;

use App\Modules\Abstracts\Events\StatusChanged;
use App\Modules\Abstracts\Traits\WrapperUpdatesStatus;
use App\Modules\Abstracts\Wrappers\Contracts\Statusable;

abstract class StatusableWrapper extends SimpleModelWrapper implements Statusable
{
    use WrapperUpdatesStatus;

    /**
     * Flag that controls the dispatch status change events
     * 
     * @var bool
     */
    protected $dispatchStatusEvents = true;

    /**
     * Saves the underlying model.
     * 
     * @return bool
     */
    public function save()
    {
        $canRaiseStatusChangeEvent = $this->hasStatusChanged();

        $result = $this->model->save();

        if ($canRaiseStatusChangeEvent && $this->dispatchStatusEvents) {
            $this->broadcast();

            event(new StatusChanged($this));
        }

        return $result;
    }

    /**
     * Sets the wrapper to avoid rasing status change events and broadcasting.
     * 
     * @return $this
     */
    public function withoutStatusEvents()
    {
        $this->dispatchStatusEvents = false;

        return $this;
    }
}

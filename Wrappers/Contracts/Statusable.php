<?php

namespace App\Modules\Abstracts\Wrappers\Contracts;

interface Statusable
{
    /**
     * Sets the status on the model. This does not save it to the database.
     * 
     * @param string $statusIdentifier
     * @return static
     */
    public function setStatus($statusIdentifier);

    /**
     * Updates the status of the model. Saves the changes in the database.
     * 
     * @param string $status
     * @return bool
     */
    public function updateStatus($statusIdentifier);

    /**
     * Checks the status of the underlying model matches the given one.
     * 
     * @param string $status
     * @return bool
     */
    public function statusIs($status);

    /**
     * Returns true if the model status is dirty
     * 
     * @return bool
     */
    public function hasStatusChanged();

    /**
     * Returns all the registered status events
     * 
     * @return array
     */
    public function registeredStatusEvents();

    /**
     * Returns all the registered status broadcast identifiers
     * 
     * @return array
     */
    public function registeredStatusBroadcasts();

    /**
     * Raise broadcast if the current status has a listener registered.
     * 
     * @return void
     */
    public function broadcast();

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastChannels();

    /**
     * Returns the payload that has to be broadcasted.
     * 
     * @return array
     */
    public function broadcastData();
}

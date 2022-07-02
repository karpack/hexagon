<?php

namespace App\Modules\Abstracts\Wrappers\Contracts;

use Illuminate\Support\Collection;

interface Updateable extends ModelWrapper
{
    /**
     * Updates the model with the given data and saves it on the database.
     * 
     * @param \Illuminate\Support\Collection $data
     * @return bool
     */
    public function update(Collection $data);

    /**
     * Pushes the patches onto the model.
     * 
     * @param \Illuminate\Support\Collection $data
     * @return bool
     */
    public function patch(Collection $data);

    /**
     * Validates the given data with the rules of this model.
     * 
     * @param \Illuminate\Support\Collection $data
     * @param \Illuminate\Database\Eloquent\Model|null $model
     * @return static
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validate(Collection $data, $model = null);

    /**
     * Sets the the given data on the model. This won't save it to the database.
     * 
     * @param \Illuminate\Support\Collection $data
     * @return static
     */
    public function setData(Collection $data);

    /**
     * Updates the model with the given data and saves it on the database.
     * 
     * @param \Illuminate\Support\Collection $data
     * @return bool
     */
    public function saveData(Collection $data);

    /**
     * Saves the underlying model.
     * 
     * @return bool
     */
    public function save();

    /**
     * Deletes the model from the database.
     * 
     * @return bool|null
     */
    public function delete();
}

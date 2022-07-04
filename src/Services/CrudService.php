<?php

namespace Karpack\Hexagon\Services;

use Karpack\Contracts\Hexagon\Services\CrudManager;

abstract class CrudService extends ModelWrapperService implements CrudManager
{
     /**
     * Creates a new model from the given data, stores it into the database and returns
     * the same.
     * 
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $data)
    {
        $data = collect($data);

        $class = $this->modelClass();

        $wrapper = $this->resolveWrapper(new $class)->validate($data);
        
        $wrapper->saveData($data);

        return $wrapper->model();
    }

    /**
     * Updates the details of the given model with the given data and returns the same
     * model.
     * 
     * @param \Illuminate\Database\Eloquent\Model|int $modelOrId
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function update($modelOrId, array $data)
    {
        $wrapper = $this->resolveWrapper($this->get($modelOrId));

        $wrapper->update(collect($data));

        return $wrapper->model();
    }

    /**
     * Patches the details of the given model with the given data and returns the same
     * model.
     * 
     * @param \Illuminate\Database\Eloquent\Model|int $modelOrId
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function patch($modelOrId, array $data)
    {
        $wrapper = $this->resolveWrapper($this->get($modelOrId));

        $wrapper->patch(collect($data));

        return $wrapper->model();
    }

    /**
     * Deletes the given model from the database.
     * 
     * @param \Illuminate\Database\Eloquent\Model|int $modelOrId
     * @return bool
     */
    public function delete($modelOrId)
    {
        return $this->resolveWrapper($this->get($modelOrId))->delete();
    }
}
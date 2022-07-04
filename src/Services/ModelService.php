<?php

namespace Karpack\Hexagon\Services;

use Karpack\Contracts\Hexagon\Services\ModelGetter;

abstract class ModelService implements ModelGetter
{
    /**
     * Returns the model class on which this repository operates.
     * 
     * @return class
     */
    protected abstract function modelClass();

    /**
     * Boots the model service
     * 
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Returns the item collection from the paginator.
     * 
     * @param \Illuminate\Pagination\AbstractPaginator $paginator
     * @return \Illuminate\Support\Collection
     */
    protected function paginatorCollection($paginator)
    {
        return $paginator->getCollection();
    }

    /**
     * Returns a model from the given model id.
     * 
     * @param int|array|\Karpack\Hexagon\Models\Model $id
     * @param bool $fail Set this to false to prevent throwing exception on failure.
     * @return \Illuminate\Support\Collection|\Karpack\Hexagon\Models\Model|null
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getLocked($id, $fail = true)
    {
        return $this->get($id, $fail, true);
    }

    /**
     * Returns a model from the given model id.
     * 
     * @param int|array|\Karpack\Hexagon\Models\Model $id
     * @param bool $fail Set this to false to prevent throwing exception on failure.
     * @param bool $lock Set this to lock the row on the table
     * @return \Illuminate\Support\Collection|\Karpack\Hexagon\Models\Model|null
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function get($id, $fail = true, $lock = false)
    {
        $class = $this->modelClass();

        if ($id instanceof $class) {
            // If lock is not required and the given $id is the model itself, return
            // the model as is. No need to fetch again from the DB.
            if (!$lock || (method_exists($id, 'isLocked') && $id->isLocked())) {
                return $id;
            }

            // If lock is required, we'll fetch the model again inorder to enable DB 
            // lock on the primary key and continue with building query.
            $id = $id->getKey();
        }
        $query = $this->getQuery();

        if ($lock) {
            $query = $query->lockForUpdate();
        }

        $result = null;

        if ($fail) {
            $result = $query->findOrFail($id);
        } else if (!is_null($id)) {
            $result = $query->find($id);
        }

        if ($result && method_exists($result, 'setLocked')) {
            $result->setLocked($lock);
        }

        return $result;
    }

    /**
     * Returns locked model from the given query
     * 
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query
     * @param bool $fail
     * @return \Karpack\Hexagon\Models\Model
     */
    public function getLockedFromQuery($query, $fail = true)
    {
        return $this->getFromQuery($query, $fail, true);
    }

    /**
     * Returns model from the given query
     * 
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query
     * @param bool $fail
     * @param bool $lock
     * @return \Karpack\Hexagon\Models\Model
     */
    public function getFromQuery($query, $fail = true, $lock = false)
    {
        if ($lock) {
            $query->lockForUpdate();
        }

        $result = null;

        if ($fail) {
            $result = $query->firstOrFail();
        } else {
            $result = $query->first();
        }

        if ($result && method_exists($result, 'setLocked')) {
            $result->setLocked($lock);
        }

        return $result;
    }

    /**
     * Returns a query object on the underlying repo model. This function allows 
     * us to update the query conditions without overriding the `get` method.
     * 
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function getQuery()
    {
        $class = $this->modelClass();

        return $class::query();
    }
}
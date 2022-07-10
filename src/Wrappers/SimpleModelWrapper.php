<?php

namespace Karpack\Hexagon\Wrappers;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Karpack\Contracts\Hexagon\Wrappers\Updateable;
use Karpack\Contracts\Hexagon\Wrappers\ValidatesModel;

abstract class SimpleModelWrapper extends BaseModelWrapper implements Updateable, ValidatesModel
{
    /**
     * An anonymous function that takes three arguments `$value`, `$key` and `$model` in the 
     * same order. The field has to be set like `$model->$key = $value`
     * 
     * @var \callable
     */
    protected $fieldSetter = null;

    /**
     * Updates the model with the given data and saves it on the database.
     * 
     * @param \Illuminate\Support\Collection $data
     * @return bool
     */
    public function update(Collection $data)
    {
        return $this->validate($data, $this->model())->saveData($data);
    }

    /**
     * Validates the given data with the rules of this model.
     * 
     * @param \Illuminate\Support\Collection $data
     * @param \Illuminate\Database\Eloquent\Model|null $model
     * @return $this
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validate(Collection $data, $model = null)
    {
        Validator::make($data->all(), $this->validationRules($model))->validate();

        return $this;
    }

    /**
     * Sets the given attribute with the value from collection if it exists.
     * 
     * @param \Illuminate\Support\Collection $data
     * @return $this
     */
    protected function setOptionalField(Collection $data, $attributeName, $keyInData = null)
    {
        $keyInData = $keyInData ?? $attributeName;

        if ($data->has($keyInData)) {
            $this->model->$attributeName = $data->get($keyInData);
        }
        return $this;
    }

    /**
     * Pushes the patches onto the model.
     * 
     * @param \Illuminate\Support\Collection $data
     * @return bool
     */
    public function patch(Collection $data)
    {
        $supportedFields = $this->patchableFields();

        $data = $data->filter(function ($item, $key) use ($supportedFields) {
            return in_array($key, $supportedFields);
        });

        Validator::make(
            $data->all(),
            Arr::only($this->validationRules($this->model()), $data->keys()->all())
        )->validate();

        $fieldSetter = $this->getFieldSetter();

        $data->each(function ($value, $key) use ($fieldSetter) {
            $fieldSetter($value, $key, $this->model);
        });

        return $this->save();
    }

    /**
     * Returns an array of patchable supported by model.
     * 
     * @return array
     */
    protected function patchableFields()
    {
        return [];
    }

    /**
     * Sets a new field setter on the wrapper which will be used for seting fields on the model.
     *  
     * @param \callable $fieldSetter
     * @return $this
     */
    public function setFieldSetter($fieldSetter)
    {
        if (!is_callable($fieldSetter)) {
            throw new Exception('The field setter has to be an anonymous function');
        }
        $this->fieldSetter = $fieldSetter;

        return $this;
    }

    /**
     * Returns an anonymous function that can be used to set field on the model.
     * 
     * @return \callable
     */
    protected function getFieldSetter()
    {
        if (!is_callable($this->fieldSetter)) {
            return $this->fieldSetter = function ($value, $key, $model) {
                $model->$key = $value;
            };
        }
        return $this->fieldSetter;
    }

    /**
     * Updates the model with the given data and saves it on the database.
     * 
     * @param \Illuminate\Support\Collection $data
     * @return bool
     */
    public function saveData(Collection $data)
    {
        $result = $this->setData($data)->save();

        $this->onSave($data);

        return $result;
    }

    /**
     * Listener function that gets executed after saving the model. Override this function
     * to do some process after saving.
     * 
     * @param \Illuminate\Support\Collection $data
     * @return static
     */
    protected function onSave(Collection $data)
    {
        return $this;
    }

    /**
     * Saves the underlying model.
     * 
     * @return bool
     */
    public function save()
    {
        return $this->model->save();
    }

    /**
     * Deletes the model from the database.
     * 
     * @return bool|null
     */
    public function delete()
    {
        return $this->model->delete();
    }
}

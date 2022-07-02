<?php

namespace App\Modules\Abstracts\Wrappers;

use JsonSerializable;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Support\Arrayable;
use App\Modules\Abstracts\Wrappers\Contracts\ModelWrapper;
use Illuminate\Queue\SerializesAndRestoresModelIdentifiers;
use ReflectionClass;

class BaseModelWrapper implements ModelWrapper, Arrayable, JsonSerializable
{
    use SerializesAndRestoresModelIdentifiers;

    /**
     * The model on which this wrapper operates.
     * 
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * Creates a new wrapper around the given model.
     * 
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Returns the underlying model of this ModelWrapper.
     * 
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function model()
    {
        return $this->model;
    }

    /**
     * Updates the model with the given one. The update takes place only if the given model
     * class matches the existing model.
     * 
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return static
     * @throws \InvalidArgumentException
     */
    protected function updateModel($model)
    {
        if (get_class($this->model) !== get_class($model)) {
            throw new InvalidArgumentException();
        }
        $this->model = $model;

        return $this;
    }

    /**
     * Returns the alias that can be used to get the model used by this wrapper.
     * 
     * For example, this function will return `category` for the `CategoryWrapper` class.
     * The magic methods defined will ensure that calling function or properties using this
     * alias returns the model itself.
     * 
     * @return string
     */
    protected function modelName()
    {
        return Str::camel(
            Str::replaceLast('Wrapper', '', class_basename(static::class))
        );
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->model->toArray();
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Convert the model to its string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->jsonSerialize());
    }

    /**
     * Returns the model if the property name matches the model name.
     * 
     * @param string $name Requested property name.
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function __get($name)
    {
        if ($this->modelName() === $name) {
            return $this->model;
        }
        return $this->model->{$name};
    }

    /**
     * Returns the model, if the method name matches the model name. 
     * 
     * If it doesn't we will try to fetch a property from the underlying model. The underlying 
     * property should be in snake_case. That is the standard used for Laravel attributes.
     * 
     * For example, to fetch first_name field from user wraper, we can call $userWrapper->firstName()
     * Calling the above function will fetch the first_name field from the underlying user if no
     * firstName() exists in this wrapper.
     * 
     * This allows us to avoid unnecessary getters in every model wrapper. We will use PHPDocs for IDE
     * intellisense.
     * 
     * @param string $name Method name
     */
    public function __call($name, $arguments)
    {
        if ($this->modelName() === Str::camel($name)) {
            return $this->model;
        }
        $snake_cased_fn_name = Str::snake($name);

        if (!is_null($value = $this->model->{$snake_cased_fn_name})) {
            return $value;
        }
        return $this->model->{$name};
    }

    /**
     * Prepare the instance values for serialization.
     *
     * @return array
     */
    public function __serialize()
    {
        $values = [];

        $values['model'] = $this->getSerializedPropertyValue($this->model);

        return $values;
    }

    /**
     * Restore the model after serialization.
     *
     * @param  array  $values
     * @return array
     */
    public function __unserialize(array $values)
    {
        $this->model = $this->getRestoredPropertyValue($values['model']);

        // We'll create a clone of this wrapper without any sorts of initialization.
        // This way, we can just inject necessary services to the wrapper and everything
        // else can be the default value.
        $clone = app()->make(static::class, ['model' => $this->model]);

        foreach ((new ReflectionClass($clone))->getProperties() as $property) {
            if ($property->isStatic()) {
                continue;
            }
            $property->setAccessible(true);

            if (!$property->isInitialized($clone)) {
                continue;
            }

            // We'll use the clone properties and set the clone properties values to this
            // objects properties.
            $this->{$property->getName()} = $property->getValue($clone);
        }

        return $values;
    }
}

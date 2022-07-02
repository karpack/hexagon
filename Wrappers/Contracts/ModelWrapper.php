<?php

namespace App\Modules\Abstracts\Wrappers\Contracts;

use JsonSerializable;
use Illuminate\Contracts\Support\Arrayable;

interface ModelWrapper extends Arrayable, JsonSerializable
{
    /**
     * Returns the underlying model of the ModelService.
     * 
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function model();
}
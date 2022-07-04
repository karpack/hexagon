<?php

namespace Karpack\Hexagon\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Karpack\Hexagon\Traits\SupportsLocking;

class Model extends EloquentModel
{
    use SupportsLocking;
}
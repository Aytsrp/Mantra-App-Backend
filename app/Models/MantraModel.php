<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

abstract class MantraModel extends Model
{
    protected $connection = 'mantra';

    protected $guarded = ['*'];
}
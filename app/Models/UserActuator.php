<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserActuator extends Model
{
    use HasFactory;

    public function actuator()
    {
        return $this->belongsTo(Actuator::class);
    }
}

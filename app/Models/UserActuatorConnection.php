<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserActuatorConnection extends Model
{
    use HasFactory;

    public function actuator_pin()
    {
        return $this->belongsTo(ActuatorPin::class);
    }

    public function board_pin()
    {
        return $this->belongsTo(BoardPin::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActuatorPin extends Model
{
    use HasFactory;

    public function actuator()
    {
        return $this->belongsTo(Board::class);
    }

    public function pin_type()
    {
        return $this->belongsTo(PinType::class);
    }
}

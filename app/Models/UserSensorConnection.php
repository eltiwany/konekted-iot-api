<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSensorConnection extends Model
{
    use HasFactory;

    public function sensor_pin()
    {
        return $this->belongsTo(SensorPin::class);
    }

    public function board_pin()
    {
        return $this->belongsTo(BoardPin::class);
    }
}

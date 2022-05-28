<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SensorPin extends Model
{
    use HasFactory;

    public function sensor()
    {
        return $this->belongsTo(Board::class);
    }

    public function pin_type()
    {
        return $this->belongsTo(PinType::class);
    }
}

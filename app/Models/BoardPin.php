<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoardPin extends Model
{
    use HasFactory;

    public function board()
    {
        return $this->belongsTo(Board::class);
    }

    public function pin_type()
    {
        return $this->belongsTo(PinType::class);
    }
}

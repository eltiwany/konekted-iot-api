<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBoard extends Model
{
    use HasFactory;

    public function board()
    {
        return $this->belongsTo(Board::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonumentLanguage extends Model
{
    use HasFactory;

    public function monument()
    {
        return $this->belongsTo(Monument::class);
    }
}

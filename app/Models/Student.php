<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Student extends Authenticatable
{
    use HasFactory;
    protected $guarded = [
        'id'
    ];

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamSession extends Model
{
    use HasFactory;
    protected $guarded = [
        'id'
    ];

    public function exam_groups()
    {
        return $this->hasMany(ExamGroup::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
}

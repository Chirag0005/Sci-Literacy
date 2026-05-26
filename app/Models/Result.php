<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id', 'score', 'total_questions', 'ai_feedback'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

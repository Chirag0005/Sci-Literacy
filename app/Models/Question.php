<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'question_text', 'category', 'option_a', 'option_b', 'option_c', 'option_d', 'correct_option', 'explanation'
    ];
}

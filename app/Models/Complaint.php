<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $fillable = [
        'upload_id',
        'comment',
        'is_close'
    ];

    protected $casts = [
        'is_close' => 'boolean'
    ];
}

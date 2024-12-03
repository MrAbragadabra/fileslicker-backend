<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $fillable = [
        'upload_id',
        'file_path',
        'file_size',
        'file_name'
    ];
}

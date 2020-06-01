<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class TaskUserCheck extends Model
{
	protected $table = 'cs_task_user_check';
	protected $dateFormat = 'U';
	
	protected $guarded = [];

	protected $casts = [
        'images' => 'array',
    ];
}

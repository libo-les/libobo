<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class TaskUserShareRecord extends Model
{
    protected $table = 'cs_task_user_share_record';
    protected $dateFormat = 'U';
    
    protected $guarded = [];
}

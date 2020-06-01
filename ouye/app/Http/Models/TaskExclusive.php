<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class TaskExclusive extends Model
{
    protected $table = 'cs_task_exclusive';
    protected $dateFormat = 'U';
    
    protected $guarded = [];

    public function task()
    {
        return $this->hasOne('App\Http\Models\Task', 'id', 'task_id');
    }
}

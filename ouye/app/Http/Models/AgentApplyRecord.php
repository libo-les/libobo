<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class AgentApplyRecord extends Model
{
    protected $table = 'cs_agent_apply_record';
    protected $dateFormat = 'U';
    
    protected $guarded = [];
}

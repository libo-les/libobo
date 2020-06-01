<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class AgentApply extends Model
{
    protected $table = 'cs_agent_apply';
    protected $dateFormat = 'U';
    
    protected $guarded = [];
}

<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;  
use App\Http\Models\Region;

class RegionController extends Controller
{
    public function index(Request $request)
    {
    	$type = $request->input('type');
    	$id = $request->input('id');
    	switch ($type) {
    		case '1':
    				$res = Region::select('id','name')->where('parent_id',0)->get()->toArray();
    			break;
    		
    		default:
    				$res = Region::where('parent_id',$id)->get()->toArray();
    			break;
    	}

    	return apiReturn(0,'ok',$res);

    }
}

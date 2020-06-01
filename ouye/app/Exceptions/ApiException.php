<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;

class ApiException extends Exception
{
    public function render(Request $request) {
        return apiReturn($this->code, $this->message);
    }
}

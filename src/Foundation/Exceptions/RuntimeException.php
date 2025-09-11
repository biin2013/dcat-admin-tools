<?php

namespace Biin2013\DcatAdminTools\Foundation\Exceptions;

use Illuminate\Http\Exceptions\HttpResponseException;

class RuntimeException extends HttpResponseException
{
    public function __construct($message = '', $code = 500)
    {
        parent::__construct(response()->json([
            'message' => $message
        ], $code));
    }
}
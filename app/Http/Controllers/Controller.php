<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function error404() {
        return response()->json([
            'message' => 'Sorry, the data you were trying to look for was not found'
        ], 404);
    }

    public function error400($validator) {
        return response()->json([
            'message' => 'Sorry, the data you were trying to submit was invalid',
            'error' => $validator->errors()
        ], 400);
    }
}

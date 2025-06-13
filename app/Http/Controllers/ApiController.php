<?php

namespace App\Http\Controllers;

use App\Http\Traits\RespondsWithJson;
use App\Http\Traits\RespondsWithJsonError;
use Illuminate\Http\JsonResponse;

abstract class ApiController extends Controller
{
    use RespondsWithJson;
    use RespondsWithJsonError;

}

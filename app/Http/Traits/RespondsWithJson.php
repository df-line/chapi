<?php

namespace App\Http\Traits;

trait RespondsWithJson
{
    protected function jsonResponse($payload, int $status = 200)
    {
        if (is_string($payload))
            $payload = ['message' => $payload];

        return response()->json($payload, $status);
    }
}

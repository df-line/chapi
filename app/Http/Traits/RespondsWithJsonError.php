<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;

trait RespondsWithJsonError
{
    public function jsonErrorResponse(string $error, int $code, array $data = []): JsonResponse
    {
        $out = ['error' => $error];

        if (!empty($data))
            $out = array_merge($out, $data);

        return response()->json($out, $code);
    }
}

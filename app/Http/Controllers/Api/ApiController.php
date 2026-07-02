<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use OpenApi\Attributes as OA;

#[OA\Info(version: '1.0.0', title: 'IBC API', description: 'REST API modul UMKM & customer untuk aplikasi mobile')]
#[OA\Server(url: '/', description: 'Server lokal')]
#[OA\SecurityScheme(securityScheme: 'bearerAuth', type: 'http', scheme: 'bearer')]
abstract class ApiController extends Controller
{
    /** Respons JSON konsisten { data, message }. */
    protected function respond(mixed $data = null, string $message = 'OK', int $status = 200)
    {
        return response()->json(['data' => $data, 'message' => $message], $status);
    }
}

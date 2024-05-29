<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[
    OA\Info(version: "1.0.0", description: "API documentation for SaleSync", title: "SaleSync API",),
    OA\SecurityScheme(securityScheme: "bearerAuth", type: "http", name: "Authorization", in: "header", scheme: "bearer"),
]
abstract class Controller
{
    //
}

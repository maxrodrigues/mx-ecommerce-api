<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class RegisterController extends Controller
{
    #[OA\Post(
        path: 'api/register',
        summary: 'Register',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    required: ['name', 'email', 'password', 'password_confirmation'],
                    properties: [
                        new OA\Property(property: 'name', description: 'User name', type: 'string', example: 'Test User'),
                        new OA\Property(property: 'email', description: 'User e-mail', type: 'string', example: 'example@example'),
                        new OA\Property(property: 'password', description: 'User password', type: 'string', example: 'password'),
                        new OA\Property(property: 'password_confirmation', description: 'User password confirmation', type: 'string', example: 'password'),
                    ]
                )
            )
        ),
        tags: ['Auth'],
        responses: [
            new OA\Response(response: Response::HTTP_CREATED, description: 'Created'),
            new OA\Response(response: Response::HTTP_UNPROCESSABLE_ENTITY, description: 'Unprocessable Entity'),
        ]
    )]
    public function __invoke(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|min:3|max:255',
                'email' => 'required|email|unique:users|max:255',
                'password' => 'required|confirmed|min:6|max:255',
                'password_confirmation' => 'required',
            ]);

            if ($validator->fails()) {
                return new JsonResponse([
                    'data' => [
                        'message' => 'The given data was invalid.',
                        'errors' => $validator->errors(),
                    ],
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $user = User::create($validator->validated());

            return new JsonResponse([
                'data' => [
                    'message' => 'User created successfully',
                    'token' => $user->createToken('sale_sync_api')->plainTextToken,
                ],
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}

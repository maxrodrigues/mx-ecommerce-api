<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class RegisterController extends Controller
{
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

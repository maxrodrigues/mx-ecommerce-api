<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|min:6',
            ]);

            if ($validator->fails()) {
                return new JsonResponse([
                    'data' => [
                        'message' => 'The given data was invalid.',
                        'errors' => $validator->errors(),
                    ],
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $data = $validator->validated();
            if (! Auth::attempt($data)) {
                return new JsonResponse([
                    'data' => [
                        'message' => 'These credentials do not match our records.',
                    ],
                ], Response::HTTP_UNAUTHORIZED);
            }

            $user = User::where('email', $request->email)->first();

            return new JsonResponse([
                'data' => [
                    'message' => 'Successfully logged in.',
                    'token' => $user->createToken($request->email)->plainTextToken,
                ],
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}

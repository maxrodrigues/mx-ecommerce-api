<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $data = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|min:6',
            ]);

            if ($data->fails()) {
                return new JsonResponse([
                    'data' => [
                        'message' => $data->errors(),
                    ],
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $admin = Admin::where('email', $request->email)->first();

            if (! $admin || ! Hash::check($request->password, $admin->password)) {
                return new JsonResponse([
                    'data' => [
                        'message' => 'The provided credentials are incorrect.',
                    ],
                ], Response::HTTP_UNAUTHORIZED);
            }

            return new JsonResponse([
                'data' => [
                    'admin' => $admin,
                    'token' => $admin->createToken('SaleSync Admin', ['role:admin'])->plainTextToken,
                ],
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return new JsonResponse([
                'data' => [
                    'message' => $e->getMessage(),
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserSubsId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'Email wajib diisi',
            'password.required' => 'Password wajib diisi',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validate->errors()->first()
            ], 400);
        }
        $credentials = $request->only('email', 'password');

        $user = User::where('email', $credentials['email'])->first();

        if (! $user) {
            return response()->json([
                'status' => false,
                'message' => 'Email tidak terdaftar'
            ], 401);
        } else {
            if ($user->status == 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email belum diverifikasi'
                ], 401);
            }
        }

        if (! $token = Auth::guard('api')->attempt($credentials)) {
            return response()->json([
                'status' => false,
                'message' => 'Email atau password salah'
            ], 401);
        }

        return response()->json([
            'status' => true,
            'message' => 'Login berhasil',
            'data' => array_merge($user->toArray(), [
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' =>  Auth::guard('api')->factory()->getTTL() * 60
            ])
        ]);
    }

    public function refresh()
    {
        try {
            $newToken = JWTAuth::parseToken()->refresh();

            return response()->json([
                'status' => true,
                'message' => 'Token refreshed',
                'data' => [
                    'token' => $newToken,
                    'token_type' => 'bearer',
                    'expires_in' => JWTAuth::factory()->getTTL() * 60
                ]
            ], 200);
        } catch (TokenExpiredException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Token expired and cannot be refreshed'
            ], 401);
        } catch (JWTException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Token invalid'
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Token refresh failed: ' . $e->getMessage()
            ], 401);
        }
    }

    public function updateSubsId(Request $request)
    {
        try {
            UserSubsId::updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'subs_id' => $request->subs_id,
                    'device_type' => $request->device_type
                ],
                [
                    'last_active_at' => now()
                ]
            );
            return response()->json([
                'status' => true,
                'message' => 'Success',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed : ' . $e->getMessage(),
            ], 400);
        }
    }
}

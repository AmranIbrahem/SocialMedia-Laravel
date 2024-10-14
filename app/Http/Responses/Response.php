<?php

namespace App\Http\Responses;

use App\Models\series;
use Illuminate\Http\JsonResponse;

class Response
{
    public static function AuthSuccess($message, $data, $token,$stateCode): JsonResponse
    {
        return response()->json([
            "message" => $message,
            'data' => $data,
            'token' => $token
        ], $stateCode);
    }
    public static function logout($succes,$message,$stateCode): JsonResponse
    {
        return response()->json([
            'success' => $succes,
            'message' => $message
        ], $stateCode);
    }
    public static function PasswordSuccess($user,$stateCode): JsonResponse
    {
        return response()->json([
            'message' => 'The confirmation code has been sent to your email',
            'user_id' => $user,
        ], $stateCode);
    }

    public static function Message($message,$stateCode): JsonResponse
    {
        return response()->json([
            'message' => $message,
        ], $stateCode);
    }

    public static function Photo($message,$data,$stateCode): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
        ], $stateCode);
    }




}

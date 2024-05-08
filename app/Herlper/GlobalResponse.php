<?php

namespace App\Helper;

use Illuminate\Http\JsonResponse;

class GlobalResponse
{
    public $data;
    public $message;

    public $status_code;

    public function __construct($data, $message, $status_code)
    {
        $this->data = $data;
        $this->message = $message;
        $this->status_code = $status_code;
    }
    public static function success($data, $message, $status_code) : JsonResponse
    {
        $response = new static($data, $message, $status_code);

        if (!is_array($data)) {
            $data = [];
        }

        return response()->json([
            "success" => true,
            "data" => $response->data,
            "message" => $response->message,
            "status" => $response->status_code
        ], $status_code);
    }

    public static function error($data, $message, $status_code) : JsonResponse
    {
        $response = new static($data, $message, $status_code);

        if (!is_array($data)) {
            $data = [];
        }

        return response()->json([
            "success" => false,
            "error" => $response->message,
            "errors" => $response->data,
            "status" => $response->status_code
        ], $status_code);
    }
}

<?php

include_once 'function/main.php';
include_once 'app/config/static.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");

abstract class PostSomething {
    abstract protected static function postData();
    
    protected static function sendResponse($no = []) {
        $error = $no['error'];
        $message = $no['message'];
        $response = [
            "error" => $error,
            "message" => $message,
        ];
        if (isset($no['data'])) {
            $response['data'] = $no['data'];
        } else {
            $response['data'] = [];
        }
        echo json_encode($response);
    }
}
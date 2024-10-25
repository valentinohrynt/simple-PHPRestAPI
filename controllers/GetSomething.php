<?php

include_once 'function/main.php';
include_once 'app/config/static.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json; charset=UTF-8");

abstract class GetSomething {
    abstract protected function getListData();
    
    protected function sendResponse($data, $message = "Data fetched successfully", $error = false) {
        $response = [
            "error" => $error,
            "message" => $message,
            "listData" => $data
        ];
        echo json_encode($response);
    }
}
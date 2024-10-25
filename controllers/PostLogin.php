<?php

include_once "./models/users.php";

class PostLogin extends PostSomething {
    static function postData(){
        $username = $_GET['username'];
        $password = $_GET['password'];
        $credentials = [
            "username" => $username,
            "password" => $password
        ];

        if ( $username == '' || $password == '' ) {
            return self::sendResponse([
                "error" => true,
                "message" => "Unauthorized"
            ]);
        } else {
            $user = Users::login($credentials);
            if ( $user ) {
                return self::sendResponse([
                    "error" => false,
                    "message" => "success",
                    "data" => $user
                ]);
            } else {
                return self::sendResponse([
                    "error" => true,
                    "message" => "Unauthorized"
                ]);
            }
        }
    }
}
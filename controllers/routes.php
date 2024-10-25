<?php

Router::url('postlogin', 'GET', 'PostLogin::postData');

// Router::url('getlogin', 'GET', 'GetLogin::getData');

Router::url('/', 'get', function () {
    echo 'Hello World';
});
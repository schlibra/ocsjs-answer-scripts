<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
echo json_encode([
    "code"=> 1,
    "msg" => "请求成功"
]);
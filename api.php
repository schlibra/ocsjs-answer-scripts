<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
$action = @$_REQUEST["action"] ?? "";
$token = @$_REQUEST["token"] ?? "";
if($token != file_get_contents("token")){
    die(json_encode(["code"=>0,"msg"=>"你没有权限访问该页面"]));
}
switch ($action){
    case "import":
        importData();
        break;
    case "update":
        updateData();
        break;
    case "delete":
        deleteData();
        break;
    case "get":
        getData();
        break;
}
function importData(){

}

function updateData(){

}

function deleteData(){

}
function getData(){
    readfile("data.json");
}
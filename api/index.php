<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
$action = @$_REQUEST["action"] ?? "";
$token = @$_REQUEST["token"] ?? "";
if($token != file_get_contents("../token")){
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
    case "init":
        initData();
        break;
    default:
        echo json_encode(["code"=>0,"msg"=>"没有指定操作"]);
}
function importData(){
    $type = @$_GET["type"] ?? "";
    if ($type === "single"){
        $title = @$_POST["title"] ?? "";
        $answer = @$_POST["answer"] ?? "";
        if (empty($title) or empty($answer)){
            echo json_encode(["code"=>0,"msg"=>"字段不能为空"]);
        }else{
            if (findRepeat(["title"=>$title,"answer"=>$answer])){
                echo json_encode(["code"=>0,"msg"=>"题目已存在，跳过导入"]);
            }else{
                $data = json_decode(file_get_contents("../data.json"),true);
                $data[] = ["title"=>$title,"answer"=>$answer];
                file_put_contents("../data.json",json_encode($data));
                echo json_encode(["code"=>1,"msg"=>"导入成功"]);
            }
        }
    }elseif ($type === "multi") {
        $data = @$_POST["data"];
        if (empty($data)){
            echo json_encode(["code"=>0,"msg"=>"字段不能为空"]);
        }else{
            $import_data = json_decode($data,true);
            $data = json_decode(file_get_contents("../data.json"),true);
            $count = 0;
            for ($i=0;$i<count($import_data);++$i){
                $item = $import_data[$i];
                if (!findRepeat($item)){
                    if (!empty($item["title"] and !empty(["answer"]))){
                        $count++;
                        $data[] = $item;
                    }
                }
            }
            file_put_contents("../data.json",json_encode($data));
            echo json_encode(["code"=>1,"msg"=>"导入完成，传入".count($import_data)."道题，有效导入{$count}道题"]);
        }
    }else{
        echo json_encode(["code"=>0,"msg"=>"上传类型错误"]);
    }
}

function updateData(){
    $id = @$_POST["id"] ?? "";
    $title = @$_POST["title"] ?? "";
    $answer = @$_POST["answer"] ?? "";
    if ($id === "" || empty($title) || empty($answer)){
        die(json_encode(["code"=>0,"msg"=>"字段不能为空"]));
    }
    $data = json_decode(file_get_contents("../data.json"),true);
    $data[$id] = ["title"=>$title, "answer"=>$answer];
    file_put_contents("../data.json", json_encode($data));
    echo json_encode(["code"=>1,"msg"=>"数据更新成功"]);
}

function deleteData(){
    $id = @$_POST["id"] ?? "";
    $title = @$_POST["title"] ?? "";
    $answer = @$_POST["answer"] ?? "";
    if ($id === "" || empty($title) || empty($answer)){
        die(json_encode(["code"=>0,"msg"=>"字段不能为空"]));
    }
    $data = json_decode(file_get_contents("../data.json"),true);
    $count = count($data);
    if ($data[$id]["title"] === $title and $data[$id]["answer"] === $answer){
        $new_data = [];
        for ($i=0;$i<$count;++$i){
            if ($i!=$id){
                $new_data[] = $data[$i];
            }
        }
        if (count($new_data) === $count){
            echo json_encode(["code"=>0,"msg"=>"数据更新失败，请重试"]);
        }else{
            file_put_contents("../data.json",json_encode($new_data));
            echo json_encode(["code"=>1,"msg"=>"数据删除成功"]);
        }
    }else{
        echo json_encode(["code"=>0,"msg"=>"数据不同步，请重试"]);
    }
}
function getData(){
    echo json_encode(["code"=>1,"data"=>json_decode(file_get_contents("../data.json"),true)]);
}
function initData(){
    file_put_contents("../data.json",json_encode([]));
    echo json_encode(["code"=>1,"msg"=>"数据初始化成功"]);
}
function findRepeat($item): bool
{
    $data = json_decode(file_get_contents("../data.json"),true);
    for($i=0;$i<count($data);++$i){
        if ($data[$i]["title"]==$item["title"]){
            return true;
        }
    }
    return false;
}
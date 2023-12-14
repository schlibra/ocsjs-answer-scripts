<?php
date_default_timezone_set("PRC");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
$action = @$_REQUEST["action"] ?? "";
$token = @$_REQUEST["token"] ?? "";
$db = new SQLite3("../data.db");
if($token != file_get_contents("../token")){
    header("Content-Type: application/json");
    die(json_encode(["code"=>0,"msg"=>"你没有权限访问该页面"]));
}
switch ($action){
    case "import":
        header("Content-Type: application/json");
        importData();
        break;
    case "update":
        header("Content-Type: application/json");
        updateData();
        break;
    case "delete":
        header("Content-Type: application/json");
        deleteData();
        break;
    case "get":
        header("Content-Type: application/json");
        getData();
        break;
    case "init":
        header("Content-Type: application/json");
        initData();
        break;
    case "json":
        downloadJson();
        break;
    case "excel":
        downloadExcel();
        break;
    case "sqlite":
//        header("Content-Type: application/json");
        sqliteTest();
        break;
    default:
        header("Content-Type: application/json");
        echo json_encode(["code"=>0,"msg"=>"没有指定操作"]);
        break;
}
function sqliteTest(){
    $conn = new SQLite3("data.db");
    $sql = <<<EOF
select * from main.data
EOF;
    $result = $conn->query($sql);
    var_dump($result->numColumns());
    while ($row = $result->fetchArray(SQLITE3_ASSOC)){
        var_dump($row);
    }
//    var_dump($result->fetchArray(SQLITE3_ASSOC));

}
function importData(){
    global $db;
    $type = @$_GET["type"] ?? "";
    if ($type === "single"){
        $title = @$_POST["title"] ?? "";
        $answer = @$_POST["answer"] ?? "";
        $work = @$_POST["work"] ?? "";
        $course = @$_POST["course"] ?? "";
        if (empty($title) or empty($answer)){
            echo json_encode(["code"=>0,"msg"=>"字段不能为空"]);
        }else{
            if (findRepeat(["title"=>$title,"answer"=>$answer])){
                echo json_encode(["code"=>0,"msg"=>"题目已存在，跳过导入"]);
            }else{
//                $data = json_decode(file_get_contents("../data.json"),true);
//                $data[] = ["title"=>$title,"answer"=>$answer, "work"=>$work, "course"=>$course, "create"=>date("Y-m-d H:i:s"), "update"=>date("Y-m-d H:i:s")];
//                file_put_contents("../data.json",json_encode($data));
                $create = date("Y-m-d H:i:s");
                $update = date("Y-m-d H:i:s");
                $result = $db->query("INSERT INTO 'data' ('title','answer', 'work', 'course', 'create', 'update') VALUES ('$title', '$answer', '$work', '$course', '$create', '$update')");
                if ($result) {
                    echo json_encode(["code" => 1, "msg" => "导入成功"]);
                }else{
                    echo json_encode(["code" => 0 ,"msg" => "数据插入失败"]);
                }
            }
        }
    }elseif ($type === "multi") {
        $data = @$_POST["data"];
        if (empty($data)){
            echo json_encode(["code"=>0,"msg"=>"字段不能为空"]);
        }else{
            $import_data = json_decode($data,true);
            $count = 0;
            /*$data = json_decode(file_get_contents("../data.json"),true);
            $count = 0;
            for ($i=0;$i<count($import_data);++$i){
                $item = $import_data[$i];
                $item["create"] = date("Y-m-d H:i:s");
                $item["update"] = date("Y-m-d H:i:s");
                if (!findRepeat($item)){
                    if (!empty($item["title"] and !empty(["answer"]))){
                        $count++;
                        $data[] = $item;
                    }
                }
            }
            file_put_contents("../data.json",json_encode($data));*/
            $sql = "INSERT INTO data ('title', 'answer', 'work', 'course', 'create', 'update') VALUES";
            $list = [];
            for($i=0;$i<count($import_data);++$i){
                $item = $import_data[$i];
                $item["create"] = date("Y-m-d H:i:s");
                $item["update"] = date("Y-m-d H:i:s");
                if (!findRepeat($item)){
                    if (!empty($item["title"] and !empty(["answer"]))){
                        $count++;
                        $list[] = "('{$item["title"]}', '{$item["answer"]}', '{$item["work"]}', '{$item["course"]}', '{$item["create"]}', '{$item["update"]}')";
                    }
                }
            }
            if (count($list)) {
                $sql .= @join($list, ",");
                $result = $db->exec($sql);
            }
            if($result) {
                echo json_encode(["code" => 1, "msg" => "导入完成，传入" . count($import_data) . "道题，有效导入{$count}道题"]);
            }else{
                echo json_encode(["code"=>0,"msg"=>"数据库执行失败：".$db->lastErrorMsg()]);
            }
        }
    }else{
        echo json_encode(["code"=>0,"msg"=>"上传类型错误"]);
    }
}

function updateData(){
    global $db;
    $id = @$_POST["id"] ?? "";
    $title = @$_POST["title"] ?? "";
    $answer = @$_POST["answer"] ?? "";
    $course = @$_POST["course"] ?? "";
    $work = @$_POST["work"] ?? "";
    if ($id === "" || empty($title) || empty($answer)){
        die(json_encode(["code"=>0,"msg"=>"字段不能为空"]));
    }
//    $data = json_decode(file_get_contents("../data.json"),true);
//    $data[$id]["title"] = $title;
//    $data[$id]["answer"] = $answer;
//    $data[$id]["course"] = $course;
//    $data[$id]["work"] = $work;
//    $data[$id]["update"] = date("Y-m-d H:i:s");
//    file_put_contents("../data.json", json_encode($data));
    $update = date("Y-m-d H:i:s");
    $sql = "UPDATE data SET `title`='$title', `answer`='$answer', `course`='$course', `work`='$work', `update`='$update' WHERE `id`=$id";
    $result = $db->exec($sql);
    if($result) {
        echo json_encode(["code" => 1, "msg" => "数据更新成功"]);
    }else{
        echo json_encode(["code"=>0,"msg"=>"数据更新失败：".$db->lastErrorMsg()]);
    }
}

function deleteData(){
    global $db;
    $id = @$_POST["id"] ?? "";
    $title = @$_POST["title"] ?? "";
    $answer = @$_POST["answer"] ?? "";
    $work = @$_POST["work"];
    $course = @$_POST["course"];
    $create = @$_POST["create"];
    $update = @$_POST["update"];

    if ($id === "" || empty($title) || empty($answer)){
        die(json_encode(["code"=>0,"msg"=>"字段不能为空"]));
    }
    $sql = "DELETE FROM data WHERE `id`=$id AND `title`='$title' AND `answer`='$answer' AND `work`='$work' AND `course`='$course' AND `create`='$create' AND `update`='$update'";
//    var_dump($sql);
    $result = $db->exec($sql);
    echo json_encode(["code"=>1,"msg"=>"数据删除成功"]);

//    $data = json_decode(file_get_contents("../data.json"),true);
//    $count = count($data);
//    if ($data[$id]["title"] === $title and $data[$id]["answer"] === $answer){
//        $new_data = [];
//        for ($i=0;$i<$count;++$i){
//            if ($i!=$id){
//                $new_data[] = $data[$i];
//            }
//        }
//        if (count($new_data) === $count){
//            echo json_encode(["code"=>0,"msg"=>"数据更新失败，请重试"]);
//        }else{
//            file_put_contents("../data.json",json_encode($new_data));
//            echo json_encode(["code"=>1,"msg"=>"数据删除成功"]);
//        }
//    }else{
//        echo json_encode(["code"=>0,"msg"=>"数据不同步，请重试"]);
//    }
}
function getData(){
    global $db;
    $result = $db->query("select * from 'data'");
    $data = [];
    if ($result){
        while ($row = $result->fetchArray(SQLITE3_ASSOC)){
            $data[] = $row;
        }
        echo json_encode(["code"=>1, "data"=>$data]);
    }else{
        echo json_encode(["code"=>0, "msg"=>"数据读取失败"]);
    }
//    echo json_encode(["code"=>1,"data"=>json_decode(file_get_contents("../data.json"),true)]);
}
function initData(){
//    file_put_contents("../data.json",json_encode([]));
    global $db;
    $db->exec(@file_get_contents("../sql/delete.sql"));
    $db->exec(@file_get_contents("../sql/create.sql"));
    echo json_encode(["code"=>1,"msg"=>"数据初始化成功"]);
}
function findRepeat($item): bool
{

    /*$data = json_decode(file_get_contents("../data.json"),true);
    for($i=0;$i<count($data);++$i){
        if ($data[$i]["title"]==$item["title"]){
            return true;
        }
    }
    return false;*/
    $db = new SQLite3("../data.db");
    $result = $db->query("select * from 'data' where title='{$item["title"]}'");
    return (bool)$result->fetchArray(SQLITE3_ASSOC);
}
function downloadJson(){
    $data = [];
    global $db;
    $result = $db->query("select * from data");
    if ($result) {
        $title = "data.json";
        while($row = $result->fetchArray(SQLITE3_ASSOC)){
            $data[] = $row;
//            print_r($row);
        }
//        die();
//        file_put_contents($title, json_encode($data));
//        $file = fopen($title, "rb");
        header("Content-Type: application/octet-stream");
        Header("Accept-Ranges:  bytes ");
        Header("Content-Disposition:  attachment;  filename= $title");
        $content = "";
//        while (!feof($file)) {
//            $content .= fread($file, 8192);
//        }
        echo json_encode($data);
//        fclose($file);
    }else{
        header("Content-Type: application/json");
        echo json_encode(["code"=>0,"msg"=>"数据查询失败：".$db->lastErrorMsg()]);
    }
}
function downloadExcel(){
    require "../vendor/autoload.php";
    $obj = @new PHPExcel();
    try {
        @$obj->setActiveSheetIndex(0);
        @$obj->getActiveSheet()
            ->setCellValue("A1", "#")
            ->setCellValue("B1", "标题")
            ->setCellValue("C1", "答案")
            ->setCellValue("D1", "作业名称")
            ->setCellValue("E1", "课程名称")
            ->setCellValue("F1", "创建时间")
            ->setCellValue("G1", "更新时间");
        @$obj->getActiveSheet()->getColumnDimension("B")->setWidth(60);
        @$obj->getActiveSheet()->getColumnDimension("C")->setWidth(40);
        @$obj->getActiveSheet()->getColumnDimension("D")->setWidth(30);
        @$obj->getActiveSheet()->getColumnDimension("E")->setWidth(30);
        @$obj->getActiveSheet()->getColumnDimension("F")->setWidth(20);
        @$obj->getActiveSheet()->getColumnDimension("G")->setWidth(20);
        $data = json_decode(file_get_contents("../data.json"),true);
        for ($i=0;$i<count($data);++$i){
            $item = $data[$i];
            $c = $i + 2;
            @$obj->getActiveSheet()
                ->setCellValue("A$c", $i+1)
                ->setCellValue("B$c", $item["title"])
                ->setCellValue("C$c", $item["answer"])
                ->setCellValue("D$c", @$item["work"] ?? "")
                ->setCellValue("E$c", @$item["course"] ?? "")
                ->setCellValue("F$c", @$item["create"] ?? "")
                ->setCellValue("G$c", @$item["update"] ?? "");
            @$obj->getActiveSheet()->getCell("A$c")->getStyle()->getAlignment()->setWrapText(true)->setVertical("top");
            @$obj->getActiveSheet()->getCell("B$c")->getStyle()->getAlignment()->setWrapText(true)->setVertical("top");
            @$obj->getActiveSheet()->getCell("C$c")->getStyle()->getAlignment()->setWrapText(true)->setVertical("top");
            @$obj->getActiveSheet()->getCell("D$c")->getStyle()->getAlignment()->setWrapText(true)->setVertical("top");
            @$obj->getActiveSheet()->getCell("E$c")->getStyle()->getAlignment()->setWrapText(true)->setVertical("top");
            @$obj->getActiveSheet()->getCell("F$c")->getStyle()->getAlignment()->setWrapText(true)->setVertical("top");
            @$obj->getActiveSheet()->getCell("G$c")->getStyle()->getAlignment()->setWrapText(true)->setVertical("top");
        }
        @$obj->getActiveSheet()->setAutoFilter("A1:G".(count($data)+1));
        @$writer = @PHPExcel_IOFactory::createWriter($obj, "Excel5");
        @$writer->save("data.xls");
        $title = "data.xls";
        $file = fopen($title, "rb");
        @header("Content-Type: application/octet-stream");
        @header("Accept-Ranges:  bytes ");
        @header("Content-Disposition:  attachment;  filename= $title");
        $content = "";
        while (!feof($file)){
            $content.=fread($file, 8192);
        }
        echo $content;
        fclose($file);
        unlink($title);
    } catch (PHPExcel_Exception $e) {
        echo $e->getMessage();
    }
}
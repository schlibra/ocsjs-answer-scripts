<?php
date_default_timezone_set("PRC");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
$action = @$_REQUEST["action"] ?? "";
$token = @$_REQUEST["token"] ?? "";
$db = new SQLite3("../data.db");
/*if($token != file_get_contents("../token")){
    header("Content-Type: application/json");
    die(json_encode(["code"=>0,"msg"=>"你没有权限访问该页面"]));
}*/
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
    case "login":
        login();
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
            $result = null;
            if (count($list)) {
                $sql .= @join($list, ",");
                $result = $db->exec($sql);
            }
            if($result || count($list)==0) {
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
    $db->exec(@file_get_contents("../sql/deleteData.sql"));
    $db->exec(@file_get_contents("../sql/createData.sql"));
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
    global $db;
    $result = $db->query("SELECT * FROM data");
    $count = 0;
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
            ->setCellValue("G1", "更新时间")
            ->setCellValue("H1", "创建用户");
        @$obj->getActiveSheet()->getColumnDimension("B")->setWidth(60);
        @$obj->getActiveSheet()->getColumnDimension("C")->setWidth(40);
        @$obj->getActiveSheet()->getColumnDimension("D")->setWidth(30);
        @$obj->getActiveSheet()->getColumnDimension("E")->setWidth(30);
        @$obj->getActiveSheet()->getColumnDimension("F")->setWidth(20);
        @$obj->getActiveSheet()->getColumnDimension("G")->setWidth(20);
        @$obj->getActiveSheet()->getColumnDimension("H")->setWidth(20);
        while ($item = $result->fetchArray(SQLITE3_ASSOC)){
            $c = $count+++2;
            @$obj->getActiveSheet()
                ->setCellValue("A$c", $item["id"])
                ->setCellValue("B$c", $item["title"])
                ->setCellValue("C$c", $item["answer"])
                ->setCellValue("D$c", @$item["work"] ?? "")
                ->setCellValue("E$c", @$item["course"] ?? "")
                ->setCellValue("F$c", @$item["create"] ?? "")
                ->setCellValue("G$c", @$item["update"] ?? "")
                ->setCellValue("H$c", @$item["user"] ?? "");
            @$obj->getActiveSheet()->getCell("A$c")->getStyle()->getAlignment()->setWrapText(true)->setVertical("top");
            @$obj->getActiveSheet()->getCell("B$c")->getStyle()->getAlignment()->setWrapText(true)->setVertical("top");
            @$obj->getActiveSheet()->getCell("C$c")->getStyle()->getAlignment()->setWrapText(true)->setVertical("top");
            @$obj->getActiveSheet()->getCell("D$c")->getStyle()->getAlignment()->setWrapText(true)->setVertical("top");
            @$obj->getActiveSheet()->getCell("E$c")->getStyle()->getAlignment()->setWrapText(true)->setVertical("top");
            @$obj->getActiveSheet()->getCell("F$c")->getStyle()->getAlignment()->setWrapText(true)->setVertical("top");
            @$obj->getActiveSheet()->getCell("G$c")->getStyle()->getAlignment()->setWrapText(true)->setVertical("top");
            @$obj->getActiveSheet()->getCell("H$c")->getStyle()->getAlignment()->setWrapText(true)->setVertical("top");
        }
        @$obj->getActiveSheet()->setAutoFilter("A1:H".($count+1));
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

function login(){
    header("Content-Type: application/json");
    global $db;
    $content = @$_POST["content"];
    if (empty($content) or strlen($content) % 2) {
        die(json_encode(["code"=>0, "msg"=>"传递内容有误"]));
    }
    $data = explode("\x00",base64_decode(urldecode("%".join("%",str_split($content, 2)))));
    $username = $data[0];
    $password = $data[1];
    $sql = "SELECT * FROM `user` where `username`='$username'";
    $result = $db->query($sql);
    if ($row = $result->fetchArray(SQLITE3_ASSOC)){
        if (string_decode($row["password"], $password) === $password){
            die(json_encode([]));
        }else{
            die(json_encode(["code"=>0,"msg"=>"密码错误"]));
        }
    }
    die(json_encode(["code"=>0,"msg"=>"用户不存在"]));
}




/* 字符串加密模块 */
/*-----依赖代码，请勿修改里面的内容，防止出现不可预知的问题-----*/class __a{public static function _a(string$_a,string$_b,bool$_c=false):string{$a=base64_encode($_a);$b=str_split($a);$c="";foreach($b as$m){$x_a=ord($m);$x_b=127-$x_a;$x_c=dechex($x_b);if(strlen($x_c)==1){$x_c="0".$x_c;}$c.=$x_c;}$d=substr(hash("sha256",$_b),0,strlen($_b));$c_a=str_split($c);$d_a=str_split($d);$e="";$index=0;foreach($c_a as$item){$v_a=$item;$v_b=$d_a[$index++%count($d_a)];$v_c=decbin(ord($v_a)+ord($v_b));if(strlen($v_c)==7){$v_c="0".$v_c;}$e.=$v_c;}$f=decbin(rand(0,127));switch(strlen($f)){case 0:$f="00000000";break;case 1:$f="0000000".$f;break;case 2:$f="000000".$f;break;case 3:$f="00000".$f;break;case 4:$f="0000".$f;break;case 5:$f="000".$f;break;case 6:$f="00".$f;break;case 7:$f="0".$f;break;}$g="";$h=str_split($e);$x=str_split($f);for($i=0;$i<count($h);$i++){$i_a=$h[$i];$i_b=$x[$i%8];$i_c=$i_a===$i_b?"0":"1";$h[$i]=$i_c;}$t="";for($i=0;$i<count($h);$i++){$t.=$h[$i];}$t.=$f;$h=str_split($t);for($i=0;$i<count($h);$i++){$a=chr(rand(97,122));$A=chr(rand(65,90));if($h[$i]==0){$g.=$a;}elseif($h[$i]==1){$g.=$A;}}if($_c){return$g;}else{echo$g;}return"";}public static function _b(string$_a,string$_b,bool$_c=false):string{$a=str_split($_a);$b="";for($i=0;$i<count($a);$i++){$i_a=ord($a[$i]);$_a=97;$_z=122;$_A=65;$_Z=90;if($i_a<=$_z&&$i_a>=$_a){$b.="0";}elseif($i_a<=$_Z&&$i_a>=$_A){$b.="1";}}$c=substr($b,0,strlen($b)-8);$d=substr($b,strlen($b)-8,8);$c_a=str_split($c);$c_b=str_split($d);$e="";for($i=0;$i<count($c_a);$i++){$i_a=$c_a[$i];$i_b=$c_b[$i%8];$i_c=$i_a===$i_b?"0":"1";$e.=$i_c;}$f=str_split($e,8);$g=str_split(substr(hash("sha256",$_b),0,strlen($_b)));$h="";for($i=0;$i<count($f);$i++){$x_a=bindec($f[$i]);$x_b=ord($g[$i%count($g)]);$x_c=chr($x_a-$x_b);$h.=$x_c;}$m=str_split($h,2);$n="";for($i=0;$i<count($m);$i++){$i_a=chr(127-hexdec($m[$i]));$n.=$i_a;}$o=base64_decode($n);if($_c){return$o;}else{echo$o;return"";}}}
/**
 * 加密字符串
 * @param string $string 需要加密的数据
 * @param string $secret 加密用的密钥
 * @param bool $return 是否返回，默认不返回直接输出
 * @return string 返回加密后的数据
 */
function string_encode(string $string,string $secret,bool $return=true):string{
    return __a::_a($string,$secret,$return);
}

/**
 * 解密字符串
 * @param string $string 需要解密的数据
 * @param string $secret 解密用的密钥
 * @param bool $return 是否返回，默认不返回直接输出
 * @return string 返回解密后的数据
 */
function string_decode(string $string,string $secret,bool $return=true):string{
    return __a::_b($string,$secret,$return);
}
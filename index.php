<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
$data = json_decode(file_get_contents("data.json"),true);
$ori_title = @$_GET["title"] ?? "";
$sp_emp = urldecode("%C2%A0");
$title = str_replace(["　", " ",$sp_emp],["","",""],$ori_title);
$answer = "";
foreach($data as $item){
        if($item["title"]==$title){
                $answer = $item["answer"];
        }
}
if(empty($answer)){
        die(json_encode(["code"=> 0, "question"=>$ori_title, "answer"=>"没有找到题目"]));
}
die(json_encode(["code"=>1,"question"=>$title,"answer"=>$answer]));

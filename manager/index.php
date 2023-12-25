<?php
// require "../vendor/autoload.php";
if (@$_GET["token"]!=@file_get_contents("../token") or @file_get_contents("../token")==""){
    http_response_code(403);
    header("Content-Type: application/json");
    die(json_encode(["code"=>0,"msg"=>"拒绝访问"]));
}
?>
<html lang="zh" data-bs-theme="dark">
<head>
    <title>题库数据管理页面</title>
    <meta charset="utf-8">
    <link href="https://cdn.tsinbei.com/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
</head>
<body style="padding: 8px;">
<?php
$data = [];
$result = (new SQLite3("../data.db"))->query("select * from data");
while($row= $result->fetchArray(SQLITE3_ASSOC)){
    $data[] = $row;
}
//$data = json_decode(file_get_contents("../data.json"),true);
?>
<h1>题库数据管理</h1>
<h3>当前共<?php echo count($data); ?>条数据</h3>
<button class="btn btn-primary" onclick="addData()">添加数据</button>
<button class="btn btn-danger" onclick="initData()">初始化数据</button>
<!--<button class="btn btn-info">整理数据</button>-->
<button class="btn btn-primary" onclick="exportData()">导出数据</button>
<button class="btn btn-primary" onclick="location.reload()">刷新页面</button>
<table class="table table-striped table-hover">
    <thead>
    <tr>
        <th class="col"></th>
        <th class="col">#</th>
        <th class="col">问题</th>
        <th class="col">回答</th>
        <th class="col">作业名称</th>
        <th class="col">课程名称</th>
        <th class="col">创建时间</th>
        <th class="col">更新时间</th>
        <th class="col">操作</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for($i=0;$i<count($data);++$i){
        $item = $data[$i];
//        $item["id"] = $i;
    ?>
    <tr>
        <td scope="row"></td>
        <td><?php echo $item["id"] ?></td>
        <td><?php echo $item["title"] ?></td>
        <td><?php echo $item["answer"] ?></td>
        <td><?php echo @$item["work"] ?></td>
        <td><?php echo @$item["course"] ?></td>
        <td><?php echo @$item["create"] ?></td>
        <td><?php echo @$item["update"] ?></td>
        <td>
            <button class="btn btn-primary" onclick='editData(`<?php echo json_encode($item); ?>`)'>编辑</button>
            <button class="btn btn-danger" onclick='deleteData(`<?php echo json_encode($item); ?>`)'>删除</button>
        </td>
    </tr>
    <?php
    }
    ?>
    </tbody>
</table>
<script src="https://cdn.tsinbei.com/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-/mhDoLbDldZc3qpsJHpLogda//BVZbgYuw6kof4u2FrCedxOtgRZDTHgHUhOCVim" crossorigin="anonymous"></script>
<script src="https://cdn.tsinbei.com/npm/sweetalert"></script>
<!--<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>-->
<script src="https://cdn.tsinbei.com/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script>
    function editData(data){
        data = JSON.parse(data);
        console.log(data);
        let content = document.createElement("div");
        content.style.textAlign="left";
        content.innerHTML = `
            <div class="mb-3" style="text-align: left;">
                <label for="data_id" class="form-label">ID：</label>
                <input type="text" class="form-control" id="data_id" placeholder="ID" value="${data.id}" disabled>
            </div>
            <div class="mb-3" style="text-align: left;">
                <label for="data_title" class="form-label">标题：</label>
                <textarea class="form-control" id="data_title" cols="30" rows="3" placeholder="标题">${data.title}</textarea>
            </div>
            <div class="mb-3" style="text-align: left;">
                <label for="data_answer" class="form-label">回答：</label>
                <textarea class="form-control" id="data_answer" cols="30" rows="3" placeholder="回答">${data.answer}</textarea>
            </div>
            <div class="mb-3" style="text-align: left;">
                <label for="data_course" class="form-label">课程名称：</label>
                <textarea class="form-control" id="data_course" cols="30" rows="3" placeholder="课程名称">${data.course}</textarea>
            </div>
            <div class="mb-3" style="text-align: left;">
                <label for="data_work" class="form-label">作业名称：</label>
                <textarea class="form-control" id="data_work" cols="30" rows="3" placeholder="作业名称">${data.work}</textarea>
            </div>
        `;
        swal({
            title: "编辑数据",
            text: "在下面编辑这条数据",
            content,
            closeOnEsc: false,
            closeOnClickOutside: false,
            buttons: {
                confirm: {
                    text: "确定",
                    value: "confirm"
                },
                cancel: {
                    text: "取消",
                    value: "cancel",
                    visible: true
                }
            }
        }).then(value=>{
            if (value==="confirm"){
                data.title = $("#data_title").val();
                data.answer = $("#data_answer").val();
                data.course - $("#data_course").val();
                data.work = $("#data_work").val();
                $.post("../api/?action=update&token=<?php @readfile("../token"); ?>",data,res=>{
                    if (res.code){
                        swal({
                            title: "操作成功",
                            text: res.msg,
                            icon: "success",
                            closeOnEsc: false,
                            closeOnClickOutside: false,
                            buttons: {
                                confirm: {
                                    text: "确定",
                                    value: "confirm"
                                }
                            }
                        }).then(_=>{
                            location.reload();
                        })
                    }else {
                        swal({
                            title:"操作失败",
                            text: res.msg ?? "服务器没有正常返回",
                            icon: "error",
                            closeOnEsc: false,
                            closeOnClickOutside: false,
                            buttons: ["确定", "取消"]
                        })
                    }
                })
            }
        })
    }
    function deleteData(data){
        data = JSON.parse(data);
        swal({
            title: "删除数据",
            text: `您是否确定要删除这条数据？数据ID为${data.id}`,
            closeOnEsc: false,
            closeOnClickOutside: false,
            buttons: {
                confirm: {
                    text: "确定",
                    value: "confirm"
                },
                cancel: {
                    text: "取消",
                    value: "cancel",
                    visible: true
                }
            }
        }).then(value=>{
            if (value==="confirm"){
                $.post("../api/?action=delete&token=<?php @readfile("../token"); ?>",data,res=>{
                    if (res.code){
                        swal({
                            title: "操作成功",
                            text: res.msg,
                            icon: "success",
                            closeOnEsc: false,
                            closeOnClickOutside: false,
                            buttons: {
                                confirm: {
                                    text: "确定",
                                    value: "confirm"
                                }
                            }
                        }).then(value=>{
                            if(value==="confirm"){
                                location.reload()
                            }
                        })
                    }else {
                        swal({
                            title: "操作失败",
                            text: res.msg ?? "服务器没有正常返回",
                            icon: "error",
                            closeOnEsc: false,
                            closeOnClickOutside: false,
                            buttons: ["确定","取消"]
                        })
                    }
                });
            }
        })
    }
    function initData(){
        swal({
            title:"初始化数据",
            text: "初始化数据后将会清除题库中的所有数据，并且这个操作不可逆，当前题库中共有<?php echo count($data); ?>条数据",
            closeOnEsc: false,
            closeOnClickOutside: false,
            buttons: {
                confirm: {
                    text: "确定",
                    value: "confirm"
                },
                cancel: {
                    text: "取消",
                    value: "cancel",
                    visible: true
                }
            }
        }).then(value => {
            if(value==="confirm"){
                $.post("../api/?action=init&token=<?php @readfile("../token"); ?>",[],res=>{
                    if (res.code){
                        swal({
                            title:"操作成功",
                            text: res.msg,
                            icon: "success",
                            closeOnEsc: false,
                            closeOnClickOutside: false,
                            buttons: {
                                confirm: {
                                    text: "确定",
                                    value: "confirm"
                                }
                            }
                        }).then(value=>{
                            location.reload();
                        })
                    }else {
                        swal({
                            title:"操作失败",
                            text: res.msg  ?? "服务器没有正常返回",
                            icon: "error",
                            closeOnEsc: false,
                            closeOnClickOutside: false,
                            buttons: ["确定", "取消"]
                        })
                    }
                })

            }
        })
    }
    function addData(){
        let content = document.createElement("div");
        content.style.textAlign = "left";
        content.innerHTML = `
            <div class="form-check">
                <input type="radio" class="form-check-input" id="import_type_single" name="import-type" checked>
                <label for="import_type_single">单题导入</label>
            </div>
            <div class="form-check">
                <input type="radio" class="form-check-input" id="import_type_multi" name="import-type">
                <label for="import_type_multi">批量导入</label>
            </div>
            <div class="mb-3 import_single_control">
                <label for="import_single_title">题目</label>
                <textarea class="form-control" id="import_single_title" cols="30" rows="5" placeholder="题目（需要删除无用字符）"></textarea>
            </div>
            <div class="mb-3 import_single_control">
                <label for="import_single_answer">答案</label>
                <textarea class="form-control" id="import_single_answer" cols="30" rows="5" placeholder="答案（多选题答案使用#分隔）"></textarea>
            </div>
            <div class="mb-3 import_single_control">
                <label for="import_single_course">课程名称</label>
                <textarea class="form-control" id="import_single_course" cols="30" rows="2" placeholder="课程名称"></textarea>
            </div>
            <div class="mb-3 import_single_control">
                <label for="import_single_work">作业名称</label>
                <textarea class="form-control" id="import_single_work" cols="30" rows="2" placeholder="课程名称"></textarea>
            </div>
            <div class="mb-3 import_multi_control" style="display: none">
                <label for="import_multi_data">题库数据</label>
                <textarea class="form-control" id="import_multi_data" cols="30" rows="20" placeholder="题库数据（JSON格式）"></textarea>
            </div>
        `;
        let opt1 = content.children[0];
        let opt2 = content.children[1];
        opt1.onclick=_=>{
            $(".import_single_control").show();
            $(".import_multi_control").hide();
        }
        opt2.onclick=_=>{
            $(".import_single_control").hide();
            $(".import_multi_control").show();
        }
        swal({
            title: "添加数据",
            text: "添加数据到题库",
            content,
            closeOnEsc: false,
            closeOnClickOutside: false,
            buttons: {
                confirm: {
                    text: "确定",
                    value: "confirm"
                },
                cancel: {
                    text: "取消",
                    value: "cancel",
                    visible: true
                }
            }
        }).then(value => {
            if (value==="confirm"){
                let type = "";
                if ($("#import_type_single")[0].checked){
                    type = "single"
                }
                if ($("#import_type_multi")[0].checked){
                    type = "multi"
                }
                if (type.length){
                    if (type === "single"){
                        let title = $("#import_single_title").val();
                        let answer = $("#import_single_answer").val();
                        let course = $("#import_single_course").val();
                        let work = $("#import_single_work").val();
                        if (title && answer) {
                            $.post("../api/?action=import&type=single&token=<?php @readfile("../token"); ?>", {title,answer,course,work}, res => {
                                if (res.code){
                                    swal({
                                        title: "操作成功",
                                        text: res.msg,
                                        icon: "success",
                                        closeOnEsc: false,
                                        closeOnClickOutside: false,
                                        buttons: {
                                            confirm: {
                                                text: "确定",
                                                value: "confirm"
                                            }
                                        }
                                    }).then(value=>{
                                        if (value==="confirm"){
                                            location.reload();
                                        }
                                    })
                                }else {
                                    swal({
                                        title:"操作失败",
                                        text: res.msg  ?? "服务器没有正常返回",
                                        icon: "error",
                                        closeOnEsc: false,
                                        closeOnClickOutside: false,
                                        buttons: ["确定", "取消"]
                                    })
                                }
                            })
                        }else {
                            swal({
                                title: "操作失败",
                                text: "字段不能为空",
                                icon: "error",
                                closeOnEsc: false,
                                closeOnClickOutside: false,
                                buttons: ["确定", "取消"]
                            })
                        }
                    }
                    if (type === "multi"){
                        let data = $("#import_multi_data").val();
                        if (data) {
                            $.post("../api/?action=import&type=multi&token=<?php @readfile("../token"); ?>", {data}, res => {
                                if (res.code){
                                    swal({
                                        title: "操作成功",
                                        text: res.msg,
                                        icon: "success",
                                        closeOnEsc: false,
                                        closeOnClickOutside: false,
                                        buttons: {
                                            confirm: {
                                                text: "确定",
                                                value: "confirm"
                                            }
                                        }
                                    }).then(value=>{
                                        if (value==="confirm"){
                                            location.reload();
                                        }
                                    })
                                }else {
                                    swal({
                                        title:"操作失败",
                                        text: res.msg  ?? "服务器没有正常返回",
                                        icon: "error",
                                        closeOnEsc: false,
                                        closeOnClickOutside: false,
                                        buttons: ["确定", "取消"]
                                    })
                                }
                            })
                        }else {
                            swal({
                                title: "操作失败",
                                text: "字段不能为空",
                                icon: "error",
                                closeOnEsc: false,
                                closeOnClickOutside: false,
                                buttons: ["确定", "取消"]
                            })
                        }
                    }
                }else {
                    swal({
                        title: "操作失败",
                        text: "上传类型错误",
                        icon: "error",
                        closeOnEsc: false,
                        closeOnClickOutside: false,
                        buttons: ["确定", "取消"]
                    })
                }
            }
        })
    }
    function exportData(){
        swal({
            title: "导出数据",
            text: "导出全部数据，选择一种格式",
            closeOnEsc: false,
            closeOnClickOutside: false,
            buttons: {
                json: {
                    text: "JSON格式",
                    value: "json"
                },
                excel: {
                    text: "Excel格式",
                    value: "excel"
                },
                cancel: {
                    text: "取消",
                    value: "cancel",
                    visible: true
                }
            }
        }).then(value=>{
            switch (value){
                case "json":
                    exportJson();
                    break;
                case "excel":
                    window.open("../api/?token=<?php @readfile("../token") ?>&action=excel","_blank");
                    break;
            }
        })
    }
    function exportJson(){
        $.get("../api/?token=<?php @readfile("../token") ?>&action=get",res=>{
            if (res.code){
                let content = document.createElement("div");
                content.style.textAlign="left"
                content.innerHTML = `
            <textarea rows="10" cols="50" id="export_json_textarea" readonly>${JSON.stringify(res.data)}</textarea>
        `;
                swal({
                    title: "导出JSON",
                    text: "这是你需要的JSON数据，你可以选择复制或下载",
                    closeOnEsc: false,
                    closeOnClickOutside: false,
                    content,
                    buttons: {
                        copy: {
                            text: "复制",
                            value: "copy",
                            closeModal: false
                        },
                        download: {
                            text: "下载",
                            value: "download"
                        },
                        cancel: {
                            text: "取消",
                            value: "cancel",
                            visible: true
                        }
                    }
                }).then(value=>{
                    switch (value){
                        case "copy":
                            $("#export_json_textarea")[0].select();
                            document.execCommand("copy");
                            swal.stopLoading();
                            break;
                        case "download":
                            window.open("../api/?token=<?php @readfile("../token"); ?>&action=json","_blank");
                            break;
                    }
                })
            }else {
                swal({
                    title: "获取失败",
                    text: "无法从服务器获取数据",
                    closeOnEsc: false,
                    closeOnClickOutside: false,
                    buttons: ["确定", "取消"]
                })
            }
        })


    }
</script>
</body>
</html>
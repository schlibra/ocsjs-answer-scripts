<html lang="zh">
<head>
    <title>题库数据管理页面</title>
    <meta charset="utf-8">
    <link href="https://cdn.tsinbei.com/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
</head>
<body style="padding: 8px;">
<h1>题库数据管理</h1>
<button class="btn btn-primary">添加数据</button>
<button class="btn btn-danger">初始化数据</button>
<button class="btn btn-info">整理数据</button>
<?php
$data = json_decode(file_get_contents("data.json"),true);
?>
<table class="table table-striped table-hover">
    <thead>
    <tr>
        <th class="col">#</th>
        <th class="col">问题</th>
        <th class="col">回答</th>
        <th class="col">操作</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for($i=0;$i<count($data);++$i){
        $item = $data[$i];
        $item["id"] = $i;
    ?>
    <tr>
        <td scope="row"><?php echo $i ?></td>
        <td><?php echo $item["title"] ?></td>
        <td><?php echo $item["answer"] ?></td>
        <td>
            <button class="btn btn-primary" onclick='editData(`<?php echo json_encode($item); ?>`)'>编辑</button>
            <button class="btn btn-danger">删除</button>
        </td>
    </tr>
    <?php
    }
    ?>
    </tbody>
</table>
<script src="https://cdn.tsinbei.com/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-/mhDoLbDldZc3qpsJHpLogda//BVZbgYuw6kof4u2FrCedxOtgRZDTHgHUhOCVim" crossorigin="anonymous"></script>
<script src="https://cdn.tsinbei.com/npm/sweetalert"></script>
<script src="https://cdn.tsinbei.com/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script>
    function editData(data){
        data = JSON.parse(data);
        console.log(data);
        let content = document.createElement("div");
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
        `;
        swal({
            title: "编辑数据",
            text: "在下面编辑这条数据",
            content,
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
                $.post("api.php?action=post&token=<?php @readfile("token"); ?>",data,res=>{
                    if (res.code){

                    }else {
                        swal({
                            title:"操作失败",
                            text: res.msg ?? "服务器没有正常返回",
                            icon: "danger"
                        })
                    }
                })
            }
        })
    }
    function deleteData(data){

    }
</script>
</body>
</html>

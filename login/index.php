<!DOCTYPE html>
<html lang="zh" data-bs-theme="dark">
<head>
    <title>题库数据管理 - 登录</title>
    <meta charset="UTF-8"/>
    <link href="https://cdn.tsinbei.com/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet"
          crossorigin="anonymous">

</head>
<body>
<div class="container">
    <h1 class="text-center mt-4">题库数据管理</h1>
    <h2 class="text-center mt-2">登录</h2>
    <div class="row">
        <div class="col"></div>
        <form class="col">
            <div class="mt-3">
                <label for="username" class="form-label">用户名：</label>
                <input type="text" id="username" class="form-control">
            </div>
            <div class="mt-3">
                <label for="password" class="form-label">密码：</label>
                <input type="password" id="password" class="form-control">
            </div>
            <div class="row mt-3">
                <div class="col"></div>
                <div class="col text-center">
                    <button onclick="login()" type="button" class="btn btn-lg btn-primary">登录</button>
                </div>
                <div class="col"></div>
            </div>
        </form>
        <div class="col"></div>
    </div>

</div>
<script src="https://cdn.tsinbei.com/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN"
        crossorigin="anonymous"></script>
<script src="https://cdn.tsinbei.com/npm/sweetalert"></script>
<!--<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>-->
<script src="https://cdn.tsinbei.com/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script type="text/javascript">
    function login() {
        let username = $("#username").val();
        let password = $("#password").val();
        if (username && password) {
            let content = "";
            new TextEncoder().encode(window.btoa(`${username}\x00${password}`)).forEach(_ => content += _.toString(16))
            // console.log(content);
            $.post("../api/?action=login", {content}, res=>{
                console.log(res)
                if(res.code){
                    swal({
                        title: "登录成功",
                        text: res.msg,
                        icon: "success",
                        buttons: {
                            confirm: {
                                text: "确定"
                            }
                        }
                    })
                }else {
                    swal({
                        title: "登录失败",
                        text: res.msg ?? "服务端没有正常返回",
                        icon: "error",
                        buttons: {
                            confirm: {
                                text: "确定"
                            }
                        }
                    })
                }
            });
        } else {
            swal({
                title: "登录失败",
                text: "用户名或密码不能为空",
                icon: "error",
                buttons: ["确定", "取消"]
            })
        }
    }
</script>
</body>
</html>
# OCSJS网课助手自动答题回答脚本
## 简介
使用该脚本轻松配置一个属于自己的题库，本人体验过网上现成的题库，如果需要自己导入题库会非常麻烦，可能还需要等待平台审核，时间会非常久，于是我打算实现一个自动回答脚本来解决这个问题。
## 配置脚本的要求
- 要求有服务器能运行网站
- 网站**必须**支持*SSL*
- PHP版本 >= 7.0
## 脚本部署教程
克隆本仓库：
```
git clone https://github.com/schlibra/ocsjs-answer-scripts.git
```
为这个仓库创建一个站点，创建完站点后为绑定的域名生成一个SSL证书，将SSL证书配置到站点中
## 添加题库教程
题库配置时用到的域名需要和脚本部署时绑定的域名一致，例如域名为`example.com`，题库配置如下：
```JSON
[
    {
        "url": "https://example.com/?title=${title}",
        "name": "题库名称",
        "method": "get",
        "contentType": "json",
        "handler": "return (res)=> res.code === 1 ? [res.question,res.answer] : undefined"
    }
]
```
注意url的协议必须为`https`，否则会无法请求
## 结尾
如果你有遇到问题，可以发送邮件向我提问，[schlibra@163.com](mailto:schlibra@163.com)

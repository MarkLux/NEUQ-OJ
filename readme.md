# NEUQ-OJ

### 开源在线判题系统 developed by 不洗碗工作室团队

访问地址: http://newoj.acmclub.cn

powered by PHP,GO,Redis,Mysql,React

![](http://of1deuret.bkt.clouddn.com/17-10-17/79411594.jpg)

### 代码说明

NEUQ-OJ 采用 WEB 前端 + WEB 后端 + 判题服务 分离的架构设计。

本仓库为WEB后端源码（业务层）,使用Laravel框架开发。

各代码库地址：

* 前端（JavaScript,使用React框架）: https://github.com/ouxu/NEUQ-OJ
* 后端（PHP,使用Laravel框架）: https://github.com/MarkLux/NEUQ-OJ
* 判题服务端（Golang,使用GIN框架）: https://github.com/MarkLux/JudgeServer
* 判题沙箱集成（Golang）： https://github.com/MarkLux/Judger_GO
* 判题沙箱（C，原作者为青岛大学，本人略有改动）:https://github.com/MarkLux/Judger

### 安装与使用

#### 环境配置与说明

从线上代码安装和部署完整的NEUQ-OJ需要以下的语言和环境：

* [必需]nginx或Apache2
* [必需]PHP(7.0+)
* [必需]MySQL(5.5+)
* [必需]Redis
* Golang(1.8+)

#### 安装流程

1. 部署WEB业务后端
   
   部署方法同Laravel框架，注意同时配置Redis和Mysql
 
2. 部署判题机

   NEUQ-OJ使用多判题机分布式判题的架构，配置一台判题服务机可以使用已经配置好的docker镜像（推荐，一秒部署）。
   也可以自行安装。
   流程参考 https://github.com/MarkLux/JudgeServer
   
3. 部署前端

   webpack打包后直接发布即可。

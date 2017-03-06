# Core

### 判题前需要的数据库数据
* source_code 表
* solution 表： result=0（ <2 ）
* problem 表：测试的输入和输出需要再检查
* user

### 数据库修改指南

* solutions 表 ：已经根据表结构修改核心
* problems 表 ：已经根据表结构修改核心
* users 表：已经根据表结构修改核心
* complie_infos :不要添加多余的字段，也不要修改它
* runtime_infos :同上
* custominput ：核心里用了，具体用途不明，建议保留
* sim 系列：表名等都还没有修改 原理不明 建议保留原样

### 核心运行说明

* 判题时的输入输出数据均来自``` /home/judge/data/题目号 ``` 文件夹 因此修改和添加题目都必须在这里进行改动。
* 重编译说明：需要修改judge_client.cc和judged.cc两个源代码文件。然后关闭正在运行的judged服务进程，删除```/usr/bin/```目录下的judged和judge_client然后用bash运行make.sh脚本即可。 其他的在安装时已经搞定（如启动服务的脚本等）

### Test
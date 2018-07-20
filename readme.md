# lumen-easyui

基于`lumen`框架和`wangdong/laravel-module-easyui`包组成

## docker环境

### 正式环境

> 环境启动

```
# 启动docker环境
docker-compose up -d
```

> 系统配置

```
# 创建.env文件
docker-compose exec php cp .env.example .env

# 安装composer包
docker-compose exec php composer install

# 复制文件
docker-compose exec php php artisan module:publish

# 处理上一步中新增php类
docker-compose exec php composer dumpautoload

# 初始化数据
docker-compose exec php php artisan module.wangdong.easyui:migrate
```

> 开发

功能模块目录`module/group_name/module_name`

```
docker-compose exec php php artisan module.wangdong.easyui:init group_name/module_name
```

### 开发环境

> 环境启动

```
# 启动docker环境
docker-compose -f docker-compose.dev.yml up -d
```

> composer私有仓库配置

修改本地host

```
127.0.0.1 packagist.test
```

浏览器访问 http://packagist.test 初始化设置

> 系统配置

同上

> 开发

同上

> 数据库管理

浏览器访问 http://127.0.0.1:8080

- 系统	PostgreSQL
- 服务器	postgres
- 用户名	postgres
- 密码	postgres
- 数据库  postgres

> C9编辑器

浏览器访问 http://127.0.0.1:8181

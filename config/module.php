<?php

return [
    // 路由配置
    'route' => [
        'default' => 'laravel/easyui',
        'prefix' => '', // TODO 留空时需要屏蔽默认的首页路由
        'middleware' => [],
    ],

    // 路由设置
    'router' => function ($router, $module) {
        $middleware = array_get($module, 'composer.extra.laravel-module.middleware', []);
        $controller = array_get($module, 'controller');

        // Controller路由
        $method = strtolower($_SERVER['REQUEST_METHOD']);
        $action = camel_case(implode('_', [$method, array_first(array_get($module, 'subfix')) ? : 'index']));
        if (method_exists($controller, $action)) {
            $route = array_get($module, 'route');
            $router->$method($route, ['uses' => "{$controller}@{$action}", 'middleware' => $middleware]);
        }

        // RESTful路由
        $resource = array_get($module, 'name');
        $router->group(['prefix' => $resource, 'middleware' => $middleware], function ($router) use ($controller) {
            if (method_exists($controller, 'index')) {
                $router->get('/', $controller . '@index');
            }
            if (method_exists($controller, 'create')) {
                $router->get('/create', $controller . '@create');
            }
            if (method_exists($controller, 'store')) {
                $router->post('/', $controller . '@store');
            }
            if (method_exists($controller, 'show')) {
                $router->get('/{id}', $controller . '@show');
            }
            if (method_exists($controller, 'edit')) {
                $router->get('/{id}/edit', $controller . '@edit');
            }
            if (method_exists($controller, 'update')) {
                $router->put('/{id}', $controller . '@update');
                $router->patch('/{id}', $controller . '@update');
            }
            if (method_exists($controller, 'destroy')) {
                $router->delete('/{id}', $controller . '@destroy');
            }
        });
    },

    // 复制文件(支持闭包)
    'publishes' => function () {
        return [
            module_path('laravel/easyui', 'config') => base_path('config'),
            module_path('laravel/easyui', 'database') => base_path('database'),
            module_path('laravel/easyui', 'public') => base_path('public'),
        ];
    },

    // 命令配置(支持闭包)
    'commands' => [
        Module\Laravel\Easyui\Commands\Init::class,
        Module\Laravel\Easyui\Commands\Migrate::class,
    ],

    // 模块配置，可通过module_config方法获取
    'modules' => [
        // TODO 按照group_name/module_name格式配置(覆盖composer.json > extra.laravel-module.config)
        'laravel' => [
            'easyui' => [
                'name' => '后台管理系统',
                'theme' => 'metro',
                'import' => [
                    'styles' => [],
                    'scripts' => [],
                ],
            ]
        ]
    ],
];

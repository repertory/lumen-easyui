<?php

return [
    // 路由配置
    'route' => [
        'default' => 'wangdong/easyui',
        'prefix' => '', // TODO 留空时需要屏蔽默认的首页路由
        'middleware' => [
            Illuminate\Session\Middleware\StartSession::class,
            Module\Wangdong\Easyui\Middleware::class,
        ],
    ],

    // 复制文件(支持闭包)
    'publishes' => function () {
        return [
            module_path('wangdong/easyui', 'config') => base_path('config'),
            module_path('wangdong/easyui', 'database') => base_path('database'),
            module_path('wangdong/easyui', 'public') => base_path('public'),
        ];
    },

    // 命令配置(支持闭包)
    'commands' => [
        Module\Wangdong\Easyui\Commands\Init::class,
        Module\Wangdong\Easyui\Commands\Migrate::class,
    ],

    // 模块配置，可通过module_config方法获取
    'modules' => [
        // TODO 按照group_name/module_name格式配置(覆盖composer.json > extra.laravel-module.config)
        'wangdong' => [
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

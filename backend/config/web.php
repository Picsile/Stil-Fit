<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'language' => 'RU-ru',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            'cookieValidationKey' => 'a',

            'baseUrl' => '/backend',
            // 'baseUrl' => '',

            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info'],
                ],
            ],
        ],
        'db' => $db,
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            'useFileTransport' => false,
            'transport' => [
                'scheme' => 'smtp',
                'host' => 'smtp.mail.ru',
                'username' => 'noreply@stil-fit.ru',
                'password' => 'PHGa9jiEAhwreMGfCPZe',
                'port' => 587,
                'options' => [
                    'ssl' => [
                        'allow_self_signed' => true,
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ],
                ],
            ],
        ],

        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                // Auth
                "POST auth/register" => "auth/register",
                "OPTIONS auth/register" => "auth/options",

                "POST auth/login" => "auth/login",
                "OPTIONS auth/login" => "auth/options",

                "GET auth/find-by-token" => "auth/find-by-token",
                "OPTIONS auth/find-by-token" => "auth/options",

                "GET auth/logout" => "auth/logout",
                "OPTIONS auth/logout" => "auth/options",

                "GET auth/verify-email" => "auth/verify-email",
                "OPTIONS auth/verify-email" => "auth/options",

                // View
                "GET view/posts-system-board" => "view/posts-system-board",
                "OPTIONS view/posts-system-board" => "view/options",

                "GET view/posts-for-board" => "view/posts-for-board",
                "OPTIONS view/posts-for-board" => "view/options",

                "GET view/posts-in-board" => "view/posts-in-board",
                "OPTIONS view/posts-in-board" => "view/options",

                "GET view/search-posts" => "view/search-posts",
                "OPTIONS view/search-posts" => "view/options",

                "GET view/post" => "view/post",
                "OPTIONS view/post" => "view/options",

                "GET view/similar" => "view/similar",
                "OPTIONS view/similar" => "view/options",

                "GET view/outfits-with-item" => "view/outfits-with-item",
                "OPTIONS view/outfits-with-item" => "view/options",

                "POST view/posts-by-ids" => "view/posts-by-ids",
                "OPTIONS view/posts-by-ids" => "view/options",

                "GET view/generations" => "view/generations",
                "OPTIONS view/generations" => "view/options",

                "GET view/board" => "view/board",
                "OPTIONS view/board" => "view/options",

                "GET view/boards" => "view/boards",
                "OPTIONS view/boards" => "view/options",

                "GET view/profile" => "view/profile",
                "OPTIONS view/profile" => "view/options",

                "GET view/tags" => "view/tags",
                "OPTIONS view/tags" => "view/options",

                "GET view/file" => "view/file",
                "OPTIONS view/file" => "view/options",

                // Post
                "POST post/create-post" => "post/create-post",
                "OPTIONS post/create-post" => "post/options",

                "POST post/delete-post" => "post/delete-post",
                "OPTIONS post/delete-post" => "post/options",

                // Generation
                "POST generation/generate-outfit" => "generation/generate-outfit",
                "OPTIONS generation/generate-outfit" => "generation/options",

                "POST generation/create-generation" => "generation/create-generation",
                "OPTIONS generation/create-generation" => "generation/options",

                "GET generation/check-generation/<generation_id:\d+>" => "generation/check-generation",
                "OPTIONS generation/check-generation/<generation_id:\d+>" => "generation/options",

                // Account
                "POST account/mark-seen" => "account/mark-seen",
                "OPTIONS account/mark-seen" => "account/options",

                "POST account/like" => "account/like",
                "OPTIONS account/like" => "account/options",

                "POST account/create-board" => "account/create-board",
                "OPTIONS account/create-board" => "account/options",

                "DELETE account/delete-board" => "account/delete-board",
                "OPTIONS account/delete-board" => "account/options",

                "DELETE account/delete-post" => "account/delete-post",
                "OPTIONS account/delete-post" => "account/options",

                "POST account/save-post" => "account/save-post",
                "OPTIONS account/save-post" => "account/options",

                "POST account/update-avatar" => "account/update-avatar",
                "OPTIONS account/update-avatar" => "account/options",

                "POST account/update-bg-image" => "account/update-bg-image",
                "OPTIONS account/update-bg-image" => "account/options",
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['*'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['*'],
    ];
}

return $config;

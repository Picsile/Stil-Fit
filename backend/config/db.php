<?php

return [

    // 'class' => 'yii\db\Connection',
    // 'dsn' => 'mysql:host=MySQL-8.4;dbname=lookfit;port=3306',
    // 'username' => 'root',
    // 'password' => '',
    // 'charset' => 'utf8',

    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=' . ($_ENV['DB_HOST'] ?? 'db') . ';dbname=' . ($_ENV['DB_NAME'] ?? ''),
    'username' => $_ENV['DB_USER'] ?? 'root',
    'password' => $_ENV['DB_PASSWORD'] ?? '',
    'charset' => 'utf8',
];

<?php
// 事件定义文件
return [
    'bind'      => [
    ],

    'listen'    => [
//        'AppInit'  => [\think\debugbar\event\AppInit::class],
        'AppInit'=>[],
        'HttpRun'  => [],
        'HttpEnd'  => [],
        'LogLevel' => [],
        'LogWrite' => [],
    ],

    'subscribe' => [
    ],
];

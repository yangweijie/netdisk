<?php
use think\facade\Env;
return [
	// 默认磁盘
	'default' => Env::get('filesystem.driver', 'local'),
	// 磁盘列表
	'disks' => [
		'local' => [
			'type' => 'local',
			'root' => app()->getRuntimePath() . 'storage',
		],
        'github'=>[
            'type'=>'github',
            'token'=>Env::get('filesystem.GITHUB_ACCESS_TOKEN', ''),
            'username'=>Env::get('filesystem.GITHUB_USERNAME', 'yangweijie'),
            'repository'=>Env::get('filesystem.GITHUB_REPOSITORY', 'attachment'),
            'hostIndex'  => Env::get('filesystem.GITHUB_HOST_INDEX', 'jsdelivr'),
            'branch'=>Env::get('filesystem.GITHUB_BRANCH', 'main'),
        ],
		'public' => [
			// 磁盘类型
			'type' => 'local',
			// 磁盘路径
			'root' => app()->getRootPath() . 'public/storage',
			// 磁盘路径对应的外部URL路径
			'url' => '/storage',
			// 可见性
			'visibility' => 'public',
		],
		// 更多的磁盘配置信息
	],
];

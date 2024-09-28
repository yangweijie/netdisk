<?php
declare (strict_types = 1);

namespace app;
use pkg6\flysystem\github\Plugins\FileUrl;
use think\filesystem\Driver;
use think\Service;

/**
 * 应用服务类
 */
class AppService extends Service {
	public function register() {
		// 服务注册
	}

	public function boot() {
        setlocale(LC_ALL,'en_US.UTF-8');
	}

}

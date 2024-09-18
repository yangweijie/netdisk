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

//		// 服务启动
//		$config = new Configuration('983d1152ee2900350938bc8fd5e64518ed92a2fa');
//
//		$inspector = new Inspector($config);
//
//		$pathInfo = explode('?', $_SERVER['REQUEST_URI']);
//		$path = array_shift($pathInfo);
//
//		// Start the transaction
//		$inspector->startTransaction($path);
//
//		// Add segments
//		$inspector->addSegment(function () {
//
//			file_get_contents('https://www.baidu.com');
//
//		}, 'type', 'label');
//
//
//        $inspector->addSegment(function () {
//            sleep(1);
//        }, 'type', 'label2');

//        dump($this->app);
	}

    private function honeybadger()
    {
        $honeybadger = Honeybadger::new([
            'api_key' => 'hbp_QgoSlK7ry8P7DQaWBG0sd5uffPX3Ko3CYtlL'
        ]);
        $honeybadger->customNotification([
            'title'   => 'Special Error',
            'message' => 'Special Error: a special error has occurred',
        ]);
    }
}

<?php
declare (strict_types = 1);

namespace app\middleware;

use Closure;
use think\Request;
use think\Response;

class Auth
{
    /**
     * 处理请求
     *
     * @param Request $request
     * @param Closure       $next
     * @return Response
     */
    public function handle($request, Closure $next)
    {
        $password = app()->config->get('disk.password', '');
        if($password){
            $auth = $request->server('PHP_AUTH_USER', '');
            $pass = $request->server('PHP_AUTH_PW', '');
            if(empty($auth) || ($pass != $password)){
                $response = Response::create('暂未授权，请输入 用户名 music的对应密码!', 'html', 401);
                $response->header([
                    'WWW-Authenticate'=>'Basic realm="My Realm"',
                ]);
                return $response;
            }
        }
        return $next($request);
    }
}

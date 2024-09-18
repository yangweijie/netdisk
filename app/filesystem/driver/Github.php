<?php

namespace app\filesystem\driver;

use pkg6\flysystem\github\GithubAdapter;
use League\Flysystem\AdapterInterface;
use think\filesystem\Driver;

class Github extends Driver
{

    public static $branch = 'main';

    protected function createAdapter(): AdapterInterface
    {
        return new GitHubAdapter(
            $this->config['token'],
            $this->config['username'],
            $this->config['repository'],
            $this->config['branch'] ?? self::$branch,
            $this->config['hostIndex'] ?? ''
        );
    }

    public function url($path): string
    {
        return $this->filesystem->getAdapter()->getUrl($path);
    }
}
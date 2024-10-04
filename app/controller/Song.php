<?php

namespace app\controller;

use app\BaseController;
use think\facade\Filesystem;

class Song extends BaseController
{
    public function index(){
        $files = Filesystem::listContents('');
        $dirs = [];
        foreach ($files as $entry){
            if($entry['type'] === 'dir'){
                $dirs[] = $entry;
            }
        }
        return view('index',['dirs'=>$dirs]);
    }

    public function album(){
        $dir = $this->request->param('name', '');
        if(!$dir){
            throw new \Exception("专辑名必传");
        }
        $songs = [];
        $files = Filesystem::listContents($dir);
        if($files){
            foreach ($files as $file){
                if($file['type'] === 'file'){
                    $songs[] = [
                        'name'=> $file['basename'],
                        'artist'=> '未知',
                        'url'=> 'url.mp3',
                        'cover'=> ''
                    ];
                }
            }
        }
        dd($songs);
        return view('album',['dir'=>$dir,'songs'=>$songs]);
    }
}
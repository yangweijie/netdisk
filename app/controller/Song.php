<?php

namespace app\controller;

use app\BaseController;
use think\facade\Filesystem;
use wapmorgan\Mp3Info\Mp3Info;
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
                    if(in_array($file['extension'], ['mp3'])){
                        $names = explode('-', $file['filename']);
                        $song = [
                            'name'=> $names[0],
                            'artist'=> $names[1]??'未知',
                            'url'=> Filesystem::url($file['path']),
                        ];
                        $lrcFile = str_replace($file['extension'], 'lrc', $file['path']);
                        if(Filesystem::has($lrcFile)){
                            $song['lrc'] = Filesystem::url($lrcFile);
                            $cover = str_replace($file['extension'], 'jpg', $file['path']);
                            $song['cover'] = Filesystem::url($cover);
                        }
                        $songs[] = $song;
                    }
                }
            }
        }
        $songs = json_encode($songs,  JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
//        trace($songs);
        return view('album',['dir'=>$dir,'songs'=> $songs]);
    }
}
<?php
use think\facade\Filesystem;
class DiskHelper
{
    public static $config = [];
    public static function config($config){
        self::$config = $config;
    }

    public static function is_entry_ignored($entry): bool
    {
        if($entry['type'] == 'dir' && !self::$config['allow_show_folders']){
            return true;
        }
        foreach(self::$config['hidden_patterns'] as $pattern) {
            if(fnmatch($pattern, $entry['basename'])) {
                return true;
            }
        }
        return false;
    }

    public static function getSplFile($file)
    {
        return new SplFileInfo($file);
    }

    public static function is_recursively_deleteable(string $d)
    {
        $stack = [$d];
        while($dir = array_pop($stack)) {
            $dirInfo = new SplFileInfo($dir);
            if(!$dirInfo->isReadable() || !$dirInfo->isWritable()){
                return false;
            }
            $files = Filesystem::listContents($dir);
            foreach($files as $file) {
                if($file['type'] === 'dir') {
                    $stack[] = "$dir/$file";
                }
            }
        }
        return true;
    }

    public static function is_dir($file)
    {
        $pathinfo = pathinfo($file);
        return !isset($pathinfo['extension']);
    }
}
<?php
// 应用公共文件

function is_entry_ignored($entry, $allow_show_folders, $hidden_patterns) {
    if ($entry === basename(__FILE__)) {
        return true;
    }

    if (is_dir($entry) && !$allow_show_folders) {
        return true;
    }
    foreach($hidden_patterns as $pattern) {
        if(fnmatch($pattern,$entry)) {
            return true;
        }
    }
    return false;
}

function rmrf($dir) {
    if(is_dir($dir)) {
        $files = array_diff(scandir($dir), ['.','..']);
        foreach ($files as $file)
            rmrf("$dir/$file");
        @rmdir($dir);
    } else {
        @unlink($dir);
    }
}

function is_recursively_deleteable($d) {
    $stack = [$d];
    while($dir = array_pop($stack)) {
        if(!is_readable($dir) || !is_writable($dir))
            return false;
        $files = array_diff(scandir($dir), ['.','..']);
        foreach($files as $file) if(is_dir($file)) {
            $stack[] = "$dir/$file";
        }
    }
    return true;
}

function get_absolute_path($path) {
    $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    $parts = explode(DIRECTORY_SEPARATOR, $path);
    $absolutes = [];
    foreach ($parts as $part) {
        if ('.' == $part) continue;
        if ('..' == $part) {
            array_pop($absolutes);
        } else {
            $absolutes[] = $part;
        }
    }
    return implode(DIRECTORY_SEPARATOR, $absolutes);
}

function asBytes($ini_v) {
    $ini_v = trim($ini_v);
    $s = ['g'=> 1<<30, 'm' => 1<<20, 'k' => 1<<10];
    return intval($ini_v) * ($s[strtolower(substr($ini_v,-1))] ?: 1);
}

function err($code,$msg) {
    $data = [
        'error'=>[
            'code'=>intval($code),
            'msg'=>$msg
        ]
    ];
    return json($data, $code, [], [JSON_UNESCAPED_UNICODE]);
}

//====


function downloadContent($content, $downloadFileName)
{
    $response = new FileResponse($content);
    return $response->name($downloadFileName, false)
        ->isContent()
        ->force(false)
        ->expire(1);
}
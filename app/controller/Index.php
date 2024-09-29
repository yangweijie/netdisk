<?php
namespace app\controller;

use app\BaseController;
use DiskHelper;
use think\facade\Filesystem;

class Index extends BaseController {

    /**
     * @var string
     */
    private $tmp;
    /**
     * @var mixed
     */
    private $config;
    /**
     * @var array|string|string[]
     */
    private $tmp_dir;

    protected function initialize()
    {
        $tmp_dir = dirname($_SERVER['SCRIPT_FILENAME']);
//        trace('tmp_dir:'.$tmp_dir);
        if(DIRECTORY_SEPARATOR==='\\') $tmp_dir = str_replace('/',DIRECTORY_SEPARATOR,$tmp_dir);
        $path = isset($_REQUEST['file'])? $tmp_dir . '/'.$_REQUEST['file'] : $tmp_dir . '/';
        $tmp = get_absolute_path($path);
//        trace('tmp:'. $tmp);
        $this->tmp = $tmp;
        $this->tmp_dir = $tmp_dir;
        $config = config('disk', []);
        $this->config = $config;
        DiskHelper::config($config);
    }

   public function index(){

//        dd(Filesystem::listContents());
        $tmp = $this->tmp;
        $tmp_dir = $this->tmp_dir;
        $file = $_REQUEST['file'] ?? '.';
        if(empty($file)){
            $file = '';
        }
        if($this->request->isPost()){
            if($tmp === false){
                return err(404,'File or Directory Not Found');
            }
            if(substr($tmp, 0,strlen($tmp_dir)) !== $tmp_dir){
                return err(403, 'Forbidden');
            }
            if(strpos($_REQUEST['file'], DIRECTORY_SEPARATOR) === 0){
                return err(403, 'Forbidden');
            }
            if(preg_match('@^.+://@',$_REQUEST['file'])) {
                return err(403, 'Forbidden');
            }
            $do = $this->request->post('do', '');
            switch ($do){
                case 'delete':
                    $this->delete($file);
                    break;
                case 'mkdir':
                    $this->mkdir($file);
                    break;
                case 'upload':
                    $this->upload($file);
                    break;
                default:
                    ;
                    break;
            }
            return json();
        }else{
            if($do = $this->request->get('do', '')){
                switch ($do){
                    case 'list':
                        return $this->list($file);
                        break;
                    default:
                        return $this->download($file);
                        break;
                }
            }
            $MAX_UPLOAD_SIZE = min(asBytes(ini_get('post_max_size')), asBytes(ini_get('upload_max_filesize')));
            return view('index', array_merge($this->config, ['MAX_UPLOAD_SIZE'=>$MAX_UPLOAD_SIZE]));
        }
   }

    private function list($file = '')
    {
        extract($this->config);
        $files = Filesystem::listContents($file);
//        dd($files);
        $result = [];
        foreach ($files as $entry){
            $i = $file.'/'.$entry['basename'];
//            dd($i);
//            $fileInfo = DiskHelper::getSplFile($i);
            if(false === DiskHelper::is_entry_ignored($entry)){
                $result[] = [
                    'mtime'=>$entry['timestamp'],
                    'size'=>$entry['size']??0,
                    'name'=>$entry['basename'],
                    'path'=>preg_replace('@^\./@', '', $i),
                    'is_dir'=>$entry['type'] === 'dir',
                    'is_deleteable'=>true,
                    'is_readable' => true,
                    'is_writable' => true,
                    'is_executable' => false,
                ];
            }
        }

        usort($result, function($f1,$f2){
            $f1_key = ($f1['is_dir']?:2) . $f1['name'];
            $f2_key = ($f2['is_dir']?:2) . $f2['name'];
            return $f1_key > $f2_key?1: ($f1_key == $f2_key?0:-1);
        });

        return json(['success' => true, 'is_writable' => true, 'results' =>$result]);
    }

    private function download($file)
    {
        extract($this->config);
        foreach($disallowed_patterns as $pattern){
            if(fnmatch($pattern, $file)){
                err(403, 'Files of this type are not allowed.');
            }
        }
        $filename = basename($file);
        $content = Filesystem::read($file);
        return downloadContent($content, $filename);
    }

    private function delete($file)
    {
        extract($this->config);
        if($allow_delete) {
            if(DiskHelper::is_dir($file)){
                Filesystem::deleteDir($file);
            }else{
                Filesystem::delete($file);
            }
        }
    }

    private function mkdir($file)
    {
        extract($this->config);
        if($allow_create_folder){
            $file = str_replace('/', '', $file);
            $dir = $file.'/'.$_POST['name'];
            if(substr($dir, 0, 2) === '..'){
                return;
            }
            Filesystem::createDir($dir, Filesystem::getConfig());
        }
    }

    private function upload($file)
    {
        extract($this->config);
        if($allow_upload){
            foreach($disallowed_patterns as $pattern){
                if(fnmatch($pattern, $_FILES['file_data']['name'])){
                    return err(403, 'Files of this type are not allowed.');
                }
            }
            $upload = request()->file('file_data');
            Filesystem::write($file.'/'.$upload->getOriginalName(), file_get_contents($upload->getPathname()));
        }
        return true;
    }
}

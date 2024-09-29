<?php
namespace app\controller;

use app\BaseController;


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
        if(DIRECTORY_SEPARATOR==='\\') $tmp_dir = str_replace('/',DIRECTORY_SEPARATOR,$tmp_dir);
        $path = isset($_REQUEST['file'])? $tmp_dir . '/'.$_REQUEST['file'] : $tmp_dir . '/';
        $tmp = get_absolute_path($path);
        $this->tmp = $tmp;
        $this->tmp_dir = $tmp_dir;
        $config = config('disk', []);
        $this->config = $config;
    }

   public function index(){
        $tmp = $this->tmp;
        $tmp_dir = $this->tmp_dir;
        $file = $_REQUEST['file'] ?? '.';
        if(empty($file)){
            $file = '.';
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
                        $this->download($file);
                        break;
                }
            }
            $MAX_UPLOAD_SIZE = min(asBytes(ini_get('post_max_size')), asBytes(ini_get('upload_max_filesize')));
            return view('index', array_merge($this->config, ['MAX_UPLOAD_SIZE'=>$MAX_UPLOAD_SIZE]));
        }
   }

    private function list($file)
    {
        extract($this->config);
        if (is_dir($file)) {
            $directory = $file;
            $result = [];
            $files = array_diff(scandir($directory), ['.','..']);
            foreach ($files as $entry) if (!is_entry_ignored($entry, $allow_show_folders, $hidden_patterns)) {
                $i = $directory . '/' . $entry;
                $stat = stat($i);
                $result[] = [
                    'mtime' => $stat['mtime'],
                    'size' => $stat['size'],
                    'name' => basename($i),
                    'path' => preg_replace('@^\./@', '', $i),
                    'is_dir' => is_dir($i),
                    'is_deleteable' => $allow_delete && ((!is_dir($i) && is_writable($directory)) ||
                            (is_dir($i) && is_writable($directory) && is_recursively_deleteable($i))),
                    'is_readable' => is_readable($i),
                    'is_writable' => is_writable($i),
                    'is_executable' => is_executable($i),
                ];
            }
            usort($result,function($f1,$f2){
                $f1_key = ($f1['is_dir']?:2) . $f1['name'];
                $f2_key = ($f2['is_dir']?:2) . $f2['name'];
                return $f1_key > $f2_key?1: ($f1_key == $f2_key?0:-1);
            });
            return json(['success' => true, 'is_writable' => is_writable($file), 'results' =>$result]);
        } else {
            return err(412, 'Not a Directory');
        }
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
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        header('Content-Type: ' . finfo_file($finfo, $file));
        header('Content-Length: '. filesize($file));
        header(sprintf('Content-Disposition: attachment; filename=%s',
            strpos('MSIE',$_SERVER['HTTP_REFERER']) ? rawurlencode($filename) : "\"$filename\"" ));
        ob_flush();
        readfile($file);
        exit;
    }

    private function delete($file)
    {
        extract($this->config);
        if($allow_delete) {
            rmrf($file);
        }
    }

    private function mkdir($file)
    {
        trace($this->config);
        extract($this->config);
        if($allow_create_folder){
            $dir = $_POST['name'];
            $dir = str_replace('/', '', $dir);
            if(substr($dir, 0, 2) === '..'){
                return;
            }
            chdir($file);
            @mkdir($_POST['name']);
        }
    }

    private function upload($file)
    {
        extract($this->config);
        if($allow_upload){
            foreach($disallowed_patterns as $pattern)
                if(fnmatch($pattern, $_FILES['file_data']['name'])){
                    return err(403,"Files of this type are not allowed.");
                }
            $res = move_uploaded_file($_FILES['file_data']['tmp_name'], $file.'/'.$_FILES['file_data']['name']);
        }
    }
}

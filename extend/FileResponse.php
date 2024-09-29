<?php

use think\response\File;
use think\Exception;
class FileResponse extends File
{

    /**
     * 设置下载文件的显示名称
     * @access public
     * @param  string $filename 文件名
     * @param  bool   $extension 后缀自动识别
     * @return $this
     */
    public function name(string $filename, bool $extension = true)
    {
        $this->name = $filename;
        if ($extension && !str_contains($filename, '.')) {
            $this->name .= '.' . pathinfo($this->data, PATHINFO_EXTENSION);
        }
        return $this;
    }

    /**
     * 处理数据
     * @access protected
     * @param  mixed $data 要处理的数据
     * @return mixed
     * @throws \Exception
     */
    protected function output($data)
    {
        if (!$this->isContent && !is_file($data)) {
            throw new Exception('file not exists:' . $data);
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        if (!empty($this->name)) {
            $name = $this->name;
        } else {
            $name = !$this->isContent ? pathinfo($data, PATHINFO_BASENAME) : '';
        }

        if ($this->isContent) {
            $mimeType = $this->mimeType;
            $size     = strlen($data);
        } else {
            $mimeType = $this->getMimeType($data);
            $size     = filesize($data);
        }

        $this->header['Pragma']                    = 'public';
        $this->header['Content-Type']              = $mimeType ?: 'application/octet-stream';
        $this->header['Cache-control']             = 'max-age=' . $this->expire;
        $this->header['Content-Disposition']       = ($this->force ? 'attachment; ' : '') . "filename={$name}";
        $this->header['Content-Length']            = $size;
        $this->header['Content-Transfer-Encoding'] = 'binary';
        $this->header['Expires']                   = gmdate("D, d M Y H:i:s", time() + $this->expire) . ' GMT';

        $this->lastModified(gmdate('D, d M Y H:i:s', time()) . ' GMT');

        return $this->isContent ? $data : file_get_contents($data);
    }
}
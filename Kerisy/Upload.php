<?php
Kerisy::import("Zend.File.Transfer.Adapter.Http");

class Kerisy_Upload
{
    private $_file = null;
    private $_message = null;

    public $save_store = 'dir';
    public $upload_tmp_dir = ''; // 临时上传目录
    public $upload_dir = ''; // 上传目录
    public $save_path = ''; // 上传目录的子目录命名

    public $save_dir = ''; // 保存的位置

    public $rename = false;
    public $allow_size = 2097152; // 2M
    public $ftp_upload = false;

    public $clean_location_file = false;

    static $filename = null;
    public $file_name = '';
    public $fileInfo;
    public $create_thumb = false;
    public $thumb_sizes = array();

    private $_thumbs_real_sizes = array();

    public function init($config = array(), $file)
    {
        foreach ($config as $key => $val) {
            if (empty($this->$key)) {
                $this->$key = $val;
            }
        }
        $upload = Kerisy::config()->get()->upload;
        $this->allow_size = $upload['allow_size'];
        $this->setFile($file);
        return $this;
    }

    public function setSaveDir()
    {
        if ($this->save_store == 'dir') {
            $this->save_dir = $this->upload_dir;
        } else {
            $this->save_dir = $this->upload_tmp_dir;
        }

        $this->save_dir .= $this->save_path;
    }

    public function setCreateThumb($create = true)
    {
        $this->create_thumb = $create;
        return $this;
    }

    public function setThumbSizes($thumb_sizes)
    {
        $this->thumb_sizes = $thumb_sizes;
        return $this;
    }

    public function getThumbRealSize()
    {
        return $this->_thumbs_real_sizes;
    }

    public function setFile($file)
    {
        $this->_file = $file;

        return $this;
    }

    public function getFile()
    {
        return $this->_file;
    }

    public function beforeUploadCheckHTTP($file)
    {
        if (!file_exists($this->file['tmp_name'])) {
            return array('上传的文件尺寸过大或无法写入临时目录');
        }

        if (!$this->file['name']) {
            return array('上传的文件文件名无效');
        }
    }

    public function chechFile()
    {
        if ($this->_file['error']) {
            /*switch ($this->_file['error'])
            {
                case 1:
                    $message = '';
                case 2:
                    $message = '';
            }*/

            return array('status' => 'error', 'message' => '上传失败,请重试.');
        }

        if ($this->_file['size'] > $this->allow_size) {
            return array('status' => 'error', 'message' => '上传文件超过允许大小');
        }

        if (!file_exists($this->_file['tmp_name'])) {
            return array('status' => 'error', 'message' => '上传的文件无法写入临时目录');
        }

        return true;
    }

    public function setSavePath($path)
    {
        $this->save_path = $path;
    }

    public function upload()
    {
        $check = $this->chechFile();
        if (true !== $check) {
            return $check;
        }

        $adapter = new Zend_File_Transfer_Adapter_Http();

        $this->setSaveDir();

        if (!is_dir($this->save_dir)) {
            mkdirs($this->save_dir);
            if (!is_dir($this->save_dir)) {
                $this->_message = '文件保存目录不存在，并且未能创建！';
                return false;
            }
        }

        $adapter->setDestination($this->save_dir);

        $adapter->addFilter('Rename', array(
            'target' => $this->getFileName(),
            'overwrite' => true
        ));

        if (!$adapter->receive()) {
            $this->_message = $adapter->getMessages();
            return false;
        }

        if ($this->create_thumb) {
            $Thumb = Kerisy::loadClass('Kerisy_Thumb');
            $Thumb->setDriver(Kerisy::config()->get()->image['driver']);
            // $Thumb->setQuality(Kernel::config()->get()->image['thumb']['quality']);

            $thumbs = $Thumb->createThumb($this->getFileName(), $this->save_dir, $this->save_dir, $this->thumb_sizes);
            $this->_thumbs_real_sizes = $Thumb->getThumbRealSize();
        }
        if ($this->save_store == 'ftp') {
            if (!$this->syncFtp($this->save_dir . $this->getFileName(), $this->save_path . $this->getFileName())) {
                return false;
            }

            if (is_array($thumbs)) {
                foreach ($thumbs as $thumb) {
                    if (!$this->syncFtp($this->save_dir . $thumb, $this->save_path . $thumb)) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    public function syncFtp($load_filename, $ftp_filename, $clean_local_file = true)
    {
        $ftp = Kerisy::loadClass('Kerisy_Ftp');
        $ftp->connect(Kerisy::config()->get()->ftp);

        if (!$ftp->upload($load_filename, $ftp_filename, 'binary')) {
            $this->_message = 'FTP上传文件失败！';
            return false;
        }

        if ($clean_local_file) {
            @unlink($load_filename);
        }

        $ftp->close();

        return true;
    }

    /*
        $file 表单中 type=file 的名称
        $path 存储目录 请使用相对路径!!
        $extension 接受的文件
        $target_filename 目标文件名, 空则使用用户文件名
        $clean_temp 如果是 FTP 上传模式是否删除本地文件
    */

    public function uploadByUrl($url)
    {
        if (strstr($url, 'http://')) {
            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0)');
            curl_setopt($curl, CURLOPT_HTTPHEADER,
                array('Accept-Language: zh-cn', 'Connection: Keep-Alive', 'Cache-Control: no-cache'));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

            $file_content = curl_exec($curl);

            curl_close($curl);
        } else {
            $file_content = file_get_contents($url);
        }

        self::$filename = md5_file($url) . ('.' . end(explode('.', $url)));
        $this->setSaveDir();

        if (!is_dir($this->save_dir)) {
            mkdirs($this->save_dir);
        }

        if (!is_dir($this->save_dir)) {
            return array('status' => 'error', 'message' => '文件保存目录不存在');
        }

        $new_file = file_put_contents($this->save_dir . $this->getFileName(), $file_content);
        if (!$new_file) {
            return array('文件目录 ' . $this->save_dir . ' 无法写入。');
        }

        if ($this->create_thumb) {
            $Thumb = Kerisy::loadClass('Kerisy_Thumb');
            $Thumb->setDriver(Kerisy::config()->get()->image['driver']);
            $thumbs = $Thumb->createThumb($this->getFileName(), $this->save_dir, $this->save_dir, $this->thumb_sizes);
            $this->_thumbs_real_sizes = $Thumb->getThumbRealSize();
        }

        if ($this->save_store == 'ftp') {
            if (!$this->syncFtp($this->save_dir . $this->getFileName(), $this->save_path . $this->getFileName())) {
                return false;
            }

            if ($this->create_thumb && $thumbs) {
                foreach ($thumbs as $thumb) {

                    if (!$this->syncFtp($this->save_dir . $thumb, $this->save_path . $thumb)) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /*
        $file 要删除的文件 相对路径
    */

    public function delete($file)
    {
        if (Kernel::config()->get()->upload['save_store'] == 'ftp') {
            $ftp = $this->connectFTP();

            if ($ftp->chmod(Kernel::config()->get()->ftp['remote_dir'] . $file, 0775)) {
                $ftp->delete_file(Kernel::config()->get()->ftp['remote_dir'] . $file);
                $ftp->delete_file(Kernel::config()->get()->ftp['remote_dir'] . $file);
            }

            $ftp->close();

            return true;
        }

        unlink(Kernel::config()->get()->upload['upload_dir'] . $file);

        return false;
    }

    /*
        $file 检查文件是否存在 相对路径
    */

    public function checkFileExists($file)
    {
        if (Kernel::config()->get()->upload['save_store'] == 'ftp') {
            $ftp = $this->connectFTP();

            $exists = $ftp->chmod(Kernel::config()->get()->ftp['remote_dir'] . $file, 0777);

            $ftp->close();

            if ($exists) {
                return true;
            }
        } else {
            if (file_exists(Kernel::config()->get()->upload['upload_dir'] . $file)) {
                return true;
            }
        }

        return false;
    }

    public function move($source, $destination)
    {
        if (Kernel::config()->get('uploads')->ftp_upload) {
            $ftp = $this->connectFTP();

            $this->mkdirFtp(dirname(Kernel::config()->get('uploads')->ftp_remote_dir . $destination));

            $ftp->move(Kernel::config()->get('uploads')->ftp_remote_dir . $source,
                Kernel::config()->get('uploads')->ftp_remote_dir . $destination);
        } else {
            $path_tree = explode('/', dirname(Kernel::config()->get('uploads')->ftp_remote_dir . $destination));

            foreach ($path_tree AS $path_dir) {
                if (strlen($path_dir) > 0) {
                    $_path_dir .= $path_dir . '/';

                    @mkdir(Kernel::config()->get('uploads')->local_upload_dir . $_path_dir);
                }
            }

            rename(Kernel::config()->get('uploads')->local_upload_dir . $source,
                Kernel::config()->get('uploads')->local_upload_dir . $destination);
        }
    }

    public function getPostfix()
    {
        return '.' . end(explode('.', $this->_file['name']));
    }

    public function getExtendByMime($mime)
    {
        $allowed_image_mime = array(
            'image/pjpeg' => "jpg",
            'image/jpeg' => "jpg",
            'image/jpg' => "jpg",
            'image/png' => "png",
            'image/x-png' => "png",
            'image/gif' => "gif"
        );

        if (isset($allowed_image_mime[$mime])) {
            return $allowed_image_mime[$mime];
        }

        return false;
    }

    public function getFileName()
    {
        if (!self::$filename) {
            $image_info = getimagesize($this->_file['tmp_name']);
            if (!$ext = $this->getExtendByMime($image_info['mime'])) {
                show_error('不允许的文件类型！');
            }
            $this->fileInfo = ['w' => $image_info[0], 'h' => $image_info[1], 'ext' => $ext];
            self::$filename = sha1_file($this->_file['tmp_name']) . '.' . $ext;
        }

        return self::$filename;
    }

    public function getFileInfo()
    {
        return $this->fileInfo;
    }

    public function getMessage()
    {
        return $this->_message;
    }
}

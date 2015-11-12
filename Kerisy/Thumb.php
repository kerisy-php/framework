<?php

class Kerisy_Thumb
{
    private $_messate = null;
    private $_dirname = '';
    private $_driver = 'ImageMagick'; // GD or ImageMagick
    private $_quality = 90;

    private $_thumb_real_size = array();

    public function __construct()
    {

    }

    public function setDriver($driver)
    {
        $this->_driver = $driver;
    }

    public function setQuality($quality)
    {
        $this->_quality = $quality;
    }

    public function getThumbRealSize()
    {
        return $this->_thumb_real_size;
    }

    public function createThumb($filename, $file_dir, $save_dir, $thumb_sizes)
    {
        if (!$thumb_sizes) {
            $this->_message = '缺少缩略图尺寸配置！';
            return false;
        }

        if (!$image_info = getimagesize($file_dir . $filename)) {
            $this->_message = '无法获得图片信息！';
            return false;
        }

        $this->_thumb_real_size = array();
        foreach ($thumb_sizes as $size) {
            $_size = explode('x', $size);

            $width = $_size[0];
            $height = $_size[1];

            Kerisy::import("Kerisy.Image");
            $image = Kerisy_Image::factory($file_dir . $filename, $this->_driver);

            if ($_size[0] && $_size[1]) {
                if ($_size[0] == $_size[1]) {
                    $what_size = $image_info[0] > $image_info[1] ? $image_info[1] : $image_info[0];

                    $image->crop($what_size, $what_size,
                        $image_info[0] > $image_info[1] ? ceil(($image_info[0] - $image_info[1]) / 2) : 0,
                        $image_info[0] < $image_info[1] ? ceil(($image_info[1] - $image_info[0]) / 2) : 0);
                } else {
                    $width_b = $image_info[0] / $_size[0];
                    $height_b = $image_info[1] / $_size[1];
                    if ($width_b > $height_b) {
                        $image->crop($image_info[0] * $width_b * $height_b, $image_info[1], 'center', 'center');
                    } else {
                        $image->crop($image_info[0], $image_info[1] * $width_b * $height_b, 'center', 'center');
                    }
                }
            } else {
                if ($_size[0] && $image_info[0] < $_size[0]) {
                    $width = $image_info[0];
                }

                if ($_size[1] && $image_info[1] < $_size[1]) {
                    $height = $image_info[1];
                }
            }

            $image->resize($width, $height)->sharpen(100);

            $thumb_image = preg_replace('/(\.[\w]+)$/', '_' . $size . '\\1', $filename);

            $image->save($save_dir . $thumb_image, $this->_quality);

            $thumb_info = getimagesize($save_dir . $thumb_image);

            $thumb_files[] = $thumb_image;

            $this->_thumb_real_size[$size] = array(
                'w' => $thumb_info[0],
                'h' => $thumb_info[1]
            );
        }

        return $thumb_files;
    }

    public function getMessage()
    {
        return $this->_message;
    }
}

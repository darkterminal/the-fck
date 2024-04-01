<?php

namespace Fckin\core\utils;

class File
{
    public function file_upload($file_input_name, $destination)
    {
        if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] === UPLOAD_ERR_OK) {
            $temp_file = $_FILES[$file_input_name]['tmp_name'];
            $destination = rtrim($destination, '/') . '/' . $_FILES[$file_input_name]['name'];
            return move_uploaded_file($temp_file, $destination);
        }
        return false;
    }

    public function file_delete($path)
    {
        if (file_exists($path)) {
            return unlink($path);
        }
        return false;
    }

    public function file_exists($path)
    {
        return file_exists($path);
    }

    public function file_extension($filename)
    {
        return pathinfo($filename, PATHINFO_EXTENSION);
    }
}

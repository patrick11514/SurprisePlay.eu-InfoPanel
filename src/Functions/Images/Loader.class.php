<?php

/**
 * Load pictures base by url
 * 
 * @author    patrick115 <info@patrick115.eu>
 * @copyright ©2020
 * @link      https://patrick115.eu
 * @link      https://github.com/patrick11514
 * @version   1.0.0
 * 
 */

namespace patrick115\Images;

class Loader {
    
    /**
     * Path to storage
     */
    private $storage;

    /**
     * Construct function
     * @param string $storage - Path to stroage
     * @return string
     */
    public function __construct($storage)
    {
        if (!file_exists($storage)) {
            if (is_writable(dirname($storage))) {
                if (is_readable(dirname($storage))) {
                    $f = fopen($storage, "w");
                    fwrite($f, json_encode(["images" => []]));
                    fclose($f);
                    $this->storage = $storage;
                } else {
                    return $this->errorException("Storage directory is not readable");
                }
            } else {
                return $this->errorException("Storage directory is not writable");
            }
        } else {
            $this->storage = $storage;
        }
    }

    /**
     * Show picture
     * @return string
     */
    public function showPicture()
    {

        if (empty($_GET["image"]) || empty($_GET["ts"])) {
            return $this->errorException("Empty parameters");
        }

        $extension = explode(".", $_GET["image"])[1];

        $image = pack("H*", explode(".", $_GET["image"])[0]);
        $timestamp = $_GET["ts"];
        if ((time() - $timestamp) > 5) {
            return $this->errorException("Bad URL");
        }
        $name = explode(";", $image)[0];
        $createtime = explode(";", $image)[1];

        $json = json_decode(file_get_contents($this->storage), 1);

        $json = $json["images"];

        if (empty($json[$name . "." . $extension])) {
            return $this->errorException("File in storage not found!");
        }

        if ($createtime != $json[$name . "." . $extension]) {
            return $this->errorException("Create time is not equal");
        }

        if (!file_exists(dirname($this->storage) . "/{$name}.{$extension}")) {
            return $this->errorException("File doesn't exists!");
        }

        switch($extension) {
            case "jpg":
                $header = "jpeg";
            break;
            case "jpeg":
                $header = "jpeg";
            break;
            case "png":
                $header = "png";
            break;
            case "gif":
                $header = "gif";
            break;
        }

        header("Content-Type: image/{$header}");
        readfile(dirname($this->storage) . "/{$name}.{$extension}");

    }

    /**
     * Create json error by message
     * @param string $message - message of error
     * @return string
     */
    public function errorException($message)
    {
        header("Content-type: application/json; charset=utf-8");
        return json_encode([
            "success" => false,
            "message" => $message
        ]);
    }
}
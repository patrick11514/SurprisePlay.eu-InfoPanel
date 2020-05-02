<?php

/**
 * Uploader for Core of Requests
 * 
 * @author    patrick115 <info@patrick115.eu>
 * @copyright ©2020
 * @link      https://patrick115.eu
 * @link      https://github.com/patrick11514
 * @version   1.0.0
 * 
 */


namespace patrick115\Images;

use patrick115\Main\Tools\Utils;

class Uploader {

    /**
     * Name of file
     * @var string
     * 
     * Extension of file
     * @var string
     * 
     * Path to temp file
     * @var string
     * 
     * Upload error
     * @var int
     * 
     * Allowed extensions
     * @var array
     */
    private
        $name,
        $extension,
        $temp,
        $error,
        $allowed_exts;

    /**
     * Error
     * @var string
     * 
     * Url of uploaded file
     * @var string
     */
    public $_error, $url;

    /**
     * Path to uploading dir
     * @var string
     * 
     * Path to storage of files
     * @var string
     */
    const 
    upload_dir = "./src/images",
    storage = "./src/images/.storage";

    /**
     * Construct function
     * @param array $data - Data of uploaded file
     */
    public function __construct(array $data)
    {
        $this->allowed_exts = $data["allowed_extensions"];
        $this->name = explode(".", $data["file_data"]["name"])[0];
        $this->extension = explode(".", $data["file_data"]["name"])[1];
        $this->temp = $data["file_data"]["tmp_name"];
        $this->error = $data["file_data"]["error"];
    }

    /**
     * Function to upload file
     * @return mixed
     */
    public function uploadFile()
    {
        if ($this->error != 0) {
            return $this->getError($this->error);
        }

        if  (!$this->checkExtension()) {
            $this->deleteFile();
            return "Nahrávání tohoto souboru není povoleno! (" . implode(", ", $this->allowed_exts) .")";
        }

        if (!$this->upload(self::upload_dir)) {
            $this->deleteFile();
            return $this->_error;
        }
        return true;
    }

    /**
     * Convert error
     * @param int $error - Error code
     * @return string
     */
    private function getError(int $error)
    {
        switch($error) {
            case UPLOAD_ERR_INI_SIZE:
                $error = "Soubor je moc velký!";
            break;
            case UPLOAD_ERR_FORM_SIZE:
                $error = "Soubor je moc velký!";
            break;
            case UPLOAD_ERR_PARTIAL:
                $error = "Nahrála se pouze část souboru, opakujte nahrávání";
            break;
            case UPLOAD_ERR_NO_FILE:
                $error = "Žádný soubor nebyl nahrán!";
            break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $error = "Nenalezena temp složka!";
            break;
            case UPLOAD_ERR_CANT_WRITE:
                $error = "Temp složka nemá práva na zápis";
            break;
            case UPLOAD_ERR_EXTENSION:
                $error = "Soubor obsahuje neznámou příponu.";
            break;
            default:
                $error = "Při nahrávání souboru se vyskytla chyba!";
            break;
        }

        return $error;
    }

    /**
     * Check extension, if is allowed
     * @return bool
     */
    private function checkExtension()
    {
        $allowed = $this->allowed_exts;
        $ext = $this->extension;

        if (in_array($ext, $allowed)) {
            return true;
        }
        
        return false;
    }

    /**
     * Delete temp file
     */
    private function deleteFile()
    {
        unlink($this->temp);
    }
    
    /**
     * Upload file
     * @param string $dest - Destination for file
     * @return bool
     */
    private function upload(string $dest)
    {
        if (file_exists($dest)) {
            if (is_writable($dest)) {
                if (is_readable($dest)) {
                    $done = false;
                    $cn = null;

                    while (!$done) {
                        $name = $this->name . $cn . "." . $this->extension;
                        if (!file_exists($dest . "/" . $name)) {
                            $done = true;
                        }
                        $cn++;
                    }
                    
                    copy($this->temp, $dest . "/" . $name);
                    unlink($this->temp);

                    $f = file_get_contents(self::storage);
                
                    $json = json_decode($f, 1);

                    $c_time = microtime(true);

                    #fixing mictrotime
                    $c_time = (int) ((string) $c_time);
                    ##

                    $json["images"][$name] = $c_time;

                    $f = fopen(self::storage, "w");
                    fwrite($f, json_encode($json));
                    fclose($f);

                    $this->url = "%%domain%%/src/images/?image=" . Utils::createPackage(explode(".", $name)[0] . ";" . $c_time)[1] . "." . explode(".", $name)[1] . "&ts=%time%";

                    return true;
                } else {
                    $this->_error = "Složka není na čtení!";
                    return false;
                }
            } else {
                $this->_error = "Složka není zapisovatelná!";
                return false;
            }
        } else {
            $this->_error = "Složka neexistuje!";
            return false;
        }
    }

}
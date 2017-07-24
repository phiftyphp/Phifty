<?php
namespace Phifty;
use Exception;

// Upload Header is like:
//
//        (
//            [HOST] => phifty.local
//            [CONNECTION] => keep-alive
//            [REFERER] => http://phifty.local/bs/image
//            [CONTENT-LENGTH] => 96740
//            [ORIGIN] => http://phifty.local
//            [X-UPLOAD-TYPE] => image/png
//            [X-UPLOAD-FILENAME] => Screen shot 2011-08-17 at 10.25.58 AM.png
//            [X-UPLOAD-SIZE] => 72555
//            [USER-AGENT] => Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/13.0.782.218 Safari/535.1
//            [CONTENT-TYPE] => application/xml
//            [ACCEPT] => */*
//            [ACCEPT-ENCODING] => gzip,deflate,sdch
//            [ACCEPT-LANGUAGE] => en-US,en;q=0.8
//            [ACCEPT-CHARSET] => UTF-8,*;q=0.5
//            [COOKIE] => PHPSESSID=6dqs40ngvldtjrg9iim3uafnl3; locale=zh_TW
//        )

class Html5UploadHandler
{
    public $content;
    public $headers;
    public $uploadDir;
    public $field;

    public function __construct($field = false)
    {
        if ($field)
            $this->field = $field;

        $this->content = $this->decodeContent();
        if ( function_exists('getallheaders') )
            $this->headers = @getallheaders();
        if ( $this->headers )
            $this->headers = array_change_key_case($this->headers, CASE_UPPER);
    }

    public function supportSendAsBinary()
    {
        return count($_FILES) > 0;
    }

    public function getFileName()
    {
        if ( isset($_SERVER['HTTP_X_UPLOAD_FILENAME']) )

            return $_SERVER['HTTP_X_UPLOAD_FILENAME'];
        if ( isset( $this->headers[ 'X-UPLOAD-FILENAME' ] ) )

            return $this->headers[ 'X-UPLOAD-FILENAME' ];
    }

    public function getFileType()
    {
        if ( isset($_SERVER['HTTP_X_UPLOAD_TYPE']) )

            return $_SERVER['HTTP_X_UPLOAD_TYPE'];
        if ( isset($this->headers[ 'X-UPLOAD-TYPE' ]) )

            return $this->headers[ 'X-UPLOAD-TYPE' ];
    }

    public function getFileSize()
    {
        if ( isset($_SERVER['HTTP_X_UPLOAD_SIZE']) )

            return $_SERVER['HTTP_X_UPLOAD_SIZE'];
        if ( isset($this->headers[ 'X-UPLOAD-SIZE' ]) )

            return $this->headers[ 'X-UPLOAD-SIZE' ];
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function setUploadDir( $dir )
    {
        $this->uploadDir = $dir;
    }

    public function decodeContent()
    {
        $content = file_get_contents('php://input');
        if (isset($_GET['base64'])) {
            $content = base64_decode( $content );
        }

        return $content;
    }

    public function hasFile()
    {
        if ( count($_FILES) > 0 )

            return true;

        if ( $this->content )

            return true;

        return false;
    }

    public function move( $newFileName = null )
    {
        if ($this->field) {
            if ( ! isset($_FILES[$this->field]['name'] ) ) {
                throw new Exception( "File field {$this->field}: name is empty");
            }

            if ($err = $_FILES[$this->field]['error']) {
                throw new Exception( "File field {$this->field} error: $err");
            }

            /* process with $_FILES */
            // $_FILES['upload']['tmp_name'];
            $filename = $newFileName ? $newFileName : $_FILES[$this->field]['name'];
            $path = $this->uploadDir . DIRECTORY_SEPARATOR . $filename;
            $path = FileUtils::filename_increase($path);
            if ( move_uploaded_file( $_FILES['upload']['tmp_name'] , $path ) === false ) {
                return false;
            }

            return $path;
        } else {
            $content = $this->getContent();
            $filename = $newFileName ? $newFileName : $this->getFileName();
            $path = $this->uploadDir . DIRECTORY_SEPARATOR . $filename;
            $path = FileUtils::filename_increase($path);
            if ( file_put_contents( $path , $content ) === false ) {
                return false;
            }

            return $path;
        }
    }
}

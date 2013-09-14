<?php
namespace Phifty;

/*
    $f = new UploadFile( 'ufile' , 0 );   // name and index   , $_FILES['ufile'][...][0]
    $f->putIn( "file_dirs" );
 */
class UploadFile
{

    // file data object
    public $column;  // file column name

    public $name;
    public $type;
    public $size;
    public $tmp_name;
    public $error;
    public $saved_path;

    public function __construct( $name , $index = null )
    {
        $this->column = $name;
        $hasFile = (bool) @$_FILES[$name]['tmp_name'];
        if ($hasFile) {
            $keys = array_keys( @$_FILES[ $name ] );
            if ($index) {
                foreach( $keys as $key )
                    $this->$key = $_FILES[ $name ][ $key ][ $index ];
            } else {
                foreach( $keys as $key )
                    $this->$key = $_FILES[ $name ][ $key ];
            }
        }
    }

    public function __destruct()
    {

    }

    public function getKBytes()
    {
        return (int) $this->size / 1024;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getExtension()
    {
        $parts = explode('.',$this->name);

        return end($parts);
    }

    /* size: kbytes */
    public function validateSize( $size )
    {
        return ($this->size / 1024) < $size;
    }

    public function validateExtension( $exts )
    {
        $ext = strtolower($this->getExtension());

        return in_array( $ext, $exts );
    }

    public function getSavedPath() { return $this->saved_path; }
    public function getType() { return $this->type; }
    public function getSize() { return $this->size; }

    /**
     * In this method, we don't modify tmp_name attribute
     * rather than that, we set the saved_path attribute
     * for location of these moved files.
     */
    public function putIn( $targetDir , $targetFileName = null, $useCopy = false )
    {
        /* source file */
        $file = $this->tmp_name;
        if ( ! file_exists($file) && isset( $_FILES[ $this->column ]['saved_path'] ) ) {
            $useCopy = true;
            $file = $_FILES[ $this->column ]['saved_path'];
        }


        // if targetFilename is not given,
        // we should take the filename from original filename by using basename.
        if ( ! $targetFileName )
            $targetFileName = basename( $this->name );

        // make sure we have the directory exists.
        if ( ! file_exists( $targetDir ) )
            FileUtils::mkpath( $targetDir );

        // relative file path.
        $newPath = FileUtils::path_join( $targetDir , $targetFileName );

        /* avoid file name duplication */
        $fileCnt = 1;
        while ( file_exists($newPath) ) {
            $newPath = FileUtils::path_join( $targetDir ,
                FileUtils::filename_suffix( $targetFileName , '_' . $fileCnt++ ) );
                // substr(md5_file( $file ),0,5) . '_' . $targetFileName );
        }

        /* register to $_FILES[ name ][ savedpath ]
         *
         * in CRUD action, we need to validate if a action file column's value
         * is a real upload file.
         * */
        $this->saved_path = $newPath;

        if ($useCopy) {
            copy($file, $newPath);
        } else {
            $this->move( $file , $newPath );
        }
        $_FILES[ $this->column ]['saved_path'] = $newPath;

        return $newPath;
    }

    public function move( $from , $to )
    {
        if ( ! $from || ! file_exists( $from ) )
            throw new \Exception('Source file not found.');

        if ( $this->error != 0 )
            throw new \Exception('File Upload Error:' . $this->getErrorMessage() );
        if ( false === move_uploaded_file( $from , $to ) )
            throw new \Exception('File Upload Error: Move uploaded file failed.');
    }

    public function deleteTmp()
    {
        unlink( $this->tmp_name );
    }

    public function found()
    {
        return $this->name ? true : false;
    }

    public function hasError()
    {
        return (bool) $this->error;
    }

    public function getErrorMessage()
    {
        $error = $this->error;

        // error messages for normal users.
        switch ($error) {
            case UPLOAD_ERR_OK:
                return _("No Error");
            case UPLOAD_ERR_INI_SIZE || UPLOAD_ERR_FORM_SIZE:
                return _("The upload file exceeds the limit.");
            case UPLOAD_ERR_PARTIAL:
                return _("The uploaded file was only partially uploaded.");
            case UPLOAD_ERR_NO_FILE:
                return _("No file was uploaded.");
            case UPLOAD_ERR_CANT_WRITE:
                return _("Failed to write file to disk.");
            case UPLOAD_ERR_EXTENSION:
                return _("A PHP extension stopped the file upload.");
            default:
                return _("Unknown Error.");
        }

        // built-in php error description
        switch ($error) {
            case UPLOAD_ERR_OK:
                return _("There is no error, the file uploaded with success.");
            case UPLOAD_ERR_INI_SIZE:
                return _("The uploaded file exceeds the upload_max_filesize directive in php.ini.");
            case UPLOAD_ERR_FORM_SIZE:
                return _("The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.");
            case UPLOAD_ERR_PARTIAL:
                return _("The uploaded file was only partially uploaded.");
            case UPLOAD_ERR_NO_FILE:
                return _("No file was uploaded.");
            case UPLOAD_ERR_NO_TMP_DIR:
                return _("Missing a temporary folder. Introduced in PHP 4.3.10 and PHP 5.0.3.");
            case UPLOAD_ERR_CANT_WRITE:
                return _("Failed to write file to disk. Introduced in PHP 5.1.0.");
            case UPLOAD_ERR_EXTENSION:
                return _("A PHP extension stopped the file upload. PHP does not provide a way to ascertain which extension caused the file upload to stop; examining the list of loaded extensions with phpinfo() may help. Introduced in PHP 5.2.0.");
            default:
                return _("Unknown Error.");
        }
    }

}

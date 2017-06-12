<?php
namespace Phifty;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class FileUtils
{
    public static function read_dir_for_dir($dir)
    {
        return futil_scanpath_dir($dir);
    }

    public static function pretty_size($bytes)
    {
        return futil_prettysize($bytes);
    }


    public static function path_join($list)
    {
        if ( is_array($list) ) {
            return futil_pathjoin($list);
        }
        $args = func_get_args();
        return futil_pathjoin($args);
    }

    public static function mkdir( $path , $verbose = false , $mode = 0777 )
    {
        if ( $verbose )
            echo "Creating dir: $path\n";
        futil_mkdir_if_not_exists($path, $mode, true);
    }

    public static function mkpath( $paths , $verbose = false , $mode = 0777 )
    {
        $paths = (array) $paths;
        foreach ($paths as $path) {
            if ( $verbose )
                echo "\tCreating directory $path\n";
            futil_mkdir_if_not_exists($path, $mode, true);
        }
    }

    public static function create_keepfile( $path )
    {
        $keepfile = static::path_join( $path , '.keep' );
        touch( $keepfile );
    }

    /* substract cwd path */
    public static function relative_path( $abspath )
    {
        $path = realpath( $abspath );
        $cwd = getcwd();

        return substr( $path , strlen( $cwd ) + 1 );
    }

    /* remove base path , return relative path */
    public static function remove_base( $path , $base )
    {
        return substr( $path , strlen( $base ) + 1 );
    }

    public static function expand_path( $path )
    {
        $start = strpos( $path , '{' );
        $end   = strpos( $path , '}' , $start );

        if ( $start === false || $end === false )

            return (array) $path;

        $expand = explode(',',substr( $path , $start + 1 , $end - $start - 1 ));
        $wstr_start = substr( $path , 0 , $start  );
        $wstr_end   = substr( $path , $end + 1 );
        $paths = array();
        foreach ($expand as $item) {
            $paths[] = $wstr_start . $item . $wstr_end;
        }

        return $paths;
    }

    /*
     * Expand dir to file paths
     *
     * Return file list with fullpath.
     * */
    public static function expand_dir($dir)
    {
        if ( is_dir($dir) ) {
            $files = array();
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir),
                                                    \RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($iterator as $path) {
                if ($path->isDir()) {
                    # rmdir($path->__toString());
                } elseif ( $path->isFile() ) {
                    array_push( $files , $path->__toString() );
                }
            }

            return $files;
        }

        return array($dir);
    }

    public static function concat_files( $files )
    {
        $content = '';
        foreach ($files as $file) {
            $content .= file_get_contents( $file );
        }

        return $content;
    }

    public static function filename_md5($filename, $tmpFile = null)
    {
        $md5 = $tmpFile ? md5($tmpFile) : md5($filename . time());
        $info = pathinfo($filename);
        return "{$info['dirname']}/{$md5}.{$info['extension']}";
    }

    public static function filename_append_md5($filename, $tmpFile = null)
    {
        $suffix = $tmpFile ? md5($tmpFile) : md5($filename . time());
        $pos = strrpos( $filename , '.' );
        if ($pos) {
            return
                substr( $filename , 0 , $pos )
                . $suffix
                . substr( $filename , $pos );
        }

        return $filename . $suffix;
    }

    public static function filename_increase($path)
    {
        if ( ! file_exists($path) )

            return $path;

        $pos = strrpos( $path , '.' );
        if ($pos !== false) {
            $filepath = substr($path, 0 , $pos);
            $extension = substr($path, $pos);
            $newfilepath = $filepath . $extension;
            $i = 1;
            while ( file_exists($newfilepath) ) {
                $newfilepath = $filepath . " (" . $i++ . ")" . $extension;
            }

            return $newfilepath;
        }

        return $path;
    }

    public static function filename_suffix( $filename , $suffix )
    {
        $pos = strrpos( $filename , '.' );
        if ($pos !== false) {
            return substr( $filename , 0 , $pos )
                . $suffix
                . substr( $filename , $pos );
        }

        return $filename . $suffix;
    }

    public static function mimetype( $file )
    {
        $fi = new \finfo( FILEINFO_MIME );
        $info = $fi->buffer(file_get_contents($file));
        $attrs = explode(';',$info);

        return $attrs[0];
    }

    public static function is_cache_expired( $cacheFile , $targetFile )
    {
        return filemtime($targetFile) > filemtime($cacheFile);
    }

    public static function remove_cwd($path)
    {
        return substr($path, strlen(getcwd()) + 1 );
    }

    public static function fileobject_from_path($path)
    {
        $pathinfo = pathinfo($path);
        $file = array(
            'name' => $pathinfo['basename'],
            'tmp_name' => $path,
            'saved_path' => $path,
            'size' => filesize($path)
        );

        return $file;
    }
}

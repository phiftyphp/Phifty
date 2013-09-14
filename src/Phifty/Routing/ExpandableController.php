<?php
namespace Phifty\Routing;
use Phifty\Controller;
abstract class ExpandableController extends Controller
{
    /**
     * @var array Class name => Mount Path
     */
    static $mountPaths = array();

    public static function expand() {  }

    public static function set_mount_path($path)
    {
        static::$mountPaths[ get_called_class() ] = $path;
    }

    public static function get_mount_path()
    {
        $class = get_called_class();
        if ( isset(static::$mountPaths[$class]) ) {
            return static::$mountPaths[$class];
        }
    }


}

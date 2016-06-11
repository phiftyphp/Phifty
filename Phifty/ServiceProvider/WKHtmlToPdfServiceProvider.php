<?php
namespace Phifty\ServiceProvider;
use Phifty\Kernel;


class WKHtmlToPdf
{
    public $bin;
    public $pageSize;

    public function __construct($bin,$pageSize = 'A4')
    {
        $this->bin = $bin;
        $this->pageSize = $pageSize;
    }

    public function convert($url,$target)
    {
        $cmds = array($this->bin);
        if ($this->pageSize) {
            $cmds[] = '--page-size';
            $cmds[] = $this->pageSize;
        }
        putenv('DISPLAY=:1');
        $cmds[] = "\"$url\"";
        $cmds[] = $target;
        $cmd = join(' ',$cmds);
        system($cmd);
        return $target;
    }
}

/**
 * WebKitHtmlToPdf
 *
 * Usage:
 *
 *  require 'main.php';
 *  kernel()->wkHtmlToPdf->convert('http://google.com','test.pdf');
 *  system('open test.pdf');
 */
class WKHtmlToPdfServiceProvider extends BaseServiceProvider
{
    public function getId() { return 'wkhtmltopdf'; }

    public function register(Kernel $kernel, $options = array() )
    {
        $kernel->wkHtmlToPdf = function() use ($kernel,$options) {
            return new WKHtmlToPdf($options['Bin'], @$options['PageSize']);
        };
    }
}

<?php
namespace Phifty;
use RuntimeException;

class Pygmentize
{
    public $bin;

    public $language = 'php';

    public $format = 'html';

    /**
     *
     * @see http://pygments.org/docs/formatters/
     */
    public $options = array(
        // 'linenos' => 1,
        'style' => 'colorful',
        'bg' => 'light',
        // 'hl_lines' => 2
    );

    public function __construct($bin = null)
    {
        if ($bin) {
            $this->bin = $bin;
        } elseif ( $bin = $this->findBin() ) {
            // simple findbin
            $this->bin = $bin;
        }
    }

    public function setOption($name,$value)
    {
        $this->options[ $name ] = $value;
    }

    public function getOption($name)
    {
        if ( isset($this->options[ $name ]) ) {
            return $this->options[ $name ];
        }
    }

    public function getOptionString()
    {
        $pairs = array();
        foreach ($this->options as $n => $v) {
            $pairs[] = "$n=$v";
        }

        return join(',', $pairs);
    }

    public function findBin()
    {
        $paths = explode( PATH_SEPARATOR, getenv('PATH'));
        foreach ($paths as $path) {
            $binPath = $path . DIRECTORY_SEPARATOR . 'pygmentize';
            if ( file_exists($binPath) )

                return $binPath;
        }

        return 'pygmentize';
    }

    public function getBin()
    {
        return $this->bin;
    }

    public function isSupported()
    {
        return $this->bin ? true : false;
    }

    public function runProcess($command,$input = null)
    {
        $desc = array(
            0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
            1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
            2 => array("file", "/dev/null",'a') // stderr is a file to write to
        );
        $pipes = array();
        $process = proc_open(
            $command,
            $desc,
            $pipes
        );
        $output = '';
        if (! is_resource($process)) {
            throw new RuntimeException('Can not initialize pygmentize process');
        }

        // $pipes now looks like this:
        // 0 => writeable handle connected to child stdin
        // 1 => readable handle connected to child stdout
        // Any error output will be appended to /tmp/error-output.txt
        if ($input != null) {
            fwrite($pipes[0], $input);
        }
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        // It is important that you close any pipes before calling
        // proc_close in order to avoid a deadlock
        $return_value = proc_close($process);

        // pygmentize not found.
        if ($return_value === 127) {
            return '<pre>' . htmlentities($input) . '</pre>';
        }
        if ($return_value !== 0) {
            throw new RuntimeException("Pygmentize error, command: $command, return: $return_value");
        }

        return $output;

    }

    public function renderStyle()
    {
        $style = $this->getOption('style');

        return $this->runProcess(sprintf('%s -f %s -S %s' , $this->bin , $this->format, $style ));
    }

    public function renderString($input)
    {
        return $this->runProcess(sprintf('%s -l %s -f %s -O %s',$this->bin, $this->language, $this->format, $this->getOptionString() ), $input );
    }

}

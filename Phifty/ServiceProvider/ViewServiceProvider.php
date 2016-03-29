<?php
namespace Phifty\ServiceProvider;
use Phifty\View\Engine;
use CodeGen\Expr\NewObject;
use Phifty\Kernel;

/**
 * Usage:
 *
 *    $view = kernel()->view;
 */
class ViewFactory
{
    public $templateDirs = array();

    protected $kernel;

    protected $options = array();

    public function __construct($kernel, array $options = array())
    {
        $this->kernel = $kernel;
        $this->options = $options;
    }

    public function __invoke($class = null)
    {
        /* get template engine */
        $engine = Engine::createEngine($this->kernel);
        $viewClass = $class ?: $this->options['Class'];
        $options = [
            'template_dirs' => $this->options['TemplateDirs'],
        ];
        return new $viewClass($engine, $options);
    }
}

class ViewServiceProvider extends BaseServiceProvider
{
    public function getId() { return 'View'; }

    static public function generateNew(Kernel $kernel, array & $options = array())
    {
        if (!isset($options['Class']) ) {
            $options['Class'] = 'Phifty\\View';
        }
        if (!isset($options['Backend']) ) {
            $options['Backend'] = 'twig';
        }
        if (!isset($options['TemplateDirs'])) {
            if (PH_APP_ROOT != PH_ROOT) {
                $options['TemplateDirs'] = [PH_APP_ROOT, PH_ROOT];
            } else {
                $options['TemplateDirs'] = [PH_APP_ROOT];
            }
        } else {
            // Rewrite template directories with realpath
            $dirs = [];
            foreach ($options['TemplateDirs'] as $dir) {
                $dirs[] = realpath($dir);
            }
            $options['TemplateDirs'] = $dirs;
        }
        $class = get_called_class();
        return new NewObject($class,[$options]);
    }

    public function register($kernel, $options = array())
    {
        $kernel->registerFactory('view', new ViewFactory($kernel, $options));
    }
}

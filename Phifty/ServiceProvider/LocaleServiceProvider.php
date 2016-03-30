<?php
namespace Phifty\ServiceProvider;
use Phifty\Locale;
use Phifty\Kernel;
use CodeGen\Expr\NewObject;

class LocaleServiceProvider extends BaseServiceProvider
{

    public function getId() { return 'Locale'; }

    static public function generateNew(Kernel $kernel, array & $options = array())
    {
        if (!isset($options['Default'])) {
            $options['Default'] = 'en';
        }
        if (!isset($options['LocaleDir'])) {
            $options['LocaleDir'] = 'locale';
        }
        if (!isset($options['Domain'])) {
            $options['Domain'] = $kernel->getApplicationID();
        }
        if (!isset($options['Langs'])) {
            $options['Langs'] = [ 'en' => 'English' ];
        }

        $options['LocaleDir'] = $kernel->rootDir . DIRECTORY_SEPARATOR . $options['LocaleDir'];
        $className = get_called_class();
        return new NewObject($className, [$options]);
    }


    public function register($kernel, $options = array())
    {
        $self = $this;
        $kernel->locale = function() use ($kernel, $self, $options) {
            $locale = new Locale($options['Domain'], $options['LocaleDir'], $options['Langs']);
            $locale->setDefaultLanguage($options['Default']);
            // _('en');
            // _('es');
            // _('zh_TW');
            // _('zh_CN');
            $locale->init();
            return $locale;
        };
        // we need service dependency for this.
        // kernel()->twig->env->addGlobal('currentLang', kernel()->locale->current() );
    }
}

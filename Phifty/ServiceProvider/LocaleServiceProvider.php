<?php
namespace Phifty\ServiceProvider;
use Phifty\Locale;
use Phifty\Kernel;

// _('en');
// _('es');
// _('zh_TW');
// _('zh_CN');
class LocaleServiceProvider extends BaseServiceProvider
{

    public function getId() { return 'Locale'; }

    static public function canonicalizeConfig(Kernel $kernel, array $options)
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
        return $options;
    }

    public function register(Kernel $kernel, $options = array())
    {
        $kernel->locale = function() use ($options) {
            $locale = new Locale($options['Domain'], $options['LocaleDir'], $options['Langs']);
            $locale->setDefaultLanguage($options['Default']);
            $locale->init();
            return $locale;
        };
        // we need service dependency for this.
        // kernel()->twig->env->addGlobal('currentLang', kernel()->locale->current() );
    }
}

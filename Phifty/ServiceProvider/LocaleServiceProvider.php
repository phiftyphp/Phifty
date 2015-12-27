<?php
namespace Phifty\ServiceProvider;
use Phifty\Locale;
use Phifty\Kernel;

class LocaleServiceProvider extends BaseServiceProvider
{

    public function getId() { return 'Locale'; }

    static public function isGeneratable(Kernel $kernel, array & $options = array() )
    {
        return !empty($options);
    }

    public function register($kernel, $options = array())
    {
        // call spl autoload, to load `__` locale function,
        // and we need to initialize locale before running the application.
        $self = $this;
        $kernel->locale = function() use ($kernel, $self, $options) {
            $defaultLang = isset($self->config['Default'])   ? $self->config['Default']   : 'en';
            $localeDir   = isset($self->config['LocaleDir']) ? $self->config['LocaleDir'] : 'locale';
            $domain      = isset($self->config['Domain'])    ? $self->config['Domain'] : $kernel->getApplicationID();
            $langs       = isset($self->config['Langs'])     ? $self->config['Langs'] : array('en');

            $locale = new Locale;
            $locale->setDefault($defaultLang);
            $locale->setDomain($domain); # use application id for domain name.
            $locale->setLocaleDir( $kernel->rootDir . DIRECTORY_SEPARATOR . $localeDir);

            // add languages to list
            foreach ( $langs as $localeName) {
                $locale->add( $localeName );
            }

            # _('en');
            # _('zh_TW');
            # _('zh_CN');
            $locale->init();
            return $locale;
        };
        // we need service dependency for this.
        // kernel()->twig->env->addGlobal('currentLang', kernel()->locale->current() );
    }
}

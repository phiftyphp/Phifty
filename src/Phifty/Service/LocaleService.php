<?php
namespace Phifty\Service;
use Phifty\Locale;

class LocaleService
    implements ServiceRegister
{

    public function getId() { return 'Locale'; }

    public function register($kernel, $options = array() )
    {

        // for backward compatibility
        if (! $options) {
            $options = $kernel->config->get('framework','Locale');
            if ( ! $options )

                return;
        }

        // call spl autoload, to load `__` locale function,
        // and we need to initialize locale before running the application.

        $kernel->locale = function() use ($kernel,$options) {
            $defaultLang = isset($options['Default'])   ? $options['Default']   : 'en';
            $localeDir   = isset($options['LocaleDir']) ? $options['LocaleDir'] : 'locale';
            $domain      = isset($options['Domain'])    ? $options['Domain'] : $kernel->getApplicationID();
            $langs       = isset($options['Langs'])     ? $options['Langs'] : array('en');

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

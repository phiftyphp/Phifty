<?php
namespace Phifty\Service;
use Phifty\Locale;

class LocaleService
    implements ServiceInterface
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
        spl_autoload_call('Phifty\Locale');

        $kernel->locale = function() use ($kernel,$options) {
            $textdomain  = $kernel->config->framework->ApplicationID;
            $defaultLang = isset($options['Default'])   ? $options['Default']   : 'en';
            $localeDir   = isset($options['LocaleDir']) ? $options['LocaleDir'] : 'locale';

            if ( ! ( $textdomain && $defaultLang && $localeDir) ) {
                return;
            }

            $locale = new Locale;
            $locale->setDefault( $defaultLang );
            $locale->domain( $textdomain ); # use application id for domain name.
            $locale->localedir( $kernel->rootDir . DIRECTORY_SEPARATOR . $localeDir);

            // add languages to list
            foreach (@$options['Langs'] as $localeName) {
                $locale->add( $localeName );
            }

            # _('en');
            $locale->init();

            return $locale;
        };
        // we need service dependency for this.
        // kernel()->twig->env->addGlobal('currentLang', kernel()->locale->current() );
    }
}

<?php
namespace Phifty\Command;
use CLIFramework\Command;

class ComposerConfigCommand extends Command
{
    public function brief() {
        return 'Build composer config for web application.';
    }

    public function execute()
    {
        $bundles = kernel()->bundles;
        $config = [];
        $kernel = kernel();
        $config['name'] = 'site/' . strtolower($kernel->getApplicationId());
        $config['version'] = '1.0';
        $config['require'] = [];
        foreach( $bundles as $bundle ) {
            if ( $deps = $bundle->getComposerDependency() ) {
                if ( ! is_array($deps) ) {
                    throw new Exception("Returned dependency data is not an array.");
                }
                foreach( $deps as $pkgName => $versionConstraint ) {
                    if ( isset($config['require'][$pkgName]) ) {
                        throw new Exception("$pkgName is already defined.");
                    }
                    $config['require'][$pkgName] = $versionConstraint;
                }
            }
        }

        $config['require-dev'] = [
            "corneltek/phpunit-testmore" => "dev-master",
        ];

        $config['scripts'] = [
            "post-install-cmd" => [ "Phifty\\Installer\\ComposerInstaller::postInstall" ]
        ];
        echo json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}




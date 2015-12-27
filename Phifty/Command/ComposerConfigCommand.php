<?php
namespace Phifty\Command;
use CLIFramework\Command;
use Phifty\ComposerConfigBridge;
use Exception;

class ComposerConfigCommand extends Command
{
    public function brief() {
        return 'Build composer config for web application.';
    }

    public function mergeConfig(& $config, $deps ) {
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

    public function execute()
    {
        $bundles = kernel()->bundles;
        $config = [];
        $kernel = kernel();
        $config['name'] = 'site/' . strtolower($kernel->getApplicationId());
        $config['version'] = '1.0';
        $config['require'] = [];
        foreach( $bundles as $bundle ) {
            if ($bundle instanceof ComposerConfigBridge ) {
                if ( $deps = $bundle->getComposerDependency() ) {
                    $this->mergeConfig($config, $deps);
                }
            }
        }

        foreach ( $kernel->services as $service ) {
            if ($service instanceof ComposerConfigBridge ) {
                if ( $deps = $service->getComposerDependency() ) {
                    $this->mergeConfig($config, $deps);
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




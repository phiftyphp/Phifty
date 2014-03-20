<?php
namespace email;
use GenPHP\Flavor\BaseGenerator;
use Exception;
use Phifty\Inflector;


/**
 * $ phifty new schema StaffBundle Staff name:varchar:30
 */
class Generator extends BaseGenerator
{
    public function brief() { return 'generate email class and template'; }

    public function generate($ns, $name)
    {
        $app = kernel()->app($ns) ?: kernel()->bundle($ns,true);
        if ( ! $app) {
            throw new Exception("$ns application or plugin not found.");
        }

        $emailClassName = $name . 'Email';
        $handle = Inflector::getInstance()->underscore($name);

        $dir = $app->locate();
        $className = $ns . '\\Email\\' . $emailClassName;
        $classDir = $dir . DIRECTORY_SEPARATOR . 'Email';
        $classFile = $classDir . DIRECTORY_SEPARATOR . $emailClassName . '.php';


        if ( ! file_exists($classDir) ) {
            mkdir($classDir, 0755, true);
        }

        if ( file_exists($classFile) ) {
            $this->logger->info("Found existing $classFile, skip");
            return;
        }

        $this->render('Email.php.twig', $classFile ,array(
            'namespace' => $ns,
            'emailClassName' => $emailClassName,
            'emailHandle' => $handle,
        ));

        foreach( kernel()->locale->available() as $locale => $name ) {
            $templateFile = $app->getTemplateDir() . DIRECTORY_SEPARATOR . 'email' . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . $handle . '.html';

            if ( file_exists($classFile) ) {
                $this->logger->info("Found existing $templateFile, skip");
                continue;
            }

            $this->render('EmailTemplate.html.twig', $templateFile ,array(
                'namespace' => $ns,
                'emailClassName' => $emailClassName,
                'emailHandle' => $handle,
            ));
        }

        /*
         */
    }

}

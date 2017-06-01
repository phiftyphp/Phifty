<?php

namespace Phifty\Generator;

use Dotenv\Loader;
use CodeGen\Statement\Statement;
use CodeGen\Statement\AssignStatement;
use CodeGen\Block;
use CodeGen\Expr\MethodCall;
use CodeGen\Expr\FunctionCall;
use CodeGen\Comment;

class EnvLoader extends Loader
{
    protected $customEnvs = [];

    public function setEnvironmentVariable($name, $value = null)
    {
        list($name, $value) = $this->normaliseEnvironmentVariable($name, $value);

        // Don't overwrite existing environment variables if we're immutable
        // Ruby's dotenv does this with `ENV[key] ||= value`.
        if ($this->immutable && $this->getEnvironmentVariable($name) !== null) {
            return;
        }

        $this->customEnvs[$name] = $value;
    }

    public function load()
    {
        $this->ensureFileIsReadable();
        $filePath = $this->filePath;
        $lines = $this->readLinesFromFile($filePath);
        foreach ($lines as $line) {
            if (!$this->isComment($line) && $this->looksLikeSetter($line)) {
                $this->setEnvironmentVariable($line);
            }
        }

        return $this->customEnvs;
    }
}

class EnvGenerator
{
    const SETTER_APACHE = "apache_setenv";

    const SETTER_PUTENV = "putenv";

    const SETTER_ENV = "_ENV";

    const SETTER_SERVER = "_SERVER";

    protected $setters;

    function __construct(array $setters = null)
    {
        if (!$setters) {
            // The default behavior is like phpdotenv
            $setters = [
                self::SETTER_APACHE,
                self::SETTER_PUTENV,
                self::SETTER_ENV,
                self::SETTER_SERVER,
            ];
        }
        $this->setters = $setters;
    }

    public function generate($rootDir, $filename = '.env', $output = '.phpenv')
    {
        $filepath = $rootDir . DIRECTORY_SEPARATOR . $filename;
        $loader = new EnvLoader($filepath, true);
        $envs = $loader->load();

        $block = new Block;
        $block[] = '<?php';
        $block[] = new Comment("This file is @generated. Please see EnvGenerator for more details.");

        foreach ($this->setters as $setter) {
            switch ($setter) {
                case self::SETTER_PUTENV:
                    $block[] = "if (function_exists('putenv')) {";
                    $block[] = new Statement(new FunctionCall($setter, ["$name=$value"]));
                    $block[] = "}";
                    break;
                case self::SETTER_APACHE:
                    $block[] = "if (function_exists('apache_getenv') && function_exists('apache_setenv')) {";
                    foreach ($envs as $name => $value) {
                        $block[] = new Statement(new FunctionCall($setter, [$name, $value]));
                    }
                    $block[] = "}";
                    break;
                case self::SETTER_ENV:
                    foreach ($envs as $name => $value) {
                        $block[] = new AssignStatement("\$_ENV['$name']", $value);
                    }
                    break;
                case self::SETTER_SERVER:
                    foreach ($envs as $name => $value) {
                        $block[] = new AssignStatement("\$_SERVER['$name']", $value);
                    }
                    break;
            }
        }

        return file_put_contents($output, $block->render());
    }
}

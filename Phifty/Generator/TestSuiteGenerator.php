<?php
namespace Phifty\Generator;

use Phifty\Bundle;
use ReflectionObject;

class TestSuiteGenerator
{
    protected $suites = array();

    public function addFromBundle(Bundle $bundle)
    {
        $testDir = $bundle->getTestDir();
        if (is_dir($testDir)) {
            $refl = new ReflectionObject($bundle);
            $className = $refl->getShortName();
            if (strpos($testDir, getcwd() . DIRECTORY_SEPARATOR) === 0) {
                $testDir = substr($testDir, strlen(getcwd()) + 1);
            }
            $this->suites[$className] = $testDir;
            // <directory suffix="Test.php">tests/Phifty</directory>
        }
    }

    public function toXml()
    {
        $lines = [];
        foreach ($this->suites as $className => $testDir) {
            $lines[] = '<testsuite name="' . $className . '">';
            $lines[] = '  <directory suffix="Test.php">' . $testDir . '</directory>';
            $lines[] = '</testsuite>';
        }
        return join("\n", $lines);
    }
}

<?php

namespace Phifty\Generator;

use PHPUnit\Framework\TestCase;

class EnvGeneratorTest extends TestCase
{
    public function testDefaultSetters()
    {
        $generator = new EnvGenerator();
        $ret = $generator->generate("tests/fixture/env_generator", ".env", "tests/fixture/env_generator/phpenv_default.actual");
        $this->assertFileEquals("tests/fixture/env_generator/phpenv_default.expected", "tests/fixture/env_generator/phpenv_default.actual");
        $this->assertNotFalse($ret);
    }

    public function testApacheSetEnv()
    {
        $generator = new EnvGenerator([ EnvGenerator::SETTER_APACHE ]);
        $ret = $generator->generate("tests/fixture/env_generator", ".env", "tests/fixture/env_generator/phpenv_apache.actual");
        $this->assertFileEquals("tests/fixture/env_generator/phpenv_apache.expected", "tests/fixture/env_generator/phpenv_apache.actual");
        $this->assertNotFalse($ret);
    }

    public function testGlobalEnvSetter()
    {
        $generator = new EnvGenerator([ EnvGenerator::SETTER_ENV ]);
        $ret = $generator->generate("tests/fixture/env_generator", ".env", "tests/fixture/env_generator/phpenv_env.actual");
        $this->assertFileEquals("tests/fixture/env_generator/phpenv_env.expected", "tests/fixture/env_generator/phpenv_env.actual");
        $this->assertNotFalse($ret);
    }
}





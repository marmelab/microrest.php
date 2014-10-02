<?php

namespace Marmelab\Silex\Provider\SilrestTest\Config;

class ValidConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testItShouldLaunchExceptionIfFilePathIsNotSetInApp()
    {
        $this->assertEquals('yes', 'no');
    }
}

<?php

namespace Marmelab\Silex\Provider\SilrestTest\Config;

use Marmelab\Silex\Provider\Silrest\Config\ValidConfig;
use Marmelab\Silex\Provider\Silrest\Parser\RamlParser;
use Raml\Parser;
use Marmelab\Silex\Provider\Silrest\RestApiProvider;
use Silex\Application;

require_once __DIR__ . "/../../vendor/autoload.php";

class ValidConfigTest extends \PHPUnit_Framework_TestCase
{

    protected $app;

    public function setUp()
    {
        $this->app = new Application();
        $this->app["debug"] = true;
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testItShouldLaunchExceptionIfConfigFileParamIsNotSetInApp()
    {
        $valideConfig = new ValidConfig($this->app['rest_api.config_file'], new RamlParser());
    }

    /**
     * @expectedException Exception
     */
    public function testItShouldLaunchExceptionIfConfigFileIsReadable()
    {
        $this->app->register(new RestApiProvider(), array(
            'rest_api.config_file' =>__DIR__ . "/../Fixtures/unreadable.raml",
            'rest_api.url_prefixe' => 'api'
        ));

        $valideConfig = new ValidConfig($this->app['rest_api.config_file'], new RamlParser());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testItShouldLaunchExceptionWithParserWithoutSilrestConfigParserInterface()
    {
        $valideConfig = new ValidConfig($this->app['rest_api.config_file'], new Parser());
    }

    /**
     * @expectedException Symfony\Component\Yaml\Exception\ParseException
     */
    public function testItShouldLaunchExceptionIfConfigFileIsIncorrectlyFormatted()
    {
        $this->app->register(new RestApiProvider(), array(
            'rest_api.config_file' =>__DIR__ . "/../Fixtures/wrongFormated.raml",
            'rest_api.url_prefixe' => 'api'
        ));

        $valideConfig = new ValidConfig($this->app['rest_api.config_file'], new RamlParser());
    }

    public function testItShouldReturnValidConfigurationFromRamlFile()
    {
        $this->app->register(new RestApiProvider(), array(
            'rest_api.config_file' =>__DIR__ . "/../Fixtures/songs.raml",
            'rest_api.url_prefixe' => 'api'
        ));

        $valideConfig = new ValidConfig($this->app['rest_api.config_file'], new RamlParser());
        $config = $valideConfig->getConfig();

        $this->assertCount(5, $config);
        $this->assertEquals('World Music API', $config['title']);
    }
}

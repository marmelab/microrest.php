<?php

namespace Marmelab\MicrorestTest;

use Marmelab\Microrest\MicrorestServiceProvider;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class MicrorestServiceProviderTest extends \PHPUnit_Extensions_Database_TestCase
{
    private $connection = null;

    /**
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    public function getConnection()
    {
        $databasePath = __DIR__ . "/Fixtures/api.db";
        $config = new \Doctrine\DBAL\Configuration();
        $connectionParams = array(
            'path' => $databasePath,
            'driver' => 'pdo_sqlite',
        );
        $this->connection = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);

        return $this->createDefaultDBConnection($this->connection->getWrappedConnection(), 'api');
    }

    /**
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet()
    {
        return new \PHPUnit_Extensions_Database_DataSet_YamlDataSet(
            dirname(__FILE__)."/Fixtures/data.yml"
        );
    }

    public function testSimpleRamlApiFile()
    {
        $app = new Application();
        $app['debug'] = true;

        $app->register(new \Silex\Provider\DoctrineServiceProvider(), array(
            'db.options' => array(
                'driver' => 'pdo_sqlite',
                'path' => __DIR__ . "/Fixtures/api.db",
            ),
        ));
        $app->register(new \Silex\Provider\ServiceControllerServiceProvider());
        $app->register(new MicrorestServiceProvider(), array(
            'microrest.config_file' => __DIR__.'/Fixtures/api.rml',
        ));

        $request = Request::create('/api/artists', 'GET');
        $response = $app->handle($request);

        $this->assertEquals('[{"id":"1","name":"Daft Punk","description":"French electronic music duo consisting of musicians Guy-Manuel de Homem-Christo and Thomas Bangalter","image_url":"http:\/\/travelhymns.com\/wp-content\/uploads\/2013\/06\/random-access-memories1.jpg","nationality":"France"},{"id":"2","name":"Pink Floyd","description":"English rock band that achieved international acclaim with their progressive and psychedelic music.","image_url":"http:\/\/www.billboard.com\/files\/styles\/promo_650\/public\/stylus\/1251869-pink-floyd-reunions-617-409.jpg","nationality":"England"},{"id":"3","name":"Radiohead","description":"English rock band from Abingdon, Oxfordshire, formed in 1985","image_url":"http:\/\/www.billboard.com\/files\/styles\/promo_650\/public\/stylus\/1251869-pink-floyd-reunions-617-409.jpg","nationality":"England"}]', $response->getContent());
    }
}

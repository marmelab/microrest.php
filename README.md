#Silrest

Silrest is a Silex provider to setting up a rest api from a configuration file size raml.

## What is Raml
[RESTful API Modeling Language (RAML)](http://raml.org/) is a simple and succinct way of describing practically-RESTful APIs. It encourages reuse, enables discovery and pattern-sharing, and aims for merit-based emergence of best practices.    
You should easely set up a raml file from [API Designer](http://api-portal.anypoint.mulesoft.com/raml/api-designer).     

## Setup Silrest
Add repositories to your silex project composer.json :

    {
        "name": "your project",
        "repositories": [
            {
                "type": "vcs",
                "url":  "git@bitbucket.org:alexisjanvier/silrest.git"
            }
        ],
        "require": {
            "php": ">=5.4",
            "silex/silex": "~1.2",
            "marmlelab/silrest": "dev-master"
        }
    }

Then, register Doctrine and Silrest in your application ;

    $app->register(new Silex\Provider\ServiceControllerServiceProvider());
    $app->register(new Silex\Provider\DoctrineServiceProvider(), array(
        'db.options' => array(
            'driver'   => 'pdo_sqlite',
            'path'     => __DIR__.'/app.db',
        ),
    ));
    $app->register(new Marmelab\Silrest\RestApiProvider(), array(
        'rest_api.config_file' => __DIR__ . '/api.raml',
        'rest_api.url_prefixe' => 'api',
    ));

For the moment Silrest works only with sqlite, so you must create a sqlite db file somewhere.   
You also need give the path to the raml file describing your api. You sould find an exemple in tests/fictures folder.

Then, you should browse your new api rest on your domain/api.url_prefixe set on Silex registration (the second parameter).

All your object must be set in a 'content' entry from a json file on post and put request. 

## Tests

    make test

## Todo
It's only a draft for the moment. There are some horror in the code with dbal doctrine, correct emergency.    
Then, it will :

* handle error codes
* make the code independent of the storage
* parse raml file as ramlLoader for routing, like in Symfony style
* Use object description from raml to set validation from request
* ...


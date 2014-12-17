# Marmelab Microrest

Marmelab Microrest is a Silex provider to setting up a REST API from a RAML configuration file.

## What is RAML ?

[RESTful API Modeling Language (RAML)](http://raml.org/) is a simple and succinct way of describing practically-RESTful APIs. It encourages reuse, enables discovery and pattern-sharing, and aims for merit-based emergence of best practices.    
You should easely set up a RAML file from [API Designer](http://api-portal.anypoint.mulesoft.com/raml/api-designer).     

## Installation

Add repositories to your silex project `composer.json`:

    {
        "name": "your project",
        "repositories": [
            {
                "type": "vcs",
                "url":  "git@github.com:marmelab/microrest.php.git"
            }
        ],
        "require": {
            "php": ">=5.4",
            "silex/silex": "~1.2",
            "marmlelab/microrest": "~1.0@dev"
        }
    }

Then, register Doctrine and Microrest in your application:

    $app->register(new Silex\Provider\ServiceControllerServiceProvider());
    $app->register(new Silex\Provider\DoctrineServiceProvider(), array(
        'db.options' => array(
            'driver'   => 'pdo_sqlite',
            'path'     => __DIR__.'/app.db',
        ),
    ));
    $app->register(new Marmelab\Microrest\MicrorestServiceProvider(), array(
        'microrest.config_file' => __DIR__ . '/api.raml',
        'microrest.url_prefix' => 'api', // This is the default value
    ));
  
You need to give the path to the `raml` file describing your API. You can find an example into the `tests/fixtures` directory.

Then, you should browse your new API REST on your `domain/api.url_prefix` previously set the Silex provider registration.

## Tests

Run the tests suite with the following commands:

    make install
    make test

## Demo

You can find a complete demo application in `examples/ng-admin`.

It's just 2 commands for setup and run:

    make install-demo
    make run-demo

Play with the Silex demo API at the url: `http://localhost:8888/api`

Explore the API using [ng-admin](https://github.com/marmelab/ng-admin) backend administration at the url: `http://localhost:8888/admin`

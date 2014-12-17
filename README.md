# Marmelab Microrest

Marmelab Microrest is a Silex provider to setting up a REST API from a RAML configuration file.

## What is RAML ?

[RESTful API Modeling Language (RAML)](http://raml.org/) is a simple and succinct way of describing practically-RESTful APIs. It encourages reuse, enables discovery and pattern-sharing, and aims for merit-based emergence of best practices.    
You should easely set up a RAML file from [API Designer](http://api-portal.anypoint.mulesoft.com/raml/api-designer).     

## Installation

To install this library, run the command below and you will get the latest version:

    composer require marmelab/microrest "~1.0@dev"

And enable `ServiceController`, `Doctrine` and `Microrest` service provider in your application:

    $app->register(new Silex\Provider\ServiceControllerServiceProvider());
    $app->register(new Silex\Provider\DoctrineServiceProvider(), array(
        'db.options' => array(
            'driver'   => 'pdo_sqlite',
            'path'     => __DIR__.'/app.db',
        ),
    ));
    $app->register(new Marmelab\Microrest\MicrorestServiceProvider(), array(
        'microrest.config_file' => __DIR__ . '/api.raml',
    ));
  
You need to give the path to the `RAML` file describing your API. You can find an example into the `tests/fixtures` directory.

Then, you should browse your new API REST on the url defined in the `baseUrl` configuration of your `RAML` api file.

## Tests

Run the tests suite with the following commands:

    make install
    make test

## Demo

You can find a complete demo application in `examples/ng-admin`.

It's just 2 commands for installation and run:

    make install-demo
    make run-demo

Play with the Silex demo API at the url: `http://localhost:8888/api`

Explore the API using [ng-admin](https://github.com/marmelab/ng-admin) backend administration at the url: `http://localhost:8888/admin`

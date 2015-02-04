# Marmelab Microrest

Microrest is a Silex provider to setting up a REST API on top of a relational database, based on a YAML (RAML) configuration file.

Check out the [launch post](http://marmelab.com/blog/2015/01/05/introducing-microrest-raml-api-in-silex.html).

## What is RAML ?

[RESTful API Modeling Language (RAML)](http://raml.org/) is a simple and succinct way of describing practically-RESTful APIs. It encourages reuse, enables discovery and pattern-sharing, and aims for merit-based emergence of best practices.   

You can easily set up a RAML file from [API Designer](http://api-portal.anypoint.mulesoft.com/raml/api-designer).     

## Installation

To install microrest.php library, run the command below and you will get the latest version:

```bash
composer require marmelab/microrest "~1.0@dev"
```

Enable `ServiceController`, `Doctrine` and `Microrest` service providers in your application:

```php
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
```
  
You need to give the path to the `RAML` file describing your API. You can find an example into the `tests/fixtures` directory.

Then, browse your new API REST on the url defined in the `baseUrl` configuration of your `RAML` api file.

## Using request parameters

You can specify some get request parameters. For example:

Request param | Description | Type / Example | Default
--------------|--------------|--------------|--------------
`_start` | specify start bound of selection | `number` | `0`
`_end` | specify **length** of selection | `number` |`20` 
`_sort` | specify key ordering | `string` |
`_sortDir` | specify order direction | `ASC`, `DESC` | `ASC`
`_fields` | specify comma separated set of fields in result set | `string` | `*`
`_strongFilter[]` | specify conjunction filter like a ````id` = 8 AND `post_id` = 2``` as request params array | `array` |
`_strongFilterIn[]` | specify `IN` condition like a ````id` IN (1,2,3)``` | array | 
`_searchOr[]` | specify search disjunction filter like a ````title` LIKE '%foo%' OR `post` LIKE '%bar%'``` | `array` |
`_searchAnd[]` | specify search conjunction filter like a ````title` LIKE '%foo%' AND `post` LIKE '%bar%'``` | `array` |
`_group` | set group part | `string` |

You can combine one of `_strongFilter[]`, `_strongFilterIn[]`, `_searchOr[]`, `_searchAnd[]` with `_sort`, `_sortDir`, `_fields`, `_start` and `_end` params

#### Warning!
You should use **only** one filer from 
`_strongFilter[]`, `_strongFilterIn[]`, `_searchOr[]`, `_searchAnd[]` or you will get an HTTP error `400 Bad request`.

### Query string examples

Query string | Description
-------------|------------
`/posts?_start=10&_end=15` | you will receive a 15 posts from 10 position as result set
`/posts?_sort=title&_sortDir=DESC` | you will receive a list sorted by `title` descending
`/posts?_fields=id,title` | you will receive a list with `id` and `title` field in response
`/posts?_strongFilter[id]=8&_strongFilter[title]=foo` | you will receive a list of items where ````id` = 8 AND `title` = 'foo'```
`/posts?_strongFilterIn[id]=1,2,3` | you will receive a list of items with `id` in list: `1`, `2`, `3`
`/posts?_searchOr[title]=foo&_searchOr[body]=bar` | you will receive a list of items where ````title` LIKE '%foo%' OR `body` LIKE '%bar%'```
`/posts?_searchAnd[title]=foo&_searchAnd[body]=bar` | you will receive a list of items where ````title` LIKE '%foo%' AND `body` LIKE '%bar%'```
`/posts?_group=title` | should use for request distinct values of column instead
`/posts?_searchAnd[title]=foo&_searchOr[body]=bar` | you will receive HTTP error `400 Bad request`

## Tests

Run the tests suite with the following commands:

```bash
make install
make test
```

## Demo

You can find a complete demo application in `examples/ng-admin`. You just need 2 commands to install and run it:

```bash
make install-demo
make run-demo
```

Play with the Silex demo API at the url: `http://localhost:8888/api`

Explore the API using [ng-admin](https://github.com/marmelab/ng-admin) backend administration at the url: `http://localhost:8888/admin`

## License

microrest.php is licensed under the [MIT License](LICENSE), courtesy of [marmelab](http://marmelab.com).

<?php
/**
 * Simple port of Raml Parser to make it implement SilrestConfigParser
 */

namespace Marmelab\Silex\Provider\Silrest\Parser;

use Marmelab\Silex\Provider\Silrest\Parser\SilrestConfigParser;
use Raml\Parser;

class RamlParser implements SilrestConfigParser
{
    private $ramlParser;

    public function __construct()
    {
        $this->ramlParser = new Parser();
    }

    public function parse($fileName)
    {
        return $this->ramlParser->parse($fileName);
    }
}

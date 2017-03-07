<?php

namespace Webby\Model;

use Nette\Utils\Finder;
use UniMapper\Adapter\IQuery;
use Webby\Model\Adapter\Query;
use Webby\System;

class Adapter extends \UniMapper\Adapter
{

    private $contentDir;

    public function __construct($contentDir)
    {
        $this->contentDir = $contentDir;
    }

    public function createDelete($resource) {}

    public function createDeleteOne($resource, $column, $primaryValue ) {}

    public function createSelectOne($resource, $column, $primaryValue )
    {
        return new Query(function () use ($resource, $primaryValue) {

            if ($resource === "pages") {
                return yaml_parse_file($this->contentDir . "/" . $resource . "/" . System::linkToPath($primaryValue) . ".yml") + ["link" => $primaryValue];
            } elseif ($resource === "themes") {
                return yaml_parse_file($this->contentDir . "/" . $resource . "/" . $primaryValue . ".yml") + ["name" => $primaryValue];
            }
        });
    }

    public function createSelect($resource, array $selection = [], array $orderBy = [], $limit = 0, $offset = 0 )
    {
        return new Query($cb = function ($relativeDir = null) use ($resource, &$cb) {

            $result = [];

            foreach (Finder::findFiles("*.yml")->in($this->contentDir . "/" . $resource . "/" . $relativeDir) as $file) {

                $name = $file->getBasename(".yml");
                $config = yaml_parse_file($file->getPathname());

                if ($resource === "pages") {

                    $config["link"] = System::pathToLink($relativeDir . "/" . $name);
                    $result[] = $config;
                    if (is_dir($this->contentDir . "/" . $resource . "/" . $relativeDir . "/" . $name)) {
                        $result = array_merge($result, $cb($relativeDir . "/" . $name));
                    }
                } elseif ($resource === "themes") {

                    $config["name"] = $name;
                    $result[] = $config;
                }
            }
            return $result;
        });
    }

    public function createCount($resource ) {}

    public function createInsert($resource, array $values, $primaryName = null ) {}

    public function createUpdate($resource, array $values ) {}

    public function createUpdateOne($resource, $column, $primaryValue, array $values ) {}

    public function createManyToManyAdd($sourceResource, $joinResource, $targetResource, $joinKey, $referencingKey, $primaryValue, array $keys ) {}

    public function createManyToManyRemove($sourceResource, $joinResource, $targetResource, $joinKey, $referencingKey, $primaryValue, array $keys ) {}

    public function onExecute(IQuery $query)
    {
        return $query->getRaw();
    }

}
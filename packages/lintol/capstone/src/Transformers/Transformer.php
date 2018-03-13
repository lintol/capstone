<?php

namespace Lintol\Capstone\Transformers;

use Illuminate\Support\Collection;
use Illuminate\Contracts\Validation\Factory;
use WoohooLabs\Yang\JsonApi\Serializer\JsonDeserializer;
use League\Fractal;
use WoohooLabs\Yang\JsonApi\Schema\Document;
use GuzzleHttp\Psr7\Response;
use App;

class Transformer extends Fractal\TransformerAbstract
{
    private static $typeToModelClass = [
        'profiles' => \Lintol\Capstone\Models\Profile::class,
        'processorConfigurations' => \Lintol\Capstone\Models\ProcessorConfiguration::class
    ];

    private static $typeToTransformerClass = [
        'profiles' => ProfileTransformer::class,
        'processorConfigurations' => ProcessorConfigurationTransformer::class
    ];

    public static function deserialize($jsonApi)
    {
        $document = Document::createFromArray((array)$jsonApi);

        return $document;
    }

    public static function sanitize($data, $filters)
    {
        $factory = App::make('sanitizer');

        $sanitizer = $factory->make($data, $filters);

        return $sanitizer->sanitize();
    }

    /**
     * Starting from a Yang resource, sanitize, validate and fill a new model.
     */
    public function parseResource($resource, $model = null)
    {
        if (!$model) {
            $model = app(self::$typeToModelClass[$resource->type()]);
        }

        $attributes = $this->mapInput($resource->attributes());

        $attributes = $this->sanitize($attributes, $model->filters());

        $relationships = $this->sideMapping();

        collect($resource->relationships())->each(function ($relation) use ($model, &$relationships) {
            if (array_key_exists($relation->name(), $relationships)) {
                $relationName = $relationships[$relation->name()];

                if ($relation->isToOneRelationship()) {
                    throw new Exception("Not yet implemented");
                } else {
                    while (!$model->{$relationName}->isEmpty()) {
                        $resource = $model->{$relationName}->pop();
                        $resource->delete();
                    }

                    array_map(function ($resource) use ($model, $relationName) {
                        $resource = app(self::$typeToTransformerClass[$resource->type()])->parseResource($resource);
                        $model->{$relationName}->add($resource);
                    }, $relation->resources());
                }
            }
        });

        $validator = app(Factory::class)->make($attributes, $model->rules());

        if ($validator->fails()) {
            throw new Exception("Invalid attribute data in model " . self::$class);
        }

        $model->fill($attributes);

        return $model;
    }

    /**
     * Starting from JSON-API, sanitize, validate and fill a new model.
     */
    public function parse($jsonApi, $model = null)
    {
        $document = self::deserialize($jsonApi);

        if ($document) {
            $resource = $document->primaryResource();

            if ($resource) {
                if ($resource->type() !== static::$model) {
                    throw new Exception("Wrong type of object sent");
                }
                return $this->parseResource($resource, $model);
            }
        }

        throw new Exception("Could not deserialize JSON-API");
    }

    /**
     * Below from flaxandteal/bedappy-controllers [MIT]
     */

    /**
     * Function to map input to a more useful array
     *
     * @param array
     * @return array
     */
    public static function mapInput(array $data)
    {
        $mapping = static::inputMapping();

        $filteredData = [];
        foreach ($mapping as $field => $key) {
            if (array_key_exists($field, $data)) {
                $entry = $data[$field];
                if (is_callable($key)) {
                    $entries = $key($entry);
                } else {
                    $entries = [$key => $entry];
                }

                if ($entries) {
                    $filteredData = array_merge($filteredData, $entries);
                }
            }
        }

        return $filteredData;
    }

    public static function sideMapping()
    {
        return [];
    }

    /**
     * Mapping array for this transformer
     *
     * @return array
     */
    public static function inputMapping()
    {
        return [];
    }
}

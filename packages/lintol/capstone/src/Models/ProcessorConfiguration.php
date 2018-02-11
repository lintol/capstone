<?php

namespace Lintol\Capstone\Models;

use Illuminate\Database\Eloquent\Model;
use Alsofronie\Uuid\UuidModelTrait;
use App\User;

class ProcessorConfiguration extends Model
{
    use UuidModelTrait;

    protected $casts = [
        'definition' => 'json',
        'configuration' => 'json',
        'rules' => 'json'
    ];

    protected $fillable = [
         'user_configuration_storage',
         'processor_id',
         'configuration',
         'definition',
         'rules'
    ];

    public function filters()
    {
        return [];
    }

    public function rules()
    {
        return [];
    }

    public function processor()
    {
        return $this->belongsTo(Processor::class);
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function buildDefinition()
    {
        $this->configuration = json_decode($this->user_configuration_storage);

        $supplementary = [];
        foreach ($this->configuration as $key => $value) {
          if (strlen($value) > 3 && substr($value, 0, 3) == '$->') {
            $value = substr($value, 3);

            if ($this->processor) {
              if (array_key_exists($value, $this->processor->supplementary_links)) {
                $supplementary[$value] = $this->processor->supplementary_links[$value];
              } else {
                $supplementary[$value] = 'error://link-not-found';
              }
            } else {
              $supplementary[$value] = 'error://processor-details-missing';
            }
          }
        }

        $module = null;
        if ($this->processor && $this->processor->module) {
          $module = $this->processor->module;
        }

        return [
            'configuration' => $this->configuration,
            'definition' => $this->definition,
            'supplementary' => $supplementary,
            'module' => $module
        ];
    }
}

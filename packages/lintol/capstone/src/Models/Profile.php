<?php

namespace Lintol\Capstone\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Alsofronie\Uuid\UuidModelTrait;
use Lintol\Capstone\Services\RulesService;

class Profile extends Model
{
    use UuidModelTrait;

    protected $fillable = [
        'name',
        'description',
        'version',
        'unique_tag',
        'rules'
    ];

    protected $casts = [
        'rules' => 'json'
    ];

    public function configurations()
    {
        return $this->hasMany(ProcessorConfiguration::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class);
    }

    public function runs()
    {
        return $this->hasMany(ValidationRun::class);
    }

    public function match($definition)
    {
        // TODO: make a singleton
        $this->rulesService = app()->make(RulesService::class);
        $rules = Profile::select(['id', 'rules'])->get();
        $profiles = $this->rulesService->match($definition, $rules);

        return Profile::whereIn(
            'id',
            $profiles
        )->get();
    }

    public function configurationsByRules($definition)
    {
        $this->rulesService = app()->make(RulesService::class);

        return $this->configurations->filter(function ($configuration) use ($definition) {
            return $this->rulesService->filter($definition, $configuration->rules);
        });
    }

    public function buildDefinitions($definition)
    {
        return $this->configurationsByRules($definition)
            ->keyBy('id')
            ->map(function ($configuration) {
                return $configuration->buildDefinition();
            })
            ->toArray();
    }
}

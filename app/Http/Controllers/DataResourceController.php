<?php

namespace App\Http\Controllers;

use Auth;
use Log;
use GuzzleHttp;
use App\User;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use Lintol\Capstone\Models\DataResource;
use Illuminate\Http\Request;
use Lintol\Capstone\Transformers\DataResourceTransformer;
use Lintol\Capstone\ResourceManager;

class DataResourceController extends Controller
{
    protected $validFilters = ['filetype', 'user_id', 'created_at'];
    protected $validSortBy = ['filetype', 'filename', 'created_at', 'status', 'user_id'];

    public function __construct(DataResourceTransformer $transformer, ResourceManager $resourceManager)
    {
        $this->transformer = $transformer;
        $this->resourceManager = $resourceManager;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $search = request()->input('search');
        $filters = request()->input('filters');
        $sortBy = request()->input('sortBy');
        $orderDesc = (request()->input('order') == 'desc');

        if ($filters) {
            $filters = collect(explode(',', $filters))
              ->map(function ($filterString) {
                $filter = explode(':', $filterString);

                if (count($filter) !== 2 || !in_array($filter[0], $this->validFilters) || !$filter[1]) {
                    return null;
                }

                return [
                  'filter' => $filter[0],
                  'value' => $filter[1]
                ];
              })
              ->filter()
              ->pluck('value', 'filter');
        } else {
            $filters = [];
        }

        $maxPagination = config('capstone.frontend.max-pagination', 250);

        $count = (int) request()->input('count');
        if (!$count || $count > $maxPagination) {
            $count = $maxPagination;
        }

        if (!in_array($sortBy, $this->validSortBy)) {
          $sortBy = 'filename';
        }

        switch (request()->input('provider')) {
            case '_remote':
                // Add windowing/pagination to call

                $resourceProvider = $this->resourceManager->getProvider();

                $data = collect();
                if ($resourceProvider) {
                    $data = $resourceProvider->getDataResources();
                }

                $paginator = new LengthAwarePaginator($data, $data->count(), $count);
                break;
            case '_local':
            default:
                $query = new DataResource;
                if ($search) {
                    $query = $query->where('filename', 'LIKE', '%' . $search . '%');
                }
                foreach ($filters as $filter => $value) {
                    if ($filter == 'created_at') {
                        $query = $query->whereDate('created_at', '=', $value);
                    } else {
                        $query = $query->where($filter, '=', $value);
                    }
                }

                $query = $query->orderBy($sortBy, $orderDesc ? 'desc' : 'asc');
                $paginator = $query->paginate($count);
        }

        $data = $paginator->getCollection();
        $paginator->setPath('/dataResources/');

        $users = User::whereIn('id', $data->pluck('user_id')->filter())->get()->each(function (&$user) {
          $user->retrieve();
        })->keyBy('id');

        $data->each(function (&$data) use ($users) {
            if ($data->user_id) {
                $data->user = $users[$data->user_id];
            }
        });

        return fractal()
            ->collection($data, $this->transformer, 'dataResources')
            ->paginateWith(new IlluminatePaginatorAdapter($paginator))
            ->respond();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $url = $request->input('url');
        $user = Auth::user();
        $dataResource = $this->resourceManager->find($url, $user);

        if (!$dataResource) {
            $dataResource = app()->make(DataResource::class);
        }

        $dataResource->settings = [
          'name' => basename($url),
          'dataProfileId' => $request->input('profileId')
        ];

        $dataResource->source = $request->input('stored');
        $dataResource->url = $request->input('url');

        if ($user) {
            $dataResource->user_id = $user->id;
        }

        $dataResource->filetype = $request->input('filetype');

        $dataResource = $this->resourceManager->onboard($dataResource);

        if ($dataResource) {
            return fractal()
                ->item($dataResource, $this->transformer, 'dataResources')
                ->respond();
        }
        abort(400, __("Invalid data"));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\DataResource  $dataResource
     * @return \Illuminate\Http\Response
     */
    public function show(DataResource $dataResource)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\DataResource  $dataResource
     * @return \Illuminate\Http\Response
     */
    public function edit(DataResource $dataResource)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\DataResource  $dataResource
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DataResource $dataResource)
    {
        //
        $dataResource2 = DataResource::findOrFail($dataResource->id);
        $dataResource2->archived = $dataResource->archived;
        $dataResource2->save();
     
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\DataResource  $dataResource
     * @return \Illuminate\Http\Response
     */
    public function destroy(DataResource $dataResource)
    {
        //
        $resource = DataResource::findOrFail($dataResource->id);
        $resource->delete();
        
    }
}

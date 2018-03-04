<?php

namespace App\Http\Controllers;

use Auth;
use DB;
use App\User;
use Illuminate\Http\Request;
use App\Transformers\UserTransformer;
use Lintol\Capstone\CkanResourceProvider;
use Lintol\Capstone\ResourceManager;

class UserController extends Controller
{
    /**
     * Initialize the transformer
     */
    public function __construct(UserTransformer $transformer, ResourceManager $resourceManager)
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
        $users = User::whereNull('primary_remote_user_id')->get();

        $resourceProvider = $this->resourceManager->getProvider();

        if ($resourceProvider) {
            $moreUsers = $resourceProvider->getUsers();
            $users = $users->merge($moreUsers);
        }

        return fractal()
            ->collection($users, $this->transformer, 'users')
            ->respond();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // $this->authorize('create', User::class);

        $input = $request->json()->all();

        $user = $this->transformer->parse($input);
        $user->configurations->each(function ($configuration) {
          $configuration->updateDefinition();
        });

        DB::beginTransaction();

        try {
            if (!$user->save()) {
                abort(400, "Invalid user data");
            }

            if (!$user->configurations()->saveMany($user->configurations)) {
                abort(400, "Invalid configuration data");
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollback();
            throw $e;
        }

        return fractal()
            ->item($user, $this->transformer, 'users')
            ->respond();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::findOrFail($id);

        return fractal()
            ->item($user, $this->transformer, 'users')
            ->respond();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $input = $request->json()->all();

        $user = $this->transformer->parse($input, $user);

        if ($user->push()) {
            return fractal()
                ->item($user, $this->transformer, 'users')
                ->respond();
        }

        abort(400, __("Invalid data"));
    }

    /**
     * Get details of the currently logged in user
     * (involves a call to the OAuth provider)
     *
     * @return \Illuminate\Http\Response
     */
    public function me(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            abort(401, __("No logged in user"));
        }

        $user->retrieve();

        return fractal()
            ->item($user, $this->transformer, 'users')
            ->respond();
    }
}

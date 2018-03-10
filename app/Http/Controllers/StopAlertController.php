<?php

namespace App\Http\Controllers;

use App\Http\Resources\StopAlertResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Infrastructure\Services\StopAlertService as StopAlerts;

class StopAlertController extends Controller
{
    protected $stopAlerts;

    /**
     * StopAlertController constructor.
     * @param StopAlerts $stopAlerts
     */
    public function __construct(StopAlerts $stopAlerts)
    {
        $this->stopAlerts = $stopAlerts;
    }

    /**
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    function index() {
        $stopAlerts = $this->stopAlerts->forUser(Auth::user()->id);
        return StopAlertResource::collection($stopAlerts);
    }

    /**
     * @param Request $request
     * @return StopAlertResource
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'symbol' => 'required',
        ]);

        $stopAlert = $this->stopAlerts->create($request->merge(['user_id' => Auth::user()->id])->all());

        return response(new StopAlertResource($stopAlert), 201);
    }

    /**
     * @param $stopAlertId
     * @return StopAlertResource
     */
    public function show($stopAlertId) {
        return new StopAlertResource($this->stopAlerts->byIdOrFail($stopAlertId));
    }

    /**
     * @param Request $request
     * @param $stopAlertId
     * @return StopAlertResource
     */
    public function update(Request $request, $stopAlertId) {
        $this->validate($request, []);

        $stopAlert = $this->stopAlerts->update($stopAlertId, $request->all());

        return new StopAlertResource($stopAlert);
    }

    /**
     * @param $stopAlertId
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function destroy($stopAlertId) {
        $this->stopAlerts->destroy($stopAlertId);
        return response(null, 204);
    }


}

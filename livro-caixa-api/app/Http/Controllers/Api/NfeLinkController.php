<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NfeLinkResource;
use App\Models\NfeLink;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class NfeLinkController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return NfeLinkResource::collection(NfeLink::query()->orderBy('id', 'desc')->get());
    }

    public function store(Request $request): NfeLinkResource
    {
        $data = $request->validate([
            'url' => ['required', 'string', 'max:250', 'url'],
        ]);

        return new NfeLinkResource(NfeLink::query()->create($data));
    }

    public function show(NfeLink $nfeLink): NfeLinkResource
    {
        return new NfeLinkResource($nfeLink);
    }

    public function update(Request $request, NfeLink $nfeLink): NfeLinkResource
    {
        $data = $request->validate([
            'url' => ['required', 'string', 'max:250', 'url'],
        ]);

        $nfeLink->update($data);

        return new NfeLinkResource($nfeLink);
    }

    public function destroy(NfeLink $nfeLink): Response
    {
        $nfeLink->delete();

        return response()->noContent();
    }
}

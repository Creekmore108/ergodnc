<?php

namespace App\Http\Controllers;

use App\Http\Resources\OfficeResource;
use App\Models\Office;
use Illuminate\Http\Request;
// use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;


class OfficeController extends Controller
{

    public function index(): AnonymousResourceCollection
    {
        $offices = Office::query()
                ->latest('id')
                ->get();

        return OfficeResource::collection(
            $offices
        );

    }
}

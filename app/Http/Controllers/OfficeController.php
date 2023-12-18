<?php

namespace App\Http\Controllers;

use App\Http\Resources\OfficeResource;
use App\Models\Office;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Validators\OfficeValidator;
use App\Notifications\OfficePendingApproval;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;


class OfficeController extends Controller
{

    public function index(): AnonymousResourceCollection
    {
        $offices = Office::query()
                 ->when(request('user_id'),
                    fn($builder) => $builder,
                    fn($builder) => $builder->where('approval_status', Office::APPROVAL_APPROVED)->where('hidden', false)
                    )
                ->when(request('user_id'), fn($builder) => $builder->whereUserId(request('user_id')))
                // ->where('approval_status', Office::APPROVAL_APPROVED)
                // ->where('hidden', false)
                // ->latest('id')
                ->when(
                    request('lat') && request('lng'),
                    fn($builder) => $builder->nearestTo(request('lat'), request('lng')),
                    fn($builder) => $builder->orderBy('id', 'ASC')
                )
                ->with(['images','tags','user'])
                ->withCount(['reservations' => fn($builder) => $builder->where('status', Reservation::STATUS_ACTIVE)])
                ->paginate(10);

        return OfficeResource::collection(
            $offices
        );

    }

    public function show(Office $office): JsonResource
    {
        $office->loadCount(['reservations' => fn($builder) => $builder->where('status', Reservation::STATUS_ACTIVE)])
            ->load(['images', 'tags', 'user']);

        return OfficeResource::make($office);
    }
}

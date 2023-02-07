<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CitizenController extends Controller
{
    public function listPage(Request $request)
    {
        $citizens = User::query()
            ->myCitizenAuthority()
            ->orderByDesc('created_at')
            ->paginate(10)
            ->appends(request()->query());

        return Inertia::render(
            'Citizen/List',
            compact('citizens'),
        );
    }
}

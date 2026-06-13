<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SimulasiController extends Controller
{
    public function login(Request $request): RedirectResponse
    {
        $name    = $request->input('name');
        $account = collect(Meeting::$accounts)->firstWhere('name', $name);

        if ($account) {
            session(['sim_user' => $account]);
        }

        return redirect()->back();
    }
}

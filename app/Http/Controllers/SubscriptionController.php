<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class SubscriptionController extends Controller
{
    public function create()
    {
        return Inertia::render("Auth/ChoosePlan");
    }
}

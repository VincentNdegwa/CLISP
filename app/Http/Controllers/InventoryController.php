<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoryController extends Controller
{
    public function view()
    {
        return Inertia::render('Inventory/Main');
    }
}

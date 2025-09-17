<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProduktSkany;
use App\Models\Product;
use App\Models\User;
use App\Models\Region;

class ProduktSkanyController extends Controller
{
    // List all scans
    public function index()
    {
        $produktSkany = ProduktSkany::with(['product', 'user', 'region'])
                        ->orderBy('scanned_at', 'desc')
                        ->get();

        return view('products.index', compact('produktSkany'));
    }

    // Show form to create new scan
    public function create()
    {
        $products = Product::all();
        $users = User::all();
        $regions = Region::all();

        return view('products.create', compact('products', 'users', 'regions'));
    }

    // Store new scan
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'user_id' => 'required|exists:users,id',
            'region_id' => 'required|exists:regions,id',
            'quantity' => 'required|integer|min:1',
            'barcode' => 'nullable|string|max:13',
        ]);

        ProduktSkany::create([
            'product_id' => $request->product_id,
            'user_id' => $request->user_id,
            'region_id' => $request->region_id,
            'quantity' => $request->quantity,
            'barcode' => $request->barcode,
            'scanned_at' => now(),
        ]);

        return redirect()->route('products.index')->with('success', 'Skan zapisany!');
    }

    // Optional: view a single scan
    public function show(ProduktSkany $produktSkany)
    {
        return view('products.show', compact('produktSkany'));
    }
}

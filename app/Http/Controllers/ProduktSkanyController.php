<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProduktSkany;
use App\Models\Product;
use App\Models\User;
use App\Models\Region;

class ProduktSkanyController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('perPage', 25); 
        $produktSkany = ProduktSkany::with(['product', 'user', 'region'])
            ->orderBy('scanned_at', 'desc')
            ->paginate($perPage)
            ->withQueryString(); 

        return view('products.index', compact('produktSkany', 'perPage'));
    }


    public function create()
    {
        $products = Product::all();
        $users = User::all();
        $regions = Region::all();

        return view('products.create', compact('products', 'users', 'regions'));
    }

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

        return redirect()->route('produkt_skany.index')->with('success', 'Skan zapisany!');
    }

    // Edycja ilości
    public function edit(ProduktSkany $produktSkany)
    {
        return view('products.edit', compact('produktSkany'));
    }

    public function update(Request $request, ProduktSkany $produktSkany)
{
    $request->validate([
        'quantity' => 'required|integer|min:1',
    ]);

    $produktSkany->update([
        'quantity' => $request->quantity,
    ]);

    if ($request->wantsJson()) {
        return response()->json([
            'success' => true,
            'message' => 'Ilość zaktualizowana!',
            'quantity' => $produktSkany->quantity
        ]);
    }

    return redirect()->route('produkt_skany.index')->with('success', 'Ilość zaktualizowana!');
}


    public function destroy(Request $request, ProduktSkany $produktSkany)
    {
        $produktSkany->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Skan usunięty!'
            ]);
        }

        return redirect()->route('produkt_skany.index')->with('success', 'Skan usunięty!');
    }

}

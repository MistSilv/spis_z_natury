<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProduktSkany;
use App\Models\Product;
use App\Models\User;
use App\Models\Region;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'user_id' => 'required|exists:users,id',
            'region_id' => 'required|exists:regions,id',
            'quantity'   => 'required|numeric|min:0.01',
            'barcode' => 'nullable|string|max:13',
        ]);

        $barcode = $request->barcode ?? DB::table('barcodes')
            ->where('product_id', $request->product_id)
            ->value('barcode');

        $skan = ProduktSkany::create([
            'product_id' => $request->product_id,
            'user_id' => $request->user_id,
            'region_id' => $request->region_id,
            'quantity' => $request->quantity,
            'barcode' => $barcode,
            'scanned_at' => now(),
            'price_history' => 0,
        ]);

        return response()->json([
            'success' => true,
            'newScan' => $skan->load('product'),
        ]);
    }



    public function update(Request $request, ProduktSkany $produktSkany)
    {

        $request->validate([
            'quantity' => 'required|numeric|min:0.01',
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

    public function search(Request $request)
    {
        $query = $request->get('q', '');

        $products = Product::with('unit')
            ->when($query, function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhereHas('barcodes', fn($sub) => $sub->where('barcode', 'like', "%{$query}%"));
            })
            ->limit(15)
            ->get(['id', 'name', 'unit_id']); // pobieramy też unit_id

        // dołącz jednostkę do każdego produktu
        $products->load('unit:id,code,name');


        return response()->json($products);
    }
}

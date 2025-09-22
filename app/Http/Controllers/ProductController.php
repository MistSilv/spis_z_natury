<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Unit;
use App\Models\Barcode;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Lista produktów
     */
    public function index()
    {
        $products = Product::with('unit', 'barcodes')->orderByDesc('id')->paginate(100);
        return view('products.list', compact('products'));
    }

    /**
     * Formularz dodania produktu
     */
    public function create()
    {
        $units = Unit::all();
        return view('products.create', compact('units'));
    }

    /**
     * Zapis nowego produktu
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'price'      => 'nullable|numeric|min:0',
            'unit_id'    => 'required|exists:units,id',
            'id_abaco'   => 'nullable|string|max:255',
            'barcodes.*' => 'nullable|string|max:13'
        ]);

        $product = Product::create($validated);

        if ($request->filled('barcodes')) {
            foreach ($request->barcodes as $barcode) {
                if ($barcode) {
                    Barcode::create([
                        'product_id' => $product->id,
                        'barcode'    => $barcode,
                    ]);
                }
            }
        }

        return redirect()->route('products.index')->with('success', 'Produkt został dodany.');
    }

    /**
     * Pokaż produkt
     */
    public function show(Product $product)
    {
        $product->load('unit', 'barcodes');
        return view('products.show', compact('product'));
    }

    /**
     * Formularz edycji produktu
     */
    public function edit(Product $product)
    {
        $units = Unit::all();
        $product->load('barcodes');
        return view('products.edit', compact('product', 'units'));
    }

    /**
     * Aktualizacja produktu
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'price'      => 'nullable|numeric|min:0',
            'unit_id'    => 'required|exists:units,id',
            'id_abaco'   => 'nullable|string|max:255',
            'barcodes.*' => 'nullable|string|max:13'
        ]);

        $product->update($validated);

        // odśwież kody EAN
        $product->barcodes()->delete();
        if ($request->filled('barcodes')) {
            foreach ($request->barcodes as $barcode) {
                if ($barcode) {
                    $product->barcodes()->create(['barcode' => $barcode]);
                }
            }
        }

        return redirect()->route('products.index')->with('success', 'Produkt zaktualizowany.');
    }

    /**
     * Usuń produkt
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Produkt usunięty.');
    }
}

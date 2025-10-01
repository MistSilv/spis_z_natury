<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductPriceHistory;
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

        foreach ($products as $product) {
            $product->canBeDeleted = !$product->isUsed();
        }
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

        // Tworzymy produkt (bez ceny)
        $product = Product::create([
            'name'     => $validated['name'],
            'unit_id'  => $validated['unit_id'],
            'id_abaco' => $validated['id_abaco'] ?? null,
        ]);

        // Dodajemy cenę do historii
        if (!empty($validated['price'])) {
            $product->prices()->create([
                'price' => $validated['price']
            ]);
        }

        // Dodajemy kody EAN
        if ($request->filled('barcodes')) {
            foreach ($request->barcodes as $barcode) {
                if ($barcode) {
                    $product->barcodes()->create(['barcode' => $barcode]);
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

        // Aktualizacja podstawowych danych produktu
        $product->update([
            'name'     => $validated['name'],
            'unit_id'  => $validated['unit_id'],
            'id_abaco' => $validated['id_abaco'] ?? null,
        ]);

        // Dodajemy nową cenę do historii tylko jeśli się zmieniła
        if (!empty($validated['price'])) {
            $latestPrice = $product->latestPrice?->price ?? null;
            if ($latestPrice !== (float)$validated['price']) {
                $product->prices()->create([
                    'price' => $validated['price']
                ]);
            }
        }

        // Odświeżamy kody EAN: usuwamy stare i dodajemy nowe
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

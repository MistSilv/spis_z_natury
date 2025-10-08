<?php

namespace App\Http\Controllers;

use App\Models\Faktura;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\Request;

class FakturaController extends Controller
{
    // Lista faktur
    public function index()
    {
        $faktury = Faktura::latest()->paginate(30);
        return view('faktury.index', compact('faktury'));
    }

    // Formularz dodawania faktury
    public function create()
    {
        return view('faktury.create');
    }

    // Zapis nowej faktury
    public function store(Request $request)
    {
        $request->validate([
            'number' => 'required|unique:faktury,number',
            'data_wystawienia' => 'required|date',
            'data_sprzedazy' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $dataSprzedazy = $request->data_sprzedazy ?? $request->data_wystawienia;

        $faktura = Faktura::create([
            'number' => $request->number,
            'data_wystawienia' => $request->data_wystawienia,
            'data_sprzedazy' => $dataSprzedazy,
            'notes' => $request->notes,
        ]);

        return redirect()->route('faktury.show', $faktura)
            ->with('success', 'Faktura została dodana.');
    }

    // Widok pojedynczej faktury
    public function show(Faktura $faktura)
    {
        $units = Unit::all();


        $produkty = $faktura->produkty()->paginate(20);

        return view('faktury.show', compact('faktura', 'produkty', 'units'));
    }


    // Formularz edycji faktury
    public function edit(Faktura $faktura)
    {
        return view('faktury.edit', compact('faktura'));
    }

    // Aktualizacja faktury
    public function update(Request $request, Faktura $faktura)
    {
        $request->validate([
            'number' => 'required|unique:faktury,number,' . $faktura->id,
            'data_wystawienia' => 'required|date',
            'data_sprzedazy' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $dataSprzedazy = $request->data_sprzedazy ?? $request->data_wystawienia;

        $faktura->update([
            'number' => $request->number,
            'data_wystawienia' => $request->data_wystawienia,
            'data_sprzedazy' => $dataSprzedazy,
            'notes' => $request->notes,
        ]);

        return redirect()->route('faktury.show', $faktura)
            ->with('success', 'Faktura została zaktualizowana.');
    }

    // Widok dodawania produktów do faktury
    public function productsCreate(Faktura $faktura)
    {
        $units = Unit::all();
        return view('faktury.products_create', compact('faktura', 'units'));
    }

    // Zapis produktów do faktury
    public function productsStore(Request $request, Faktura $faktura)
    {
        $request->validate([
            'products' => 'required|array',
            'products.*.name' => 'required|string',
            'products.*.price' => 'required|numeric',
            'products.*.quantity' => 'required|numeric',
            'products.*.unit' => 'nullable|string',
            'products.*.barcode' => 'nullable|string|max:13',
            'products.*.vat' => 'nullable|numeric|min:0',
            'products.*.product_id' => 'nullable|exists:products,id',
        ]);

        foreach ($request->products as $productData) {
            $price = $productData['price'];

            if (!empty($productData['vat'])) {
                $price = $price * (1 + $productData['vat'] / 100);
            }

            // Sprawdzamy, czy to ręcznie dodany produkt (nie ma product_id)
            if (empty($productData['product_id'])) {
                // Znajdź unit_id po kodzie jednostki
                $unit = \App\Models\Unit::where('code', $productData['unit'])->first();

                // Tworzymy nowy produkt w tabeli products
                $newProduct = \App\Models\Product::create([
                    'name' => $productData['name'],
                    'unit_id' => $unit?->id,
                ]);

                // Jeśli podano barcode, dodajemy do tabeli barcodes
                if (!empty($productData['barcode'])) {
                    $newProduct->barcodes()->create([
                        'barcode' => $productData['barcode'],
                    ]);
                }

                // Dodajemy cenę do historii produktów
                $newProduct->prices()->create([
                    'price' => round($price, 2),
                    'changed_at' => $faktura->data_sprzedazy ?? now(),
                ]);

                // Ustawiamy product_id do zapisania w fakturze
                $productData['product_id'] = $newProduct->id;
            }

            // Tworzymy powiązanie produktu z fakturą
            $faktura->produkty()->create([
                'product_id' => $productData['product_id'],
                'name' => $productData['name'],
                'price' => round($price, 2),
                'quantity' => $productData['quantity'],
                'unit' => $productData['unit'] ?? null,
                'barcode' => $productData['barcode'] ?? null,
            ]);
        }

        return redirect()->route('faktury.show', $faktura)
            ->with('success', 'Produkty zostały dodane do faktury.');
    }


    // Live-search produktów do faktury (z uwzględnieniem daty sprzedaży)
    public function productsLiveSearch(Request $request)
    {
        $query = $request->get('q', '');
        $date  = $request->get('date'); // spodziewany format: Y-m-d

        $products = Product::with('unit', 'barcodes')
            ->when($query, function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhereHas('barcodes', fn($sub) => $sub->where('barcode', 'like', "%{$query}%"));
            })
            ->limit(15)
            ->get();

        $result = $products->map(function ($product) use ($date) {
            $priceEntry = $date
                ? $product->prices()
                    ->where('changed_at', '<=', $date)
                    ->orderByDesc('changed_at')
                    ->first()
                : $product->latestPrice;

            return [
                'id'    => $product->id,
                'name'  => $product->name,
                'ean'   => $product->barcodes->first()?->barcode,
                'unit'  => $product->unit?->code ?? '',
                'unit_name' => $product->unit?->name ?? '',
                'price' => $priceEntry?->price ?? null,
            ];

        });

        return response()->json($result);
    }

    public function updateProduct(Request $request, Faktura $faktura, $productId)
    {
        // Znajdujemy rekord produktu powiązany z fakturą
        $produkt = $faktura->produkty()->findOrFail($productId);

        $field = $request->get('field');
        $value = $request->get('value');

        // Walidacja dynamiczna
        $rules = match ($field) {
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'numeric', 'min:0'],
            default => ['nullable'],
        };

        $request->validate(['value' => $rules]);

        // Aktualizacja pola
        $produkt->update([$field => $value]);

        return response()->json([
            'success' => true,
            'message' => "Zaktualizowano pole '{$field}' dla produktu.",
            'produkt' => $produkt,
        ]);
    }

    public function getProducts(Faktura $faktura)
    {
        $produkty = $faktura->produkty()
            ->select('id', 'name', 'price', 'quantity', 'unit', 'barcode')
            ->orderBy('id', 'asc')
            ->get();

        return response()->json($produkty);
    }

    public function destroyProduct(Faktura $faktura, $productId)
    {
        // Znajdź produkt powiązany z fakturą
        $produkt = $faktura->produkty()->findOrFail($productId);

        // Usuń produkt
        $produkt->delete();

        return response()->json([
            'success' => true,
            'message' => 'Produkt został usunięty z faktury.'
        ]);
    }

    
}

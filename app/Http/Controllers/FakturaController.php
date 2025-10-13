<?php

namespace App\Http\Controllers;

use App\Models\Faktura;
use App\Models\Product;
use App\Models\Unit;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FakturaController extends Controller
{
    public function index(Request $request)
    {
        $query = Faktura::with('region');

        if ($request->has('region_id') && $request->region_id) {
            $query->where('region_id', $request->region_id);
        }

        $faktury = $query->latest()->paginate(30);
        $regions = Region::all();

        return view('faktury.index', compact('faktury', 'regions'));
    }

    public function create()
    {
        $regions = Region::all();
        return view('faktury.create', compact('regions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'number' => 'required|unique:faktury,number',
            'data_wystawienia' => 'required|date',
            'data_sprzedazy' => 'nullable|date',
            'notes' => 'nullable|string',
            'region_id' => 'required|integer|exists:regions,id',
        ]);

        $dataSprzedazy = $request->data_sprzedazy ?? $request->data_wystawienia;

        $faktura = Faktura::create([
            'number' => $request->number,
            'data_wystawienia' => $request->data_wystawienia,
            'data_sprzedazy' => $dataSprzedazy,
            'notes' => $request->notes,
            'region_id' => $request->region_id,
        ]);

        return redirect()->route('faktury.show', $faktura)
            ->with('success', 'Faktura została dodana.');
    }

    public function show(Faktura $faktura)
    {
        $units = Unit::all();
        $produkty = $faktura->produkty()->paginate(20);
        return view('faktury.show', compact('faktura', 'produkty', 'units'));
    }

    public function edit(Faktura $faktura)
    {
        $regions = Region::all();
        return view('faktury.edit', compact('faktura', 'regions'));
    }

    public function update(Request $request, Faktura $faktura)
    {
        $request->validate([
            'number' => 'required|unique:faktury,number,' . $faktura->id,
            'data_wystawienia' => 'required|date',
            'data_sprzedazy' => 'nullable|date',
            'notes' => 'nullable|string',
            'region_id' => 'required|exists:regions,id',
        ]);

        $dataSprzedazy = $request->data_sprzedazy ?? $request->data_wystawienia;

        $faktura->update([
            'number' => $request->number,
            'data_wystawienia' => $request->data_wystawienia,
            'data_sprzedazy' => $dataSprzedazy,
            'notes' => $request->notes,
            'region_id' => $request->region_id,
        ]);

        return redirect()->route('faktury.show', $faktura)
            ->with('success', 'Faktura została zaktualizowana.');
    }

    public function productsCreate(Faktura $faktura)
    {
        $units = Unit::all();
        return view('faktury.products_create', compact('faktura', 'units'));
    }

    public function productsStore(Request $request, Faktura $faktura)
    {
        $request->validate([
            'products' => 'required|array',
            'products.*.name' => 'required|string',
            'products.*.price_net' => 'nullable|numeric|min:0',
            'products.*.price_gross' => 'nullable|numeric|min:0',
            'products.*.vat' => 'nullable|numeric|min:0',
            'products.*.quantity' => 'required|numeric|min:0',
            'products.*.unit' => 'nullable|string',
            'products.*.barcode' => 'nullable|string|max:13',
            'products.*.product_id' => 'nullable|exists:products,id',
        ]);

        foreach ($request->products as $productData) {
            $priceNet = $productData['price_net'] ?? null;
            $priceGross = $productData['price_gross'] ?? null;
            $vat = $productData['vat'] ?? null;

            if (!is_null($priceNet) && !is_null($vat) && $vat > 0) {
                $priceGross = round($priceNet * (1 + $vat / 100), 2);
            } elseif (!is_null($priceGross) && !is_null($vat) && $vat > 0) {
                $priceNet = round($priceGross / (1 + $vat / 100), 2);
            } else {
                $priceGross = null;
            }

            $unit = Unit::where('code', $productData['unit'])->first();

            if (empty($productData['product_id'])) {
                $newProduct = Product::create([
                    'name' => $productData['name'],
                    'unit_id' => $unit?->id,
                ]);

                if (!empty($productData['barcode'])) {
                    $newProduct->barcodes()->create(['barcode' => $productData['barcode']]);
                }

                $newProduct->prices()->create([
                    'price' => $priceNet ?? $priceGross,
                    'changed_at' => $faktura->data_sprzedazy ?? now(),
                ]);

                $productData['product_id'] = $newProduct->id;
            }

            $faktura->produkty()->create([
                'product_id'  => $productData['product_id'],
                'name'        => $productData['name'],
                'price_net'   => $priceNet,
                'price_gross' => $priceGross,
                'vat'         => $vat,
                'quantity'    => $productData['quantity'],
                'unit'        => $productData['unit'] ?? null,
                'barcode'     => $productData['barcode'] ?? null,
            ]);
        }

        return redirect()->route('faktury.show', $faktura)
            ->with('success', 'Produkty zostały dodane do faktury.');
    }

    public function productsLiveSearch(Request $request)
    {
        $query = $request->get('q', '');
        $date  = $request->get('date');



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
                : $product->prices()->latest('changed_at')->first();

            $mapped = [
                'id'        => $product->id,
                'name'      => $product->name,
                'price_net' => $priceEntry?->price ?? null,
                'unit'      => $product->unit?->code ?? '',
                'unit_name' => $product->unit?->name ?? '',
                'barcode'   => $product->barcodes->first()?->barcode,
            ];


            return $mapped;
        });



        return response()->json($result);
    }

    public function updateProduct(Request $request, Faktura $faktura, $productId)
    {
        $produkt = $faktura->produkty()->findOrFail($productId);
        $field = $request->get('field');
        $value = $request->get('value');

        $rules = match ($field) {
            'name' => ['required', 'string', 'max:255'],
            'price_net', 'price_gross', 'vat' => ['nullable', 'numeric', 'min:0'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'max:10'],
            'barcode' => ['nullable', 'string', 'max:13'],
            default => ['nullable'],
        };

        $request->validate(['value' => $rules]);

        if ($field === 'price_gross' && !is_null($value) && !is_null($produkt->vat)) {
            $produkt->price_net = round($value / (1 + ($produkt->vat / 100)), 2);
        } elseif ($field === 'vat' && !is_null($value) && !is_null($produkt->price_gross)) {
            $produkt->price_net = round($produkt->price_gross / (1 + ($value / 100)), 2);
        }

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
            ->select('id', 'name', 'price_net', 'price_gross', 'vat', 'quantity', 'unit', 'barcode')
            ->orderBy('id', 'asc')
            ->get();

        return response()->json($produkty);
    }

    public function destroyProduct(Faktura $faktura, $productId)
    {
        $produkt = $faktura->produkty()->findOrFail($productId);
        $produkt->delete();

        return response()->json([
            'success' => true,
            'message' => 'Produkt został usunięty z faktury.'
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\SpisZNatury;
use App\Models\Region;
use App\Models\ProduktSkany;
use Illuminate\Http\Request;

class SpisZNaturyController extends Controller
{
    // Wyświetla listę spisów (bez produktów)
    public function index()
    {
        $spisy = SpisZNatury::with(['user', 'region'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('spis_z_natury.index', compact('spisy'));
    }

    // Pusty formularz do tworzenia nowego spisu
    public function create(Request $request)
    {
        $regions = Region::all();

        $query = ProduktSkany::with(['product.unit'])
            ->orderBy('scanned_at', 'desc');

        // Filtry
        if ($request->region_id) {
            $query->where('region_id', $request->region_id);
        }

        if ($request->start_date) {
            $query->whereDate('scanned_at', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('scanned_at', '<=', $request->end_date);
        }

        // Liczba rekordów na stronę (domyślnie 50)
        $perPage = in_array($request->get('per_page'), [50, 100, 200]) ? $request->get('per_page') : 50;

        $scans = $query->paginate($perPage)->withQueryString(); // zachowuje parametry GET przy paginacji

        return view('spis_z_natury.create', compact('regions', 'scans', 'perPage'));
    }


}

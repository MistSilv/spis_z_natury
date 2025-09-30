<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SpisZNatury;
use App\Models\SpisProdukty;
use Barryvdh\DomPDF\Facade\Pdf;

class SpisPdfController extends Controller
{
    public function export($spisId)
    {
        $spis = SpisZNatury::findOrFail($spisId);
        $produkty = SpisProdukty::where('spis_id', $spisId)
            ->orderBy('id', 'asc') // initial FIFO
            ->get();

        // Group by name, but keep the original ID order within each name
        $products = $produkty
            ->groupBy('name') // group duplicates
            ->flatMap(function ($group) {
                return $group; // preserves ID order within the group
            })
            ->values() // reset keys
            ->map(function ($item) {
                return (object)[
                    'name'        => $item->name,
                    'ean'         => $item->barcode,
                    'unit'        => $item->unit,
                    'quantity'    => $item->quantity,
                    'unit_price'  => $item->price,
                    'total_value' => $item->price * $item->quantity,
                ];
            });


        return Pdf::loadView('pdf.spis_export', [
        'spis'     => $spis,
        'products' => $products,
        'date'     => now()->format('d.m.Y'),
        ])
        ->setPaper('a4')
        ->setOption('isPhpEnabled', true)
       // ->setOption('dpi', 150) // better rendering quality
        ->setOption('defaultFont', 'DejaVu Sans') // ensures Unicode characters
        //->setOption('isHtml5ParserEnabled', true)
        ->setOption('isRemoteEnabled', true) // if you have external images/CSS
        ->download('spis_' . $spis->name . '.pdf');

    }
}
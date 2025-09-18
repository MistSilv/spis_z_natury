<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index()
    {
        return response()->json(['message' => 'ProductController index']);
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        return response()->json(['message' => 'ProductController create']);
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request)
    {
        return response()->json(['message' => 'ProductController store']);
    }

    /**
     * Display the specified product.
     */
    public function show($id)
    {
        return response()->json(['message' => "ProductController show {$id}"]);
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit($id)
    {
        return response()->json(['message' => "ProductController edit {$id}"]);
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, $id)
    {
        return response()->json(['message' => "ProductController update {$id}"]);
    }

    /**
     * Remove the specified product.
     */
    public function destroy($id)
    {
        return response()->json(['message' => "ProductController destroy {$id}"]);
    }
}

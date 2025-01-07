<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::all();

        // Trả về JSON response
        return response()->json([
            'success' => true,
            'data' => $products
        ], 200);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Attributes\PreAuthorize;

class ProductController extends Controller
{
    #[PreAuthorize('product.list')]
    public function index(): \Illuminate\Http\JsonResponse
    {
        $products = Product::all();

        // Trả về JSON response
        return response()->json([
            'success' => true,
            'data' => $products
        ], 200);
    }
}

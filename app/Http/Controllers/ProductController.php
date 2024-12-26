<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        // Lấy tất cả sản phẩm từ database
        $products = Product::all();

        echo $products;

        // Trả về JSON response
        return response()->json([
            'success' => true,
            'data' => $products
        ], 200);
    }
}

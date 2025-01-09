<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Attributes\PreAuthorize;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    #[PreAuthorize('AD_TE')]
    public function index(): \Illuminate\Http\JsonResponse
    {
        $products = Product::all();

        Log::info('token : ' . $this->getCurrentToken());

        return response()->json([
            'success' => true,
            'data' => $products
        ], 200);
    }


}

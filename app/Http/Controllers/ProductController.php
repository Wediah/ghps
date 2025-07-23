<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Farm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::where('is_approved', true)->get();
        return response()->json($products);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'farm_id' => 'required|exists:farms,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|in:chicks,eggs,feed,medicine,tools',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Verify the farm belongs to the authenticated user
        $farm = Farm::find($request->farm_id);
        if ($farm->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'You can only add products to your own farm'
            ], 403);
        }

        $product = Product::create([
            'farm_id' => $request->farm_id,
            'name' => $request->name,
            'description' => $request->description,
            'category' => $request->category,
            'price' => $request->price,
            'quantity' => $request->quantity,
            'is_approved' => false
        ]);

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product
        ], 201);
    }

    public function approve($id)
    {
        $product = Product::findOrFail($id);
        $product->update(['is_approved' => true]);

        return response()->json([
            'message' => 'Product approved successfully'
        ]);
    }

    public function pending()
    {
        $products = Product::where('is_approved', false)->get();
        return response()->json($products);
    }
}

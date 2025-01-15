<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Http\Resources\ProductResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Jobs\SendLowStockNotification;
use Illuminate\Support\Facades\Validator;


class ProductController extends BaseController
{
    public function index(): JsonResponse
    {
        $products = Product::all();
        $products =Cache::remember('products', 60, function(){
            return Product::all();
        });
        return $this->sendResponse(ProductResource::collection($products), 'Products retrieved successfully.');
    }

    public function store(Request $request): JsonResponse
    {
        $input = $request->all();
        $validor = Validator::make($input,[
            'name' => 'required',
            'price' => 'required',
            'stock' => 'required|integer',
            'minimum_stock' => 'required|integer',
        ]);
        if($validor->fails())
        {
            return $this->sendError('Validator Error.', $validor->errors());
        }
        $product = Product::create($input);
        if($product->stock < $product->minimum_stock){
            SendLowStockNotification::dispatch($product);
        }
        Cache::forget('products');

        return $this->sendResponse(new ProductResource($product), 'Product created successfully.');

    }

    public function show($id): JsonResponse
    {
        $product = Cache::remember("product_{$id}", 60, function () use ($id) {
            return Product::find($id);
        });
        
        if(is_null($product))
        {
            return $this->sendError('Product not found');
        }
        return $this->sendResponse(new ProductResource($product),'Product retrieved successfully');
    }

    public function update(Request $request, Product $product):JsonResponse
    {

        $product = Product::find($product->id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        
        $input = $request->all();
        $validor = Validator::make($input,[
            'name' => 'required',
            'price' => 'required',
            'stock' => 'required|integer',
            'minimum_stock' => 'required|integer',
        ]);
        if($validor->fails())
        {
            return $this->sendError('Validator Error.', $validor->errors());
        }
        
        $product->name = $input['name'];
        $product->description = $input['description'] ?? $product->description;
        $product->price = $input['price'];
        $product->stock = $input['stock'];
        $product->minimum_stock = $input['minimum_stock'] ?? $product->minimum_stock;
        $product->save();

        if ($product->stock < $product->minimum_stock) {
            SendLowStockNotification::dispatch($product);
        }

        Cache::forget("product_{$product->id}");
        Cache::forget('products');
        return $this->sendResponse(new ProductResource($product),"Product updated successfully");
    }
    public function destroy(Product $product):JsonResponse
    {
        $product = Product::find($product->id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product->delete();
        return $this->sendResponse([],'Product successfully destroyed');
    }
}
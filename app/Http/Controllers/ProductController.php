<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if($request->ajax()){
            $products = Product::with([ 'createdBy:id,name', 'updatedBy:id,name'])->get();

            return DataTables::of($products)
                ->addColumn('actions', function ($product) {
                    return '<button class="btn btn-sm btn-primary edit-btn" data-id="' . $product->id . '">Edit</button>
                            <button class="btn btn-sm btn-danger delete-btn" data-id="' . $product->id . '">Delete</button>';
                })
                ->editColumn('status', function ($product) {
                    return Product::STATUS[$product->status]; // Display the readable status
                })
                ->rawColumns(['actions'])
                ->make(true);
        }
        return view('products.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
    
        Product::create([
            'name' => $request->name,
            'status' => 0,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);
        return response()->json(['success' => 'Product created successfully.']);
    }

    /**
     * Display the specified resources.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $product = Product::findOrFail($id);
        return response()->json($product); 
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
    
        $product = Product::findOrFail($id);
        $product->update([
            'name' => $request->name,
            'updated_by' => Auth::id(),
        ]);
    
        return response()->json(['success' => 'Product updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
    
        return response()->json(['success' => 'Product deleted successfully.']);
    }
}

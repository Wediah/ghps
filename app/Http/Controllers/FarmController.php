<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FarmController extends Controller
{
    public function index()
    {
        $farms = Farm::where('is_approved', true)->get();
        return response()->json($farms);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'contact_phone' => 'required|string',
            'contact_email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $farm = Farm::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'description' => $request->description,
            'location' => $request->location,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'contact_phone' => $request->contact_phone,
            'contact_email' => $request->contact_email,
            'is_approved' => false
        ]);

        return response()->json([
            'message' => 'Farm created successfully',
            'farm' => $farm
        ], 201);
    }

    public function approve($id)
    {
        $farm = Farm::findOrFail($id);
        $farm->update(['is_approved' => true]);

        return response()->json([
            'message' => 'Farm approved successfully'
        ]);
    }
}

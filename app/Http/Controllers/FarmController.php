<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class FarmController extends Controller
{
    public function index()
    {
        $farms = Farm::where('is_approved', true)
            ->with('media')
            ->get();
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
            'contact_email' => 'required|email',
            'farm_images' => 'nullable|array',
            'farm_images.*' => 'string', // Base64 strings
            'poultry_images' => 'nullable|array',
            'poultry_images.*' => 'string', // Base64 strings
            'warehouse_images' => 'nullable|array',
            'warehouse_images.*' => 'string' // Base64 strings
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

        // Handle file uploads
        $this->handleBase64Uploads($request, $farm);

        return response()->json([
            'message' => 'Farm created successfully',
            'farm' => $farm->load('media')
        ], 201);
    }

    protected function handleBase64Uploads(Request $request, Farm $farm)
    {
        // Upload farm images
        if ($request->filled('farm_images')) {
            foreach ($request->farm_images as $base64Image) {
                $upload = Cloudinary::upload($base64Image, [
                    'folder' => 'farms/'.$farm->id.'/farm',
                    'resource_type' => 'image'
                ]);

                $farm->media()->create([
                    'file_url' => $upload->getSecurePath(),
                    'file_type' => 'image',
                    'collection_name' => 'farm_images'
                ]);
            }
        }

        // Upload poultry images
        if ($request->filled('poultry_images')) {
            foreach ($request->poultry_images as $base64Image) {
                $upload = Cloudinary::upload($base64Image, [
                    'folder' => 'farms/'.$farm->id.'/poultry',
                    'resource_type' => 'image'
                ]);

                $farm->media()->create([
                    'file_url' => $upload->getSecurePath(),
                    'file_type' => 'image',
                    'collection_name' => 'poultry_images'
                ]);
            }
        }

        // Upload warehouse images
        if ($request->filled('warehouse_images')) {
            foreach ($request->warehouse_images as $base64Image) {
                $upload = Cloudinary::upload($base64Image, [
                    'folder' => 'farms/'.$farm->id.'/warehouse',
                    'resource_type' => 'image'
                ]);

                $farm->media()->create([
                    'file_url' => $upload->getSecurePath(),
                    'file_type' => 'image',
                    'collection_name' => 'warehouse_images'
                ]);
            }
        }
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

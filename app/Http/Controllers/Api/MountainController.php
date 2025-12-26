<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mountain;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MountainController extends Controller
{
    const DEFAULT_IMAGE_PATH = 'mountains/defaultMountainPics.jpg';

    // Get all mountains
    public function index()
    {
        try {
            $mountains = Mountain::orderBy('created_at', 'desc')->get();

            // Return a consistent envelope like other endpoints
            return response()->json([
                'success' => true,
                'message' => 'List of mountains',
                'data' => $mountains,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch mountains',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Create new mountain
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'status' => 'required|string|max:255',
                'manager' => 'required|string|max:255',
                'quota' => 'required|integer|min:0',
                'location' => 'required|string|max:255',
                'contact' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'duration' => 'required|string|max:255',
                'pos' => 'required|string|max:255',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB
                'image_url' => 'nullable|string|max:500',
                'image_path' => 'nullable|string|max:255',
                'image_type' => 'required|string|in:file,url,default',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $validated = $validator->validated();

            // Handle image based on type
            $validated['image'] = $this->handleImageUpload($request);

            // Remove helper fields
            unset($validated['image_url'], $validated['image_path'], $validated['image_type']);

            $mountain = Mountain::create($validated);

            return response()->json([
                'message' => 'Mountain created successfully',
                'data' => $mountain,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create mountain',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Get single mountain
    public function show($id)
    {
        try {
            $mountain = Mountain::findOrFail($id);

            return response()->json($mountain, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Mountain not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    // Update mountain
    public function update(Request $request, $id)
    {
        try {
            $mountain = Mountain::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'status' => 'required|string|max:255',
                'manager' => 'required|string|max:255',
                'quota' => 'required|integer|min:0',
                'location' => 'required|string|max:255',
                'contact' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'duration' => 'required|string|max:255',
                'pos' => 'required|string|max:255',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
                'image_url' => 'nullable|string|max:500',
                'image_path' => 'nullable|string|max:255',
                'image_type' => 'required|string|in:file,url,default,keep',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $validated = $validator->validated();

            // Handle image update
            if ($request->image_type !== 'keep') {
                // Delete old image if it exists and is not default
                if ($mountain->image &&
                    $mountain->image !== self::DEFAULT_IMAGE_PATH &&
                    ! filter_var($mountain->image, FILTER_VALIDATE_URL)) {
                    Storage::disk('public')->delete($mountain->image);
                }

                $validated['image'] = $this->handleImageUpload($request);
            } else {
                // Keep current image
                unset($validated['image']);
            }

            // Remove helper fields
            unset($validated['image_url'], $validated['image_path'], $validated['image_type']);

            $mountain->update($validated);

            return response()->json([
                'message' => 'Mountain updated successfully',
                'data' => $mountain,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update mountain',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Delete mountain
    public function destroy($id)
    {
        try {
            $mountain = Mountain::findOrFail($id);

            // Delete image if it exists and is not default or URL
            if ($mountain->image &&
                $mountain->image !== self::DEFAULT_IMAGE_PATH &&
                ! filter_var($mountain->image, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($mountain->image);
            }

            $mountain->delete();

            return response()->json([
                'message' => 'Mountain deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete mountain',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle image upload based on type
     */
    private function handleImageUpload(Request $request)
    {
        $imageType = $request->input('image_type');

        switch ($imageType) {
            case 'file':
                if ($request->hasFile('image')) {
                    $path = $request->file('image')->store('mountains', 'public');

                    return $path;
                }

                return self::DEFAULT_IMAGE_PATH;

            case 'url':
                $url = $request->input('image_url');
                if ($url && filter_var($url, FILTER_VALIDATE_URL)) {
                    return $url;
                }

                return self::DEFAULT_IMAGE_PATH;

            case 'default':
            default:
                return $request->input('image_path') ?? self::DEFAULT_IMAGE_PATH;
        }
    }
}

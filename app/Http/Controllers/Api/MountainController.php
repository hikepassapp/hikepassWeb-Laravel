<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mountain;
use Illuminate\Http\Request;

class MountainController extends Controller
{
    // Get all mountains
    public function index()
    {
        $mountains = Mountain::all();
        return response()->json($mountains);
    }

    // Create new mountain
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'manager' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
            'quota' => 'nullable|integer',
            'location' => 'nullable|string|max:255',
            'contact' => 'nullable|string|max:255',
            'price' => 'nullable|integer',
            'duration' => 'nullable|string|max:255',
            'pos' => 'nullable|string|max:255',
            'image' => 'nullable|string|max:255',
        ]);

        $mountain = Mountain::create($validated);
        return response()->json($mountain, 201);
    }

    // Get single mountain
    public function show($id)
    {
        $mountain = Mountain::findOrFail($id);
        return response()->json($mountain);
    }

    // Update mountain
    public function update(Request $request, $id)
    {
        $mountain = Mountain::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'manager' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
            'quota' => 'nullable|integer',
            'location' => 'nullable|string|max:255',
            'contact' => 'nullable|string|max:255',
            'price' => 'nullable|integer',
            'duration' => 'nullable|string|max:255',
            'pos' => 'nullable|string|max:255',
            'image' => 'nullable|string|max:255',
        ]);

        $mountain->update($validated);
        return response()->json($mountain);
    }

    // Delete mountain
    public function destroy($id)
    {
        $mountain = Mountain::findOrFail($id);
        $mountain->delete();
        return response()->json(['message' => 'Mountain deleted successfully']);
    }
}
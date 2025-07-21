<?php

namespace App\Http\Controllers\admin;

use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminFacilityController
{
    public function adminManageFacilities()
    {
        $facilities = Facility::all();
        return view('manage-facilities', compact('facilities'));
    }

    public function adminFacilities()
    {
        $facilities = Facility::all();
        return view('facilities', compact('facilities'));
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'facility_name' => 'required|string|max:255',
        ]);

        try {
            $validated['picture'] = $request->file('picture')->store('facilities', 'public');
            Facility::create($validated);

            return redirect()->route('admin.facilities.manage')->with('success', 'Facility added successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error adding facility: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $facility = Facility::findOrFail($id);

        $validated = $request->validate([
            'facility_name' => 'required|string|max:255',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            if ($request->hasFile('picture')) {
                // Delete old image
                if ($facility->picture) {
                    Storage::disk('public')->delete($facility->picture);
                }
                $validated['picture'] = $request->file('picture')->store('facilities', 'public');
            }

            $facility->update($validated);

            return redirect()->route('admin.facilities.manage')->with('success', 'Facility updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error updating facility: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $facility = Facility::findOrFail($id);

        try {
            if ($facility->picture) {
                Storage::disk('public')->delete($facility->picture);
            }

            $facility->delete();

            return redirect()->route('admin.facilities.manage')->with('success', 'Facility deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error deleting facility: ' . $e->getMessage());
        }
    }
}

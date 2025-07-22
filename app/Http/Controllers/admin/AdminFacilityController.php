<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\FacilityDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminFacilityController extends Controller
{
    public function adminManageFacilities()
    {
        $facilities = Facility::with('details')->get();
        return view('manage-facilities', compact('facilities'));
    }

    public function adminFacilities()
    {
        $facilities = Facility::with('details')->get();
        return view('facilities', compact('facilities'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'facility_name' => 'required|string|max:255',
            'facility_condition' => 'nullable|string|max:255',
            'status' => 'required|in:available,not_available',
            'picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'facility_per_hour_rate' => 'nullable|numeric|min:0',
            'facility_package_rate1' => 'nullable|numeric|min:0',
            'facility_package_rate2' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Store facility
            $facility = Facility::create([
                'facility_name' => $validated['facility_name'],
                'status' => $validated['status'],
                'picture' => $request->file('picture')->store('facilities', 'public'),
            ]);

            // Store facility details
            FacilityDetails::create([
                'facility_id' => $facility->facility_id,
                'facility_per_hour_rate' => $validated['facility_per_hour_rate'] ?? null,
                'facility_package_rate1' => $validated['facility_package_rate1'] ?? null,
                'facility_package_rate2' => $validated['facility_package_rate2'] ?? null,
            ]);

            DB::commit();

            return redirect()->route('admin.facilities.manage')->with('success', 'Facility added successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Facility store error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error adding facility: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $facility = Facility::with('details')->findOrFail($id);

        $validated = $request->validate([
            'facility_name' => 'required|string|max:255',
            'facility_condition' => 'nullable|string|max:255',
            'status' => 'required|in:available,not_available',
            'picture' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'facility_per_hour_rate' => 'nullable|numeric|min:0',
            'facility_package_rate1' => 'nullable|numeric|min:0',
            'facility_package_rate2' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $updateData = [
                'facility_name' => $validated['facility_name'],
                'status' => $validated['status'],
            ];

            if ($request->hasFile('picture')) {
                if ($facility->picture) {
                    Storage::disk('public')->delete($facility->picture);
                }
                $updateData['picture'] = $request->file('picture')->store('facilities', 'public');
            }

            $facility->update($updateData);

            // Update or create facility details
            if ($facility->details) {
                $facility->details->update([
                    'facility_per_hour_rate' => $validated['facility_per_hour_rate'] ?? null,
                    'facility_package_rate1' => $validated['facility_package_rate1'] ?? null,
                    'facility_package_rate2' => $validated['facility_package_rate2'] ?? null,
                ]);
            } else {
                FacilityDetails::create([
                    'facility_id' => $facility->facility_id,
                    'facility_per_hour_rate' => $validated['facility_per_hour_rate'] ?? null,
                    'facility_package_rate1' => $validated['facility_package_rate1'] ?? null,
                    'facility_package_rate2' => $validated['facility_package_rate2'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.facilities.manage')->with('success', 'Facility updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Facility update error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error updating facility: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $facility = Facility::with('details')->findOrFail($id);

        try {
            DB::beginTransaction();

            // Delete associated details if they exist
            if ($facility->details) {
                $facility->details->delete();
            }

            // Delete associated image if it exists
            if ($facility->picture) {
                Storage::disk('public')->delete($facility->picture);
            }

            $facility->delete();

            DB::commit();

            return redirect()->route('admin.facilities.manage')->with('success', 'Facility deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Facility delete error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error deleting facility: ' . $e->getMessage());
        }
    }
}

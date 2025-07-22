<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\EquipmentDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AdminEquipmentsController extends Controller
{
    public function adminManageEquipments()
    {
        $equipments = Equipment::with('details')->get();
        return view('manage-equipments', compact('equipments'));
    }

    public function adminEquipments()
    {
        $equipments = Equipment::with('details')->get();
        return view('equipments', compact('equipments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'equipment_name' => 'required|string|max:255',
            'units' => 'required|integer|min:1',
            'status' => 'required|in:available,not_available',
            'picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'equipment_per_hour_rate' => 'nullable|numeric|min:0',
            'equipment_package_rate1' => 'nullable|numeric|min:0',
            'equipment_package_rate2' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Store equipment
            $equipment = Equipment::create([
                'equipment_name' => $validated['equipment_name'],
                'units' => $validated['units'],
                'status' => $validated['status'],
                'picture' => $request->file('picture')->store('equipments', 'public'),
            ]);

            // Store equipment details
            EquipmentDetails::create([
                'equipment_id' => $equipment->equipment_id,
                'equipment_per_hour_rate' => $validated['equipment_per_hour_rate'] ?? null,
                'equipment_package_rate1' => $validated['equipment_package_rate1'] ?? null,
                'equipment_package_rate2' => $validated['equipment_package_rate2'] ?? null,
            ]);

            DB::commit();

            return redirect()->route('admin.equipments.manage')->with('success', 'Equipment added successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Equipment store error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error adding equipment: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $equipment = Equipment::with('details')->findOrFail($id);

        $validated = $request->validate([
            'equipment_name' => 'required|string|max:255',
            'units' => 'required|integer|min:1',
            'status' => 'required|in:available,not_available',
            'picture' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'equipment_per_hour_rate' => 'nullable|numeric|min:0',
            'equipment_package_rate1' => 'nullable|numeric|min:0',
            'equipment_package_rate2' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $updateData = [
                'equipment_name' => $validated['equipment_name'],
                'units' => $validated['units'],
                'status' => $validated['status'],
            ];

            if ($request->hasFile('picture')) {
                if ($equipment->picture) {
                    Storage::disk('public')->delete($equipment->picture);
                }
                $updateData['picture'] = $request->file('picture')->store('equipments', 'public');
            }

            $equipment->update($updateData);

            // Update or create equipment details
            if ($equipment->details) {
                $equipment->details->update([
                    'equipment_per_hour_rate' => $validated['equipment_per_hour_rate'] ?? null,
                    'equipment_package_rate1' => $validated['equipment_package_rate1'] ?? null,
                    'equipment_package_rate2' => $validated['equipment_package_rate2'] ?? null,
                ]);
            } else {
                EquipmentDetails::create([
                    'equipment_id' => $equipment->equipment_id,
                    'equipment_per_hour_rate' => $validated['equipment_per_hour_rate'] ?? null,
                    'equipment_package_rate1' => $validated['equipment_package_rate1'] ?? null,
                    'equipment_package_rate2' => $validated['equipment_package_rate2'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.equipments.manage')->with('success', 'Equipment updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Equipment update error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error updating equipment: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $equipment = Equipment::with('details')->findOrFail($id);

        try {
            DB::beginTransaction();

            // Delete associated details if they exist
            if ($equipment->details) {
                $equipment->details->delete();
            }

            // Delete associated image if it exists
            if ($equipment->picture) {
                Storage::disk('public')->delete($equipment->picture);
            }

            $equipment->delete();

            DB::commit();

            return redirect()->route('admin.equipments.manage')->with('success', 'Equipment deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Equipment delete error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error deleting equipment: ' . $e->getMessage());
        }
    }
}

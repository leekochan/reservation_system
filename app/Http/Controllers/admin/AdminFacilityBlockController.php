<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminFacilityBlock;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AdminFacilityBlockController extends Controller
{
    /**
     * Store a new facility block
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'facility_id' => 'required|exists:facilities,facility_id',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'purpose' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            // Check for existing blocks that conflict with this time range
            $conflictingBlocks = AdminFacilityBlock::active()
                ->forFacility($validated['facility_id'])
                ->forDate($validated['date'])
                ->where(function($query) use ($validated) {
                    $query->where(function($q) use ($validated) {
                        // Check if new block overlaps with existing blocks
                        $q->where('start_time', '<', $validated['end_time'])
                          ->where('end_time', '>', $validated['start_time']);
                    });
                })
                ->exists();

            if ($conflictingBlocks) {
                return response()->json([
                    'success' => false,
                    'message' => 'This time slot conflicts with an existing blocked period.'
                ], 422);
            }

            $block = AdminFacilityBlock::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Facility time slot blocked successfully.',
                'block' => $block->load('facility')
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating facility block: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while blocking the facility.'
            ], 500);
        }
    }

    /**
     * Get facility blocks for a specific date and facility
     */
    public function getBlocks(Request $request)
    {
        $facilityId = $request->query('facility_id');
        $date = $request->query('date');

        $query = AdminFacilityBlock::active()->with('facility');

        if ($facilityId) {
            $query->forFacility($facilityId);
        }

        if ($date) {
            $query->forDate($date);
        }

        $blocks = $query->orderBy('date')->orderBy('start_time')->get();

        return response()->json([
            'success' => true,
            'blocks' => $blocks
        ]);
    }

    /**
     * Delete a facility block
     */
    public function destroy($id)
    {
        try {
            $block = AdminFacilityBlock::findOrFail($id);
            $block->delete();

            return response()->json([
                'success' => true,
                'message' => 'Facility block removed successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting facility block: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while removing the facility block.'
            ], 500);
        }
    }

    /**
     * Show the manage blocks view
     */
    public function manage()
    {
        $facilities = Facility::where('status', 'available')->get();
        $blocks = AdminFacilityBlock::active()
            ->with('facility')
            ->where('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        return view('admin.manage-facility-blocks', compact('facilities', 'blocks'));
    }
}

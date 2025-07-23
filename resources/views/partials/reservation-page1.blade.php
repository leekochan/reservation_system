<!-- Page 1: Basic Info and Calendar -->
<div id="page1" class="form-page">
    <div class="mb-6 mt-6">
        <label class="block text-gray-700 font-medium mb-2">Transaction Date</label>
        <input id="transaction-date" name="transaction_date" readonly class="w-1/2 px-4 py-2 border-2 border-gray-600 rounded-lg bg-gray-100">
    </div>

    <div class="mb-4">
        <label class="block text-gray-700 font-medium mb-2">Reservation Type:</label>        <div class="flex flex-row justify-between w-1/2">            <label class="inline-flex items-center">
                <input type="radio" name="reservation_type" value="single" class="form-radio h-5 w-5 text-blue-600">
                <span class="ml-2 text-gray-700">Single</span>
            </label>
            <label class="inline-flex items-center">
                <input type="radio" name="reservation_type" value="consecutive" class="form-radio h-5 w-5 text-blue-600">
                <span class="ml-2 text-gray-700">Consecutive</span>
            </label>
            <label class="inline-flex items-center">
                <input type="radio" name="reservation_type" value="multiple" class="form-radio h-5 w-5 text-blue-600">
                <span class="ml-2 text-gray-700">Multiple</span>
            </label>
        </div>
    </div>

    <div id="consecutive-options" class="mb-4 hidden">
        <label class="block text-gray-700 font-medium mb-2">Total Number of Days:</label>
        <select id="days-count" class="w-[180px] px-3 py-2 border-2 border-gray-600 rounded-lg">
            <option value="">Select days</option>
            <option value="2">2</option>
            <option value="3">3</option>
        </select>
    </div>

    <div class="mb-4 flex flex-row gap-8">
        <div class="mb-4 flex flex-row gap-8">
            <div>
                <label class="block text-gray-700 font-medium mb-2">Facilities</label>
                <select id="facility-select" name="facility_id" class="w-[270px] px-3 py-2 border-2 border-gray-600 rounded-lg">
                    <option value="">Select a facility</option>
                    @foreach($facilities as $facility)
                        <option value="{{ $facility->facility_id }}" data-name="{{ $facility->facility_name }}">
                            {{ $facility->facility_name }}
                        </option>
                    @endforeach
                </select>
                <div id="calendar-message" class="text-sm mt-2">
                    Please select a reservation type and facility to see availability
                </div>
            </div>
        </div>

        <div id="calendar-container" class="w-full">
            <label class="block text-gray-700 font-medium mb-2">Available Dates</label>
            <div class="calendar border-2 border-gray-600">
                <div class="calendar-header">
                    <button id="prev-month">&lt;</button>
                    <div id="calendar-title" class="calendar-title">Month Year</div>
                    <button id="next-month">&gt;</button>
                </div>
                <div class="calendar-weekdays text-sm">
                    <div>Sun</div>
                    <div>Mon</div>
                    <div>Tue</div>
                    <div>Wed</div>
                    <div>Thu</div>
                    <div>Fri</div>
                    <div>Sat</div>
                </div>
                <div id="calendar-days" class="calendar-days"></div>
            </div>
            <input type="hidden" id="selected-date">
        </div>
    </div>

    <div id="date-time-container" class="hidden">
        <div id="multiple-dates-container">
            <!-- Date groups will be added here -->
        </div>
    </div>
</div>

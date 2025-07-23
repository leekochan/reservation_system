<!-- Page 2: Purpose and Equipment -->
<div id="page2" class="form-page">        
    <div class="mt-6 p-4 border-2 border-gray-700 rounded-md">
        <label class="block text-gray-700 font-medium mb-2">State the purpose of your request which includes the type of
            activity (ex. API), whether free use, in partnership with outside org., fund source, etc.)</label>
        <input type="text" id="purpose" name="purpose" placeholder="Enter your answer here." class="border-2 w-full p-4" required>
    </div>

    <div class="mt-6 p-4 border-2 border-gray-700 rounded-md">
        <label class="block text-gray-700 font-medium mb-2">Do you need other equipment?</label>
        <div class="flex flex-row gap-6">
            <label class="inline-flex items-center">
                <input type="radio" name="need_equipment" value="yes" class="form-radio h-5 w-5 text-blue-600">
                <span class="ml-2 text-gray-700">Yes</span>
            </label>
            <label class="inline-flex items-center">
                <input type="radio" name="need_equipment" value="no" class="form-radio h-5 w-5 text-blue-600" checked>
                <span class="ml-2 text-gray-700">No</span>
            </label>
        </div>

        <div id="equipment-container" class="equipment-container">
            <!-- Equipment rows will be added here dynamically -->
        </div>
    </div>

    <div class="mt-6 p-4 border-2 border-gray-700 rounded-md">
        <label class="block text-gray-700 font-medium mb-2">Other details of the reservation (Please include instructions of the reservation ex. no. of chairs, tables, physical arrangements, etc.)</label>
        <input type="text" id="other_details" name="other_details" placeholder="Enter your answer here." class="border-2 w-full p-4" required>
    </div>

    <div class="mt-6 p-4 border-2 border-gray-700 rounded-md">
        <label class="block text-gray-700 font-medium mb-2">Do you have personal equipment / instrument to bring? (Note: Subject to electric consumption rental computation)</label>
        <div class="flex flex-row gap-6">
            <label class="inline-flex items-center">
                <input type="radio" name="personal_equipment" value="yes" class="form-radio h-5 w-5 text-blue-600">
                <span class="ml-2 text-gray-700">Yes</span>
            </label>
            <label class="inline-flex items-center">
                <input type="radio" name="personal_equipment" value="no" class="form-radio h-5 w-5 text-blue-600" checked>
                <span class="ml-2 text-gray-700">No</span>
            </label>
        </div>
        <div id="personal-equipment-input" class="mt-4 hidden">
            <label class="block text-gray-700 font-medium mb-2">Enter your answer here..</label>
            <input type="text" id="personal_equipment_details" name="personal_equipment_details" placeholder="List your personal equipment" class="border-2 w-full p-4">
        </div>
    </div>
</div>

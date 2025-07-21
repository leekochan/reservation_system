<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
    <style>
        .calendar {
            font-family: Arial, sans-serif;
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .calendar-title {
            font-weight: bold;
            color: black;
        }
        .calendar-nav {
            display: flex;
            gap: 10px;
        }
        .calendar-nav button {
            background: black;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 2px 8px;
            cursor: pointer;
        }
        .calendar-weekdays {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            text-align: center;
            font-weight: bold;
            margin-bottom: 5px;
            color: black;
        }
        .calendar-days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
            font-size: small;
        }
        .calendar-day {
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            cursor: pointer;
            background-color: white;
            color: black;
        }
        .calendar-day:hover {
            background-color: #f0f0f0;
        }
        .available {
            background-color: white;
            color: black;
        }
        .unavailable {
            background-color: #ffdddd;
            color: #aaa;
            cursor: not-allowed;
        }
        .selected {
            background-color: #7B172E;
            color: white;
        }
        .other-month {
            color: #ccc;
        }
        .no-facility {
            background-color: white;
            color: black;
            cursor: default;
        }
        .hidden {
            display: none;
        }

        .date-time-group {
            background-color: #f8f8f8;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ddd;
            position: relative;
        }

        .date-time-group:not(:last-child) {
            margin-bottom: 15px;
        }

        .consecutive-date {
            background-color: #e6f7ff;
        }

        .remove-date {
            position: absolute;
            top: 5px;
            right: 5px;
            background: #ff4444;
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .remove-date:hover {
            background: #cc0000;
        }

        .multiple-date {
            background-color: #e6ffe6;
        }

        #calendar-message {
            transition: color 0.3s ease;
        }
        .equipment-container {
            margin-top: 15px;
            display: none;
        }

        .equipment-date-group {
            background-color: #f8f8f8;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #ddd;
            margin-bottom: 15px;
        }

        .equipment-row {
            display: flex;
            gap: 15px;
            margin-top: 10px;
            align-items: center;
        }

        .equipment-row select {
            flex: 1;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .add-equipment {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }

        .add-equipment:hover {
            background-color: #45a049;
        }

        .remove-equipment {
            color: black;
            border: none;
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
    </style>
</head>

<body class="bg-gray-50 m-0 p-0">
@include('partials.navbar')

<div id="dashboard" class="flex items-center justify-center min-h-[calc(100vh)] bg-cover bg-center bg-no-repeat bg-fixed backdrop-blur-sm"
     style="background-image: url('{{ asset('pictures/cebuUserBackground.jpg') }}'); background-blend-mode: overlay;">
    <div class="px-12 py-8 flex mt-28 justify-center flex-col bg-white h-auto w-full max-w-2xl rounded-lg shadow-lg">
        <div class="text-center">
            <div class="mb-4 flex justify-center">
                <img
                    src="{{ asset('pictures/uplogo-removebg-preview.png') }}"
                    alt="UP Cebu Logo"
                    class="h-[110px] bg-white rounded-full w-auto object-contain"
                >
            </div>
            <h1 class="text-2xl font-bold leading-tight" style="color : #7B172E; text-shadow: 0 0 10px rgba(255,255,255,0.8), 0 0 10px rgba(255,255,255,0.5);">
                Online Reservation Form Use of Facilities<br>
                and Other Equipment
            </h1>
        </div>

        <div class="mb-6 mt-6">
            <label class="block text-gray-700 font-medium mb-2">Transaction Date</label>
            <input id="transaction-date" readonly class="w-1/2 px-4 py-2 border-2 border-gray-600 rounded-lg bg-gray-100">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">Reservation Type:</label>
            <div class="flex flex-row justify-between w-1/2">
                <label class="inline-flex items-center">
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
                    <select id="facility-select" class="w-[270px] px-3 py-2 border-2 border-gray-600 rounded-lg">
                        <option value="">Select a facility</option>
                        @foreach($facilities as $facility)
                            <option value="{{ $facility->facility_id }}" data-name="{{ $facility->facility_name }}">
                                {{ $facility->facility_name }}
                            </option>
                        @endforeach
                    </select>
                    <div id="calendar-message" class="text-sm mt-2">
                        Please select a facility to see availability
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


        <div class="mt-6 p-4 border-2 border-gray-700 rounded-md">
            <label class="block text-gray-700 font-medium mb-2">State the purpose of your request which includes the type of
                activity (ex. API), whether free use, in partnership with outside org., fund source, etc.)</label>
            <input type="text" placeholder="Enter your answer here." class="border-2 w-full p-4">
        </div>

            <div class="mb-4 mt-8 p-4 border-2 border-gray-700 rounded-md">
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
    </div>


</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Calendar variables
        let currentDate = new Date();
        let selectedDate = null;
        let selectedFacility = null;
        let reservationType = null;
        let daysCount = null;
        let selectedMultipleDates = [];


        // DOM elements
        const facilitySelect = document.getElementById('facility-select');
        const calendarMessage = document.getElementById('calendar-message');
        const calendarTitle = document.getElementById('calendar-title');
        const calendarDays = document.getElementById('calendar-days');
        const prevMonthBtn = document.getElementById('prev-month');
        const nextMonthBtn = document.getElementById('next-month');
        const selectedDateInput = document.getElementById('selected-date');
        const dateTimeContainer = document.getElementById('date-time-container');
        const multipleDatesContainer = document.getElementById('multiple-dates-container');
        const consecutiveOptions = document.getElementById('consecutive-options');
        const daysCountSelect = document.getElementById('days-count');
        const reservationTypeRadios = document.querySelectorAll('input[name="reservation_type"]');
        const maxMultipleDates = 3;
        const minMultipleDates = 2;
        const transactionDateInput = document.getElementById('transaction-date');
        const today = new Date();
        transactionDateInput.value = formatDateForDisplay(today);
        const equipmentRadios = document.querySelectorAll('input[name="need_equipment"]');
        const equipmentContainer = document.getElementById('equipment-container');

        // Initialize calendar
        renderCalendar();

        // Handle reservation type changes
        reservationTypeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                reservationType = this.value;
                selectedDate = null;
                selectedMultipleDates = [];

                // Reset selections
                const selectedDays = document.querySelectorAll('.calendar-day.selected, .calendar-day.consecutive-date, .calendar-day.multiple-date');
                selectedDays.forEach(day => {
                    day.classList.remove('selected', 'consecutive-date', 'multiple-date');
                });

                // Hide/show options
                if (this.value === 'consecutive') {
                    consecutiveOptions.classList.remove('hidden');
                    daysCountSelect.value = '';
                } else {
                    consecutiveOptions.classList.add('hidden');
                    daysCount = null;
                }

                // Clear date inputs
                multipleDatesContainer.innerHTML = '';
                dateTimeContainer.classList.add('hidden');

                // Re-render calendar with new availability
                if (facilitySelect.value) {
                    renderCalendar();
                }
            });
        });

        // Handle consecutive days selection
        daysCountSelect.addEventListener('change', function() {
            daysCount = this.value ? parseInt(this.value) : null;
            multipleDatesContainer.innerHTML = '';
            dateTimeContainer.classList.add('hidden');

            // Re-render calendar with new availability
            if (facilitySelect.value) {
                renderCalendar();
            }
        });

        // Update calendar when facility is selected
        facilitySelect.addEventListener('change', async function() {
            selectedFacility = this.value;
            selectedDate = null;
            selectedMultipleDates = [];

            if (this.value) {
                const facilityName = this.options[this.selectedIndex].dataset.name;
                calendarMessage.textContent = `Checking availability for ${facilityName}...`;
                calendarMessage.style.color = "#ff0038";

                // Reset calendar to current month
                currentDate = new Date();
                await renderCalendar();

                calendarMessage.textContent = "Please choose the available date for your reservation";
            } else {
                calendarMessage.textContent = "Please select a facility to see availability";
                calendarMessage.style.color = "";
                renderCalendar();
            }
        });

        // Navigation buttons
        prevMonthBtn.addEventListener('click', async function() {
            currentDate.setMonth(currentDate.getMonth() - 1);
            await renderCalendar();
        });

        nextMonthBtn.addEventListener('click', async function() {
            currentDate.setMonth(currentDate.getMonth() + 1);
            await renderCalendar();
        });

        // Render the calendar
        // Replace the renderCalendar function with this:
        async function renderCalendar() {
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth() + 1;

            // Show loading state
            calendarDays.innerHTML = '<div class="col-span-7 py-4 text-center">Loading availability...</div>';

            try {
                // Fetch availability data from server
                const response = await fetch(`/api/availability/${selectedFacility}?month=${month}&year=${year}&type=${reservationType}&days=${daysCount}`);
                const data = await response.json();

                // Clear calendar
                calendarDays.innerHTML = '';

                // Set calendar title
                calendarTitle.textContent = `${new Intl.DateTimeFormat('en-US', { month: 'long' }).format(new Date(year, month - 1))} ${year}`;

                // Get first day of month and total days in month
                const firstDay = new Date(year, month - 1, 1).getDay();
                const daysInMonth = new Date(year, month, 0).getDate();

                // Get days from previous month
                const prevMonthDays = new Date(year, month - 1, 0).getDate();

                const today = new Date();
                today.setHours(0, 0, 0, 0);

                // Previous month days
                for (let i = firstDay - 1; i >= 0; i--) {
                    const dayElement = document.createElement('div');
                    dayElement.className = 'calendar-day other-month';
                    dayElement.textContent = prevMonthDays - i;
                    calendarDays.appendChild(dayElement);
                }

                // Current month days
                for (let i = 1; i <= daysInMonth; i++) {
                    const date = new Date(year, month - 1, i);
                    const dateString = formatDate(date);

                    // Check if date is in the past
                    const isPast = date < today;

                    // Check if date is unavailable
                    const isUnavailable = data.unavailable.includes(dateString);

                    // Check if date is start of consecutive range
                    let isConsecutiveStart = false;
                    if (reservationType === 'consecutive' && daysCount) {
                        isConsecutiveStart = data.consecutive.some(range => range[0] === dateString);
                    }

                    // Set class based on availability
                    let dayClass = 'calendar-day ';
                    if (!selectedFacility) {
                        dayClass += 'no-facility';
                    } else if (isPast) {
                        dayClass += 'unavailable';
                    } else if (isUnavailable) {
                        dayClass += 'unavailable';
                    } else if (isConsecutiveStart) {
                        dayClass += 'consecutive-date';
                    } else {
                        dayClass += 'available';
                    }

                    const dayElement = document.createElement('div');
                    dayElement.className = dayClass;
                    dayElement.textContent = i;
                    dayElement.dataset.date = dateString;

                    // Only make clickable if available and not in past
                    if (selectedFacility && !isPast && (isConsecutiveStart || (reservationType !== 'consecutive' && !isUnavailable))) {
                        dayElement.addEventListener('click', function() {
                            handleDateSelection(dateString);
                        });
                    } else {
                        dayElement.style.cursor = 'default';
                    }

                    calendarDays.appendChild(dayElement);
                }

                // Next month days (to fill the grid)
                const totalCells = Math.ceil((firstDay + daysInMonth) / 7) * 7;
                const nextMonthDays = totalCells - (firstDay + daysInMonth);

                for (let i = 1; i <= nextMonthDays; i++) {
                    const dayElement = document.createElement('div');
                    dayElement.className = 'calendar-day other-month';
                    dayElement.textContent = i;
                    calendarDays.appendChild(dayElement);
                }
            } catch (error) {
                console.error('Error loading calendar:', error);
                calendarDays.innerHTML = '<div class="col-span-7 py-4 text-center text-red-500">Error loading availability data</div>';
            }
        }

        // Handle date selection
        function handleDateSelection(dateString) {
            // Clear previous selections
            const selectedDays = document.querySelectorAll('.calendar-day.selected, .calendar-day.consecutive-date, .calendar-day.multiple-date');
            selectedDays.forEach(day => {
                day.classList.remove('selected', 'consecutive-date', 'multiple-date');
            });

            // Mark the selected date(s)
            if (reservationType === 'single') {
                const dayElement = document.querySelector(`.calendar-day[data-date="${dateString}"]`);
                dayElement.classList.add('selected');
                selectedDate = dateString;
                updateDateTimeInputs(dateString);
            }
            else if (reservationType === 'consecutive' && daysCount) {
                // For consecutive dates, we need to generate the full range
                const startDate = new Date(dateString);
                const dateRange = [dateString];

                for (let i = 1; i < daysCount; i++) {
                    const nextDate = new Date(startDate);
                    nextDate.setDate(startDate.getDate() + i);
                    dateRange.push(formatDate(nextDate));
                }

                // Mark all dates in the range
                dateRange.forEach(dateStr => {
                    const dayElement = document.querySelector(`.calendar-day[data-date="${dateStr}"]`);
                    if (dayElement) {
                        dayElement.classList.add('consecutive-date');
                    }
                });

                // Mark the first date as selected
                const firstDayElement = document.querySelector(`.calendar-day[data-date="${dateString}"]`);
                firstDayElement.classList.add('selected');
                selectedDate = dateString;

                // Update both date-time inputs and equipment inputs
                updateDateTimeInputs(dateRange);
            }
            else if (reservationType === 'multiple') {
                const dayElement = document.querySelector(`.calendar-day[data-date="${dateString}"]`);

                if (dayElement.classList.contains('multiple-date')) {
                    dayElement.classList.remove('multiple-date');
                    selectedMultipleDates = selectedMultipleDates.filter(d => d !== dateString);
                } else {
                    if (selectedMultipleDates.length < maxMultipleDates) {
                        dayElement.classList.add('multiple-date');
                        selectedMultipleDates.push(dateString);
                    }
                }

                updateDateTimeInputs(selectedMultipleDates);
            }

            // Update equipment inputs if "Yes" is selected
            if (document.querySelector('input[name="need_equipment"]:checked').value === 'yes') {
                updateEquipmentInputs();
            }
        }

        // Update date inputs based on selection
        function updateDateTimeInputs(dates) {
            multipleDatesContainer.innerHTML = '';

            if ((reservationType === 'single' && !dates) ||
                (reservationType === 'consecutive' && (!dates || dates.length === 0)) ||
                (reservationType === 'multiple' && dates.length < minMultipleDates)) {
                dateTimeContainer.classList.add('hidden');
                return;
            }

            dateTimeContainer.classList.remove('hidden');

            if (typeof dates === 'string') {
                dates = [dates];
            }

            dates.forEach((dateStr, index) => {
                const date = new Date(dateStr);
                const formattedDate = formatDateForDisplay(date);

                const group = document.createElement('div');
                group.className = 'flex flex-row justify-between mb-4 date-time-group';
                group.dataset.date = dateStr;

                let label = '';
                if (reservationType === 'consecutive') {
                    label = index === 0 ? 'Start Date' :
                        (index === dates.length - 1 ? 'End Date' : `Day ${index + 1}`);
                } else {
                    label = index === 0 ? 'Start Date' : 'Next Date';
                }

                group.innerHTML = `
            <button class="remove-date" ${reservationType === 'multiple' ? '' : 'style="display: none;"'}>X</button>
            <div class="mb-6 mt-6">
                <label class="block text-gray-700 font-medium mb-2">${label}</label>
                <input type="text" readonly class="start-date w-[180px] px-3 py-2 border-2 border-gray-600 rounded-lg bg-gray-100" value="${formattedDate}">
            </div>
            <div class="mb-6 mt-6">
                <label class="block text-gray-700 font-medium mb-2">Time From:</label>
                <select class="time-from w-[160px] px-3 py-2 border-2 border-gray-600 rounded-lg">
                    ${generateTimeOptions()}
                </select>
            </div>
            <div class="mb-6 mt-6">
                <label class="block text-gray-700 font-medium mb-2">Time To:</label>
                <select class="time-to w-[160px] px-3 py-2 border-2 border-gray-600 rounded-lg">
                    ${generateTimeOptions()}
                </select>
            </div>
        `;

                if (reservationType === 'multiple') {
                    const removeBtn = group.querySelector('.remove-date');
                    removeBtn.addEventListener('click', function() {
                        const dateToRemove = group.dataset.date;
                        selectedMultipleDates = selectedMultipleDates.filter(d => d !== dateToRemove);
                        const dayElement = document.querySelector(`.calendar-day[data-date="${dateToRemove}"]`);
                        if (dayElement) {
                            dayElement.classList.remove('multiple-date');
                        }
                        updateDateTimeInputs(selectedMultipleDates);
                    });
                }

                multipleDatesContainer.appendChild(group);
            });
        }

        // Helper function to format date as YYYY-MM-DD
        function formatDate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        // Helper function to format date for display (MM/DD/YYYY)
        function formatDateForDisplay(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${month}/${day}/${year}`;
        }

        // Helper function to generate time options
        function generateTimeOptions() {
            let options = '';
            for (let hour = 8; hour <= 17; hour++) {
                for (let minute = 0; minute < 60; minute += 30) {
                    const time = `${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}`;
                    options += `<option value="${time}">${time}</option>`;
                }
            }
            return options;
        }

        // Sample equipment data (in real app, this would come from backend)
        const equipmentOptions = [
            { id: 1, name: "Projector" },
            { id: 2, name: "Sound System" },
            { id: 3, name: "Microphone" },
            { id: 4, name: "Chairs" },
            { id: 5, name: "Tables" }
        ];

        // Handle equipment radio button changes
        // Handle equipment radio button changes
        equipmentRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'yes') {
                    equipmentContainer.style.display = 'block';
                    // Only update if we have selected dates
                    if ((reservationType === 'single' && selectedDate) ||
                        (reservationType === 'consecutive' && selectedDate && daysCount) ||
                        (reservationType === 'multiple' && selectedMultipleDates.length > 0)) {
                        updateEquipmentInputs();
                    }
                } else {
                    equipmentContainer.style.display = 'none';
                }
            });
        });

        // Function to update equipment inputs based on selected dates
        function updateEquipmentInputs() {
            equipmentContainer.innerHTML = '';

            // Get the selected dates based on reservation type
            let dates = [];

            if (reservationType === 'single' && selectedDate) {
                dates = [selectedDate];
            }
            else if (reservationType === 'consecutive' && daysCount && selectedDate) {
                // For consecutive, we need to generate all dates in the range
                const startDate = new Date(selectedDate);
                dates = [selectedDate]; // Start with the selected date

                // Add subsequent dates based on daysCount
                for (let i = 1; i < daysCount; i++) {
                    const nextDate = new Date(startDate);
                    nextDate.setDate(startDate.getDate() + i);
                    dates.push(formatDate(nextDate));
                }
            }
            else if (reservationType === 'multiple' && selectedMultipleDates.length > 0) {
                dates = selectedMultipleDates;
            }

            // If no dates selected, show message
            if (dates.length === 0) {
                equipmentContainer.innerHTML = '<p>Please select dates first</p>';
                return;
            }

            // Create equipment inputs for each date
            dates.forEach((dateStr, index) => {
                const date = new Date(dateStr);
                const formattedDate = formatDateForDisplay(date);

                const dateGroup = document.createElement('div');
                dateGroup.className = 'equipment-date-group';
                dateGroup.dataset.date = dateStr;

                // Create appropriate label for consecutive dates
                let dateLabel = formattedDate;
                if (reservationType === 'consecutive') {
                    if (index === 0) {
                        dateLabel = `Start Date: ${formattedDate}`;
                    } else if (index === dates.length - 1) {
                        dateLabel = `End Date: ${formattedDate}`;
                    } else {
                        dateLabel = `Day ${index + 1}: ${formattedDate}`;
                    }
                }

                dateGroup.innerHTML = `
            <div class="font-medium">${dateLabel}</div>
            <div class="equipment-rows">
                <div class="equipment-row">
                    <select class="equipment-select">
                        <option value="">Select Equipment</option>
                        ${equipmentOptions.map(opt =>
                    `<option value="${opt.id}">${opt.name}</option>`
                ).join('')}
                    </select>
                    <select class="units-select">
                        <option value="1">1 unit</option>
                        <option value="2">2 units</option>
                        <option value="3">3 units</option>
                        <option value="4">4 units</option>
                        <option value="5">5 units</option>
                    </select>
                    <button class="remove-equipment" style="display: none;">X</button>
                </div>
            </div>
            <button class="add-equipment">Add Equipment</button>
        `;

                // Add event for adding more equipment
                const addBtn = dateGroup.querySelector('.add-equipment');
                addBtn.addEventListener('click', function() {
                    addEquipmentRow(dateGroup);
                });

                equipmentContainer.appendChild(dateGroup);
            });
        }

        function addEquipmentRow(dateGroup) {
            const equipmentRows = dateGroup.querySelector('.equipment-rows');
            const newRow = document.createElement('div');
            newRow.className = 'equipment-row';

            newRow.innerHTML = `
                <select class="equipment-select">
                    <option value="">Select Equipment</option>
                    ${equipmentOptions.map(opt =>
                `<option value="${opt.id}">${opt.name}</option>`
            ).join('')}
                </select>
                <select class="units-select">
                    <option value="1">1 unit</option>
                    <option value="2">2 units</option>
                    <option value="3">3 units</option>
                    <option value="4">4 units</option>
                    <option value="5">5 units</option>
                </select>
                <button class="remove-equipment flex justify-center items-center">&times;</button>
            `;

            // Add remove event
            const removeBtn = newRow.querySelector('.remove-equipment');
            removeBtn.addEventListener('click', function() {
                equipmentRows.removeChild(newRow);
            });

            equipmentRows.appendChild(newRow);
        }

        // Update equipment inputs whenever dates change
        // Add these lines to your existing handleDateSelection function:
        // if (document.querySelector('input[name="need_equipment"]:checked').value === 'yes') {
        //     updateEquipmentInputs();
        // }
    });
</script>

</body>
</html>

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
            <div>
                <label class="block text-gray-700 font-medium mb-2">Facilities</label>
                <select id="facility-select" class="w-[270px] px-3 py-2 border-2 border-gray-600 rounded-lg">
                    <option value="">Select a facility</option>
                    <option value="auditorium">Auditorium</option>
                    <option value="classroom">Classroom</option>
                    <option value="sports-complex">Sports Complex</option>
                </select>
                <div id="calendar-message" class="text-sm mt-2">
                    Please select a facility to see availability
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
            <label class="block text-gray-700 font-medium mb-2">Do you need other equipment? (Please see the approved rental rates and inclusive equipment use package for each venue. Subject to rental computation.)</label>
            <div class="flex flex-row gap-6">
                <label class="inline-flex items-center">
                    <input type="radio" name="yes" value="yes" class="form-radio h-5 w-5 text-blue-600">
                    <span class="ml-2 text-gray-700">Yes</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" name="no" value="no" class="form-radio h-5 w-5 text-blue-600">
                    <span class="ml-2 text-gray-700">No</span>
                </label>
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

        // Sample available dates (in real app, this would come from backend)
        const availableDates = {
            'auditorium': {
                single: ['2025-07-16', '2025-07-17', '2025-07-20', '2025-07-22', '2025-07-27'],
                consecutive: [
                    ['2025-07-16', '2025-07-17'], // 2 consecutive days
                    ['2025-07-19', '2025-07-20', '2025-07-21'], // 3 consecutive days
                    ['2025-07-27', '2025-07-28'] // 2 consecutive days
                ]
            },
            'classroom': {
                single: ['2025-07-15', '2025-07-16', '2025-07-17', '2025-07-23', '2025-07-30'],
                consecutive: [
                    ['2025-07-15', '2025-07-16'],
                    ['2025-07-22', '2025-07-23', '2025-07-24']
                ]
            },
            'sports-complex': {
                single: ['2025-07-14', '2025-07-16', '2025-07-24', '2025-07-31'],
                consecutive: [
                    ['2025-07-14', '2025-07-15'],
                    ['2025-07-23', '2025-07-24', '2025-07-25']
                ]
            }
        };

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
        facilitySelect.addEventListener('change', function() {
            selectedFacility = this.value;
            selectedDate = null;
            selectedMultipleDates = [];

            if (this.value) {
                calendarMessage.textContent = "Please choose the available date for your reservation";
                calendarMessage.style.color = "#ff0038"; // Maroon color
                renderCalendar();
            } else {
                calendarMessage.textContent = "Please select a facility to see availability";
                calendarMessage.style.color = ""; // Reset to default
                renderCalendar();
            }
        });

        // Navigation buttons
        prevMonthBtn.addEventListener('click', function() {
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderCalendar();
        });

        nextMonthBtn.addEventListener('click', function() {
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderCalendar();
        });

        // Render the calendar
        function renderCalendar() {
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();

            // Set calendar title
            calendarTitle.textContent = `${new Intl.DateTimeFormat('en-US', { month: 'long' }).format(currentDate)} ${year}`;

            // Get first day of month and total days in month
            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();

            // Get days from previous month
            const prevMonthDays = new Date(year, month, 0).getDate();

            const today = new Date();
            today.setHours(0, 0, 0, 0); // Normalize to midnight

            const tomorrow = new Date(today);
            tomorrow.setDate(tomorrow.getDate() + 1);

            const dayAfterTomorrow = new Date(today);
            dayAfterTomorrow.setDate(dayAfterTomorrow.getDate() + 2);

            // Clear calendar
            calendarDays.innerHTML = '';

            // Previous month days
            for (let i = firstDay - 1; i >= 0; i--) {
                const dayElement = document.createElement('div');
                dayElement.className = 'calendar-day other-month';
                dayElement.textContent = prevMonthDays - i;
                calendarDays.appendChild(dayElement);
            }

            // Current month days
            let facilityAvailableDates = [];
            let consecutiveStartDates = [];

            if (selectedFacility) {
                if (reservationType === 'single') {
                    facilityAvailableDates = availableDates[selectedFacility]?.single || [];
                } else if (reservationType === 'consecutive' && daysCount) {
                    // Get all consecutive date ranges that match the selected days count
                    const allConsecutive = availableDates[selectedFacility]?.consecutive || [];
                    const matchingConsecutive = allConsecutive.filter(range => range.length === daysCount);

                    // Extract just the start dates of these ranges
                    consecutiveStartDates = matchingConsecutive.map(range => range[0]);
                }
            }

            for (let i = 1; i <= daysInMonth; i++) {
                const date = new Date(year, month, i);
                const dateString = formatDate(date);

                // Check if date is today, tomorrow, or in the past
                const isTooSoon = date < dayAfterTomorrow;

                let isAvailable = false;
                let isConsecutiveStart = false;
                let isMultipleAvailable = false;

                if (selectedFacility && !isTooSoon) {
                    if (reservationType === 'single') {
                        isAvailable = availableDates[selectedFacility]?.single.includes(dateString);
                    } else if (reservationType === 'consecutive' && daysCount) {
                        isConsecutiveStart = availableDates[selectedFacility]?.consecutive.some(range =>
                            range.length === daysCount && range[0] === dateString
                        );
                        isAvailable = isConsecutiveStart;
                    } else if (reservationType === 'multiple') {
                        isAvailable = availableDates[selectedFacility]?.single.includes(dateString);
                        isMultipleAvailable = isAvailable;
                    }
                }

                // Set class based on availability and date rules
                let dayClass = 'calendar-day ';
                if (!selectedFacility) {
                    dayClass += 'no-facility';
                } else if (isTooSoon) {
                    dayClass += 'unavailable';
                } else {
                    dayClass += isAvailable ?
                        (isConsecutiveStart ? 'consecutive-date' :
                            (isMultipleAvailable ? 'available' : 'available')) :
                        'unavailable';
                }

                const dayElement = document.createElement('div');
                dayElement.className = dayClass;
                dayElement.textContent = i;
                dayElement.dataset.date = dateString;

                // Highlight if selected
                if (reservationType === 'single' && selectedDate === dateString) {
                    dayElement.classList.add('selected');
                } else if (reservationType === 'consecutive' && daysCount) {
                    const allConsecutive = availableDates[selectedFacility]?.consecutive || [];
                    const selectedRange = allConsecutive.find(range =>
                        range.length === daysCount && range[0] === selectedDate
                    );
                    if (selectedRange && selectedRange.includes(dateString)) {
                        dayElement.classList.add('consecutive-date');
                        if (dateString === selectedDate) {
                            dayElement.classList.add('selected');
                        }
                    }
                } else if (reservationType === 'multiple' && selectedMultipleDates.includes(dateString)) {
                    dayElement.classList.add('multiple-date');
                }

                if (selectedFacility && isAvailable) {
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
        }

        // Handle date selection
        function handleDateSelection(dateString) {
            // Clear previous selections
            const selectedDays = document.querySelectorAll('.calendar-day.selected, .calendar-day.consecutive-date');
            selectedDays.forEach(day => {
                day.classList.remove('selected', 'consecutive-date');
            });

            // Mark the selected date(s)
            if (reservationType === 'single') {
                // For single, just select one date
                const dayElement = document.querySelector(`.calendar-day[data-date="${dateString}"]`);
                dayElement.classList.add('selected');
                selectedDate = dateString;

                // Create or update the date inputs
                updateDateTimeInputs(dateString);
            }
            else if (reservationType === 'consecutive' && daysCount) {
                // For consecutive, find the range and select all dates in it
                const allConsecutive = availableDates[selectedFacility]?.consecutive || [];
                const selectedRange = allConsecutive.find(range =>
                    range.length === daysCount && range[0] === dateString
                );

                if (selectedRange) {
                    // Mark all dates in the range
                    selectedRange.forEach(dateStr => {
                        const dayElement = document.querySelector(`.calendar-day[data-date="${dateStr}"]`);
                        if (dayElement) {
                            dayElement.classList.add('consecutive-date');
                        }
                    });

                    // Mark the first date as selected
                    const firstDayElement = document.querySelector(`.calendar-day[data-date="${dateString}"]`);
                    firstDayElement.classList.add('selected');
                    selectedDate = dateString;

                    // Create or update the date inputs
                    updateDateTimeInputs(selectedRange);
                }
            }
            else if (reservationType === 'multiple') {
                const dayElement = document.querySelector(`.calendar-day[data-date="${dateString}"]`);

                // Toggle selection
                if (dayElement.classList.contains('multiple-date')) {
                    // Remove from selection
                    dayElement.classList.remove('multiple-date');
                    selectedMultipleDates = selectedMultipleDates.filter(d => d !== dateString);
                } else {
                    // Add to selection if we haven't reached max
                    if (selectedMultipleDates.length < maxMultipleDates) {
                        dayElement.classList.add('multiple-date');
                        selectedMultipleDates.push(dateString);
                    }
                }

                // Update the date inputs
                updateDateTimeInputs(selectedMultipleDates);
            }
        }

        // Update date inputs based on selection
        function updateDateTimeInputs(dates) {
            multipleDatesContainer.innerHTML = '';

            if ((reservationType === 'single' && !dates.length) ||
                (reservationType === 'consecutive' && !dates.length) ||
                (reservationType === 'multiple' && dates.length < minMultipleDates)) {
                dateTimeContainer.classList.add('hidden');
                return;
            }

            dateTimeContainer.classList.remove('hidden');

            // Convert single date to array for consistent handling
            if (typeof dates === 'string') {
                dates = [dates];
            }

            dates.forEach((dateStr, index) => {
                const date = new Date(dateStr);
                const formattedDate = formatDateForDisplay(date);

                const group = document.createElement('div');
                group.className = 'flex flex-row justify-between mb-4 date-time-group';
                group.dataset.date = dateStr;

                group.innerHTML = `
                    <button class="remove-date" ${reservationType === 'multiple' ? '' : 'style="display: none;"'}>X</button>
                    <div class="mb-6 mt-6">
                        <label class="block text-gray-700 font-medium mb-2">${index === 0 ? 'Start' : 'Next'} Date:</label>
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

                // Add remove handler for multiple dates
                if (reservationType === 'multiple') {
                    const removeBtn = group.querySelector('.remove-date');
                    removeBtn.addEventListener('click', function() {
                        const dateToRemove = group.dataset.date;
                        selectedMultipleDates = selectedMultipleDates.filter(d => d !== dateToRemove);

                        // Update calendar
                        const dayElement = document.querySelector(`.calendar-day[data-date="${dateToRemove}"]`);
                        if (dayElement) {
                            dayElement.classList.remove('multiple-date');
                        }

                        // Update inputs
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
    });
</script>

</body>
</html>

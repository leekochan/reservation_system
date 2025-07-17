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
    </style>
</head>

<body class="bg-gray-50 m-0 p-0">
@include('partials.navbar')

<div id="dashboard" class="flex items-center justify-center min-h-[calc(100vh)] bg-cover bg-center bg-no-repeat bg-fixed backdrop-blur-sm"
     style="background-image: url('{{ asset('pictures/cebuUserBackground.jpg') }}'); background-blend-mode: overlay;">
    <div class="px-12 py-8 flex mt-28 justify-center flex-col bg-white h-auto w-full max-w-3xl rounded-lg shadow-lg">
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
            <input type="date" class="w-1/2 px-4 py-2 border-2 border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">Reservation Type:</label>
            <div class="flex flex-row justify-between w-1/2">
                <label class="inline-flex items-center">
                    <input type="radio" name="reservation_type" class="form-radio h-5 w-5 text-blue-600">
                    <span class="ml-2 text-gray-700">Single</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" name="reservation_type" class="form-radio h-5 w-5 text-blue-600">
                    <span class="ml-2 text-gray-700">Recurring</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" name="reservation_type" class="form-radio h-5 w-5 text-blue-600">
                    <span class="ml-2 text-gray-700">Multiple</span>
                </label>
            </div>
        </div>

        <div class="mb-4 flex flex-row gap-8">
            <div>
                <label class="block text-gray-700 font-medium mb-2">Facilities</label>
                <select id="facility-select" class="w-[270px] px-3 py-2 border-2 border-gray-600 rounded-lg">
                    <option value="">Select a facility</option>
                    <option value="auditorium">Auditorium</option>
                    <option value="classroom">Classroom</option>
                    <option value="sports-complex">Sports Complex</option>
                    <!-- Add more facilities as needed -->
                </select>
            </div>

            <div id="calendar-container" class="w-full">
                <label class="block text-gray-700 font-medium mb-2">Available Dates</label>
                <div class="calendar">
                    <div class="calendar-header">
                        <button id="prev-month">&lt;</button>
                        <div id="calendar-title" class="calendar-title">Month Year</div>
                        <button id="next-month">&gt;</button>
                    </div>
                    <div class="calendar-weekdays">
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
                <div id="calendar-message" class="text-sm text-gray-500 mt-2">
                    Please select a facility to see availability
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Calendar variables
        let currentDate = new Date();
        let selectedDate = null;

        // Facility select element
        const facilitySelect = document.getElementById('facility-select');
        const calendarMessage = document.getElementById('calendar-message');

        // Calendar elements
        const calendarTitle = document.getElementById('calendar-title');
        const calendarDays = document.getElementById('calendar-days');
        const prevMonthBtn = document.getElementById('prev-month');
        const nextMonthBtn = document.getElementById('next-month');
        const selectedDateInput = document.getElementById('selected-date');

        // Sample available dates (in real app, this would come from backend)
        const availableDates = {
            'auditorium': ['2025-07-16', '2025-07-17', '2025-07-20', '2025-07-22', '2025-07-27'],
            'classroom': ['2025-07-15', '2025-07-16', '2025-07-17', '2025-07-23', '2025-07-30'],
            'sports-complex': ['2025-07-14', '2025-07-16', '2025-07-24', '2025-07-31']
        };

        // Initialize calendar
        renderCalendar();

        // Update calendar when facility is selected
        facilitySelect.addEventListener('change', function() {
            if (this.value) {
                calendarMessage.textContent = "Available dates are shown in white, unavailable in red";
                renderCalendar();
            } else {
                calendarMessage.textContent = "Please select a facility to see availability";
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
            const selectedFacility = facilitySelect.value;

            // Set calendar title
            calendarTitle.textContent = `${new Intl.DateTimeFormat('en-US', { month: 'long' }).format(currentDate)} ${year}`;

            // Get first day of month and total days in month
            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();

            // Get days from previous month
            const prevMonthDays = new Date(year, month, 0).getDate();

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
            const facilityAvailableDates = selectedFacility ? availableDates[selectedFacility] || [] : [];

            for (let i = 1; i <= daysInMonth; i++) {
                const date = new Date(year, month, i);
                const dateString = formatDate(date);
                const isAvailable = selectedFacility ? facilityAvailableDates.includes(dateString) : false;

                const dayElement = document.createElement('div');
                dayElement.className = `calendar-day ${selectedFacility ? (isAvailable ? 'available' : 'unavailable') : 'no-facility'}`;
                dayElement.textContent = i;

                if (selectedDate === dateString) {
                    dayElement.classList.add('selected');
                }

                if (selectedFacility && isAvailable) {
                    dayElement.addEventListener('click', function() {
                        // Remove previous selection
                        const selected = document.querySelector('.calendar-day.selected');
                        if (selected) selected.classList.remove('selected');

                        // Add new selection
                        dayElement.classList.add('selected');
                        selectedDate = dateString;
                        selectedDateInput.value = dateString;
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

        // Helper function to format date as YYYY-MM-DD
        function formatDate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }
    });
</script>

</body>
</html>

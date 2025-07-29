<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Calendar variables
        let currentDate = new Date();
        let selectedDate = null;
        let selectedFacility = null;
        let reservationType = null;
        let daysCount = null;
        let selectedMultipleDates = [];
        let selectedSignatureFile = null;

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
        const personalEquipmentRadios = document.querySelectorAll('input[name="personal_equipment"]');
        const personalEquipmentInput = document.getElementById('personal-equipment-input');

        // Initialize calendar
        renderCalendar();
        
        // Initialize real-time validation
        setupRealTimeValidation();

        function initSignatureUpload() {
            const signatureUpload = document.getElementById('signature-upload');
            const signaturePreview = document.getElementById('signature-preview');
            const signaturePreviewImg = document.getElementById('signature-preview-img');

            signatureUpload.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Validate file type
                    if (!file.type.match('image.*')) {
                        alert('Please select an image file for your signature.');
                        this.value = '';
                        signaturePreview.classList.add('hidden');
                        return;
                    }

                    // Validate file size (max 5MB)
                    if (file.size > 5 * 1024 * 1024) {
                        alert('Please select an image file smaller than 5MB.');
                        this.value = '';
                        signaturePreview.classList.add('hidden');
                        return;
                    }

                    selectedSignatureFile = file;

                    // Show preview
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        signaturePreviewImg.src = e.target.result;
                        signaturePreview.classList.remove('hidden');
                    };
                    reader.readAsDataURL(file);
                } else {
                    selectedSignatureFile = null;
                    signaturePreview.classList.add('hidden');
                }
            });
        }

        personalEquipmentRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'yes') {
                    personalEquipmentInput.classList.remove('hidden');
                } else {
                    personalEquipmentInput.classList.add('hidden');
                }
            });
        });

        // Handle reservation type changes
        reservationTypeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                reservationType = this.value;
                selectedDate = null;
                selectedMultipleDates = [];

                // Reset selections but preserve pending states
                const selectedDays = document.querySelectorAll('.calendar-day.selected, .calendar-day.consecutive-date, .calendar-day.multiple-date');
                selectedDays.forEach(day => {
                    day.classList.remove('selected', 'consecutive-date', 'multiple-date');
                    // Re-add available class if it was removed and not pending/unavailable
                    if (!day.classList.contains('pending') && !day.classList.contains('unavailable') && !day.classList.contains('no-facility') && !day.classList.contains('other-month')) {
                        day.classList.add('available');
                    }
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

                // Re-render calendar with new availability and update message
                if (facilitySelect.value) {
                    renderCalendar();
                    calendarMessage.textContent = "Please choose the available date for your reservation";
                    calendarMessage.style.color = "";
                } else {
                    calendarMessage.textContent = "Please select a facility to see availability";
                    calendarMessage.style.color = "";
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

                if (reservationType) {
                    calendarMessage.textContent = "Please choose the available date for your reservation";
                } else {
                    calendarMessage.textContent = "Please select a reservation type first";
                    calendarMessage.style.color = "#ff6600";
                }
            } else {
                calendarMessage.textContent = "Please select a reservation type and facility to see availability";
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

                    // Check if date has pending reservations
                    const isPending = data.pending && data.pending.includes(dateString);

                    // Check if date is start of consecutive range
                    let isConsecutiveStart = false;
                    let consecutiveHasPending = false;
                    if (reservationType === 'consecutive' && daysCount) {
                        const consecutiveRange = data.consecutive.find(range => {
                            if (Array.isArray(range)) {
                                return range[0] === dateString;
                            } else if (range.dates) {
                                return range.dates[0] === dateString;
                            }
                            return false;
                        });
                        
                        if (consecutiveRange) {
                            isConsecutiveStart = true;
                            consecutiveHasPending = consecutiveRange.has_pending || false;
                        }
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
                        dayClass += consecutiveHasPending ? 'consecutive-date pending' : 'consecutive-date';
                    } else if (isPending) {
                        dayClass += 'available pending';
                    } else {
                        dayClass += 'available';
                    }

                    const dayElement = document.createElement('div');
                    dayElement.className = dayClass;
                    dayElement.textContent = i;
                    dayElement.dataset.date = dateString;

                    // Add tooltip for pending dates
                    if (isPending || consecutiveHasPending) {
                        dayElement.title = 'Warning: There is a pending reservation for this date';
                    }

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
            // Clear previous selections but preserve pending states
            const selectedDays = document.querySelectorAll('.calendar-day.selected, .calendar-day.consecutive-date, .calendar-day.multiple-date');
            selectedDays.forEach(day => {
                day.classList.remove('selected', 'consecutive-date', 'multiple-date');
                // Re-add available class if it was removed
                if (!day.classList.contains('pending') && !day.classList.contains('unavailable') && !day.classList.contains('no-facility') && !day.classList.contains('other-month')) {
                    day.classList.add('available');
                }
            });

            // Mark the selected date(s)
            if (reservationType === 'single') {
                const dayElement = document.querySelector(`.calendar-day[data-date="${dateString}"]`);
                dayElement.classList.add('selected');
                dayElement.classList.remove('available');
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
                        dayElement.classList.remove('available');
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
                    // Re-add available class
                    if (!dayElement.classList.contains('pending') && !dayElement.classList.contains('unavailable')) {
                        dayElement.classList.add('available');
                    }
                    selectedMultipleDates = selectedMultipleDates.filter(d => d !== dateString);
                } else {
                    if (selectedMultipleDates.length < maxMultipleDates) {
                        dayElement.classList.add('multiple-date');
                        dayElement.classList.remove('available');
                        selectedMultipleDates.push(dateString);
                    }
                }

                updateDateTimeInputs(selectedMultipleDates);
            }

            // Update equipment inputs if "Yes" is selected
            if (document.querySelector('input[name="need_equipment"]:checked').value === 'yes') {
                updateEquipmentInputs();
                // Refresh availability for existing selections after a short delay
                setTimeout(refreshEquipmentAvailability, 100);
            }
        }

        // Function to refresh equipment availability for existing selections
        function refreshEquipmentAvailability() {
            const equipmentSelects = equipmentContainer.querySelectorAll('.equipment-select');
            equipmentSelects.forEach(select => {
                if (select.value) {
                    updateUnitsAvailable(select);
                }
            });
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
                            // Re-add available class if not pending/unavailable
                            if (!dayElement.classList.contains('pending') && !dayElement.classList.contains('unavailable')) {
                                dayElement.classList.add('available');
                            }
                        }
                        updateDateTimeInputs(selectedMultipleDates);
                    });
                }

                multipleDatesContainer.appendChild(group);

                // Update time options for this date with availability data
                if (selectedFacility) {
                    updateTimeOptionsForDate(group, dateStr);
                }
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
        function generateTimeOptions(availableTimes = null) {
            let options = '';
            
            if (availableTimes && availableTimes.length > 0) {
                // Use filtered available times
                availableTimes.forEach(time => {
                    options += `<option value="${time}">${time}</option>`;
                });
            } else {
                // Default: all time slots from 8:00 to 17:30
                for (let hour = 8; hour <= 17; hour++) {
                    for (let minute = 0; minute < 60; minute += 30) {
                        const time = `${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}`;
                        options += `<option value="${time}">${time}</option>`;
                    }
                }
            }
            return options;
        }

        // Function to fetch available times for a specific date and facility
        async function fetchAvailableTimes(facilityId, date) {
            try {
                const response = await fetch(`/api/time-availability/${facilityId}?date=${date}`);
                const data = await response.json();
                return data.available_times || [];
            } catch (error) {
                console.error('Error fetching available times:', error);
                return [];
            }
        }

        // Function to update time options for a specific date group
        async function updateTimeOptionsForDate(dateGroup, date) {
            const facilityId = selectedFacility;
            if (!facilityId) return;

            const availableTimes = await fetchAvailableTimes(facilityId, date);
            const timeFromSelect = dateGroup.querySelector('.time-from');
            const timeToSelect = dateGroup.querySelector('.time-to');

            // Store current selections
            const currentTimeFrom = timeFromSelect.value;
            const currentTimeTo = timeToSelect.value;

            // Update options
            timeFromSelect.innerHTML = generateTimeOptions(availableTimes);
            timeToSelect.innerHTML = generateTimeOptions(availableTimes);

            // Restore selections if still available
            if (availableTimes.includes(currentTimeFrom)) {
                timeFromSelect.value = currentTimeFrom;
            }
            if (availableTimes.includes(currentTimeTo)) {
                timeToSelect.value = currentTimeTo;
            }

            // Add event listeners for time validation
            addTimeValidationListeners(dateGroup, availableTimes);
        }

        // Function to add time validation listeners
        function addTimeValidationListeners(dateGroup, availableTimes) {
            const timeFromSelect = dateGroup.querySelector('.time-from');
            const timeToSelect = dateGroup.querySelector('.time-to');

            timeFromSelect.addEventListener('change', function() {
                updateTimeToOptions(timeFromSelect, timeToSelect, availableTimes);
            });

            timeToSelect.addEventListener('change', function() {
                validateTimeSelection(timeFromSelect, timeToSelect);
            });
        }

        // Function to update "Time To" options based on "Time From" selection
        function updateTimeToOptions(timeFromSelect, timeToSelect, availableTimes) {
            const selectedFromTime = timeFromSelect.value;
            if (!selectedFromTime) return;

            // Filter "Time To" options to only include times after "Time From"
            const validToTimes = availableTimes.filter(time => time > selectedFromTime);
            
            const currentTimeTo = timeToSelect.value;
            timeToSelect.innerHTML = generateTimeOptions(validToTimes);

            // Restore selection if still valid
            if (validToTimes.includes(currentTimeTo)) {
                timeToSelect.value = currentTimeTo;
            }
        }

        // Function to validate time selection
        function validateTimeSelection(timeFromSelect, timeToSelect) {
            const timeFrom = timeFromSelect.value;
            const timeTo = timeToSelect.value;

            if (timeFrom && timeTo && timeTo <= timeFrom) {
                alert('End time must be after start time');
                timeToSelect.value = '';
            }
        }

        // Sample equipment data (in real app, this would come from backend)
        const equipmentOptions = [
            @if(isset($equipments) && $equipments->count() > 0)
                @foreach($equipments as $equipment)
                {
                    id: {{ $equipment->equipment_id ?? 0 }},
                    name: '{{ addslashes($equipment->equipment_name ?? "Unknown Equipment") }}',
                    units: {{ $equipment->units ?? 0 }},
                    hourly_rate: {{ optional($equipment->details)->equipment_per_hour_rate ?? 0 }},
                    package1_rate: {{ optional($equipment->details)->equipment_package_rate1 ?? 0 }},
                    package2_rate: {{ optional($equipment->details)->equipment_package_rate2 ?? 0 }}
                }@if(!$loop->last),@endif
                @endforeach
            @endif
        ];

        // Handle equipment radio button changes
        equipmentRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'yes') {
                    equipmentContainer.style.display = 'block';
                    if (selectedDate || selectedMultipleDates.length > 0) {
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
                const startDate = new Date(selectedDate);
                dates = [selectedDate];
                for (let i = 1; i < daysCount; i++) {
                    const nextDate = new Date(startDate);
                    nextDate.setDate(startDate.getDate() + i);
                    dates.push(formatDate(nextDate));
                }
            }
            else if (reservationType === 'multiple' && selectedMultipleDates.length > 0) {
                dates = selectedMultipleDates;
            }

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
                    <div class="equipment-rows">
                        <div class="equipment-row">
                            <div class="flex flex-col w-full">
                                <div class="font-medium">${dateLabel}</div>
                                <select class="equipment-select w-full px-3 py-2" onchange="updateUnitsAvailable(this)">
                                    <option value="">Select Equipment</option>
                                    ${equipmentOptions.map(opt =>
                            `<option value="${opt.id}" data-units="${opt.units}" data-hourly="${opt.hourly_rate || 0}" data-package1="${opt.package1_rate || 0}" data-package2="${opt.package2_rate || 0}">${opt.name}</option>`
                        ).join('')}
                                </select>
                            </div>
                            <div class="flex flex-col w-1/2">
                                <label class="text-gray-700 text-md font-medium mb-1">Units</label>
                                <input type="number" class="units-input w-full px-3 py-2 border border-gray-300 rounded"
                                       placeholder="Select equipment first" min="1" disabled
                                       oninput="validateUnitsInput(this)">
                            </div>
                        </div>
                    </div>
                    <button class="add-equipment">Add Equipment</button>
                `;

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
                <div class="flex flex-col w-full">
                    <button class="remove-equipment flex justify-end items-center w-full pr-2">&times;</button>
                    <div class="flex flex-row gap-[15px]">
                        <div class="flex w-full">
                            <select class="equipment-select w-full px-3 py-2" onchange="updateUnitsAvailable(this)">
                                <option value="">Select Equipment</option>
                                ${equipmentOptions.map(opt =>
                                `<option value="${opt.id}" data-units="${opt.units}" data-hourly="${opt.hourly_rate}" data-package1="${opt.package1_rate}" data-package2="${opt.package2_rate}">${opt.name}</option>`
                            ).join('')}
                            </select>
                        </div>
                        <div class="flex w-1/2">
                            <input type="number" class="units-input w-full px-3 py-2 border border-gray-300 rounded"
                                   placeholder="Select equipment" min="1" disabled
                                   oninput="validateUnitsInput(this)">
                        </div>
                    </div>
                </div>
            `;

            const removeBtn = newRow.querySelector('.remove-equipment');
            removeBtn.addEventListener('click', function() {
                equipmentRows.removeChild(newRow);
            });

            equipmentRows.appendChild(newRow);
        }

        // Global functions (needed for inline event handlers)
        window.updateUnitsAvailable = function(selectElement) {
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            const equipmentId = selectedOption.value;
            const unitsInput = selectElement.closest('.equipment-row').querySelector('.units-input');
            const dateGroup = selectElement.closest('.equipment-date-group');
            const date = dateGroup.dataset.date;

            if (selectedOption.value && date) {
                // Fetch real-time availability from server
                fetch(`{{ route('equipment.available-units') }}?equipment_id=${equipmentId}&date=${date}`)
                    .then(response => response.json())
                    .then(data => {
                        const availableUnits = data.available_units;
                        const totalUnits = data.total_units;
                        
                        unitsInput.disabled = false;
                        unitsInput.placeholder = `${availableUnits} units available (${totalUnits} total)`;
                        unitsInput.max = availableUnits;
                        unitsInput.min = 1;
                        unitsInput.title = `Enter a number between 1 and ${availableUnits}`;
                        
                        // Clear previous value if it exceeds available units
                        if (unitsInput.value && parseInt(unitsInput.value) > availableUnits) {
                            unitsInput.value = '';
                        }
                        
                        // Show warning if no units available
                        if (availableUnits === 0) {
                            unitsInput.disabled = true;
                            unitsInput.placeholder = 'No units available on this date';
                            alert(`No units of ${selectedOption.text} are available on ${date}`);
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching equipment availability:', error);
                        // Fallback to static data
                        const unitsAvailable = selectedOption.getAttribute('data-units');
                        unitsInput.disabled = false;
                        unitsInput.placeholder = `${unitsAvailable} units available (static)`;
                        unitsInput.max = unitsAvailable;
                        unitsInput.min = 1;
                        unitsInput.title = `Enter a number between 1 and ${unitsAvailable}`;
                    });
            } else {
                unitsInput.disabled = true;
                unitsInput.placeholder = 'Select equipment first';
                unitsInput.value = '';
                unitsInput.removeAttribute('max');
            }
        };

        window.validateUnitsInput = function(inputElement) {
            const maxUnits = parseInt(inputElement.max);
            const value = parseInt(inputElement.value);

            if (isNaN(value)) {
                inputElement.value = '';
                return;
            }

            if (value < 1) {
                inputElement.value = 1;
                alert('Minimum of 1 unit required');
            } else if (value > maxUnits) {
                inputElement.value = maxUnits;
                if (maxUnits === 0) {
                    alert('No units available for this equipment on the selected date');
                } else {
                    alert(`Maximum available units: ${maxUnits}`);
                }
            }
        };

        // Initialize signature upload functionality
        initSignatureUpload();

        function updateReviewContent() {
            // Gather all form data and display it for review
            const reviewContent = document.getElementById('review-content');
            let html = '<div class="space-y-4">';

            // Add your form data to the review here
            // Example:
            const facilitySelect = document.getElementById('facility-select');
            if (facilitySelect.value) {
                const facilityName = facilitySelect.options[facilitySelect.selectedIndex].dataset.name;
                html += `<p><strong>Facility:</strong> ${facilityName}</p>`;
            }

            // Add more form data as needed

            html += '</div>';
            reviewContent.innerHTML = html;
        }

        // Navigation buttons removed - all pages now visible
        
        // Single submit button handler
        document.getElementById('final-submit-btn').addEventListener('click', function(e) {
            e.preventDefault();
            
            console.log('Final submit button clicked');

            // Comprehensive validation
            const validationResult = validateAllRequiredFields();
            
            if (!validationResult.isValid) {
                console.log('Validation failed:', validationResult.emptyFields);
                // Smoothly guide user to first empty field without overwhelming notifications
                scrollToFirstEmptyField(validationResult.emptyFields[0]);
                highlightEmptyFields(validationResult.emptyFields);
                showMinimalValidationFeedback(validationResult.emptyFields[0]);
                return;
            }

            console.log('Validation passed, proceeding with submission');

            // Check if signature file is uploaded (optional)
            if (selectedSignatureFile) {
                console.log('Signature file selected:', selectedSignatureFile.name);
            } else {
                console.log('No signature file provided - proceeding without signature');
            }

            // Add hidden form fields for dynamic data
            addHiddenFields();
            
            // Calculate prices and show enhanced confirmation
            const priceEstimation = calculatePriceEstimation();
            showEnhancedConfirmation(priceEstimation);
        });

        function validateAllRequiredFields() {
            const requiredFields = [
                // 1. Start with reservation setup
                { id: 'reservation_type', label: 'Reservation Type', type: 'radio' },
                { id: 'facility-select', label: 'Facility', type: 'select' },
                
                // 2. Purpose and details 
                { id: 'purpose', label: 'Purpose', type: 'text' },
                { id: 'other_details', label: 'Other Details', type: 'text' },
                
                // 3. Personal information
                { id: 'name', label: 'Name', type: 'text' },
                { id: 'email', label: 'Email', type: 'email' },
                { id: 'organization', label: 'Organization', type: 'text' },
                
                // 4. Equipment preferences
                { id: 'need_equipment', label: 'Need Equipment', type: 'radio' },
                { id: 'personal_equipment', label: 'Personal Equipment', type: 'radio' }
            ];

            const emptyFields = [];

            for (const field of requiredFields) {
                const element = document.getElementById(field.id);
                let isEmpty = false;
                let validationMessage = '';

                if (field.type === 'radio') {
                    const radioButtons = document.querySelectorAll(`input[name="${field.id}"]`);
                    const isChecked = Array.from(radioButtons).some(radio => radio.checked);
                    if (!isChecked) {
                        isEmpty = true;
                    }
                } else if (field.type === 'select') {
                    if (!element.value || element.value === '') {
                        isEmpty = true;
                    }
                } else if (field.type === 'email') {
                    const emailValue = element.value.trim();
                    if (!emailValue) {
                        isEmpty = true;
                    } else {
                        // Email format validation
                        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (!emailRegex.test(emailValue)) {
                            isEmpty = true;
                            validationMessage = 'Please enter a valid email address';
                        }
                    }
                } else {
                    if (!element.value.trim()) {
                        isEmpty = true;
                    }
                }

                if (isEmpty) {
                    emptyFields.push({
                        ...field,
                        validationMessage: validationMessage || `${field.label} is required`
                    });
                }
            }

            // Check reservation-specific fields (dates come after reservation type is selected)
            const reservationType = document.querySelector('input[name="reservation_type"]:checked')?.value;
            
            if (reservationType === 'consecutive') {
                const daysCount = document.getElementById('days-count').value;
                if (!daysCount) {
                    emptyFields.push({ id: 'days-count', label: 'Number of Days', type: 'select', validationMessage: 'Please select the number of days' });
                }
            }

            // Check if dates are selected (this comes after reservation type and facility are selected)
            if (reservationType && selectedFacility) {
                if (reservationType === 'single' && !selectedDate) {
                    emptyFields.push({ id: 'calendar-container', label: 'Date Selection', type: 'calendar', validationMessage: 'Please select a date' });
                } else if (reservationType === 'consecutive' && !selectedDate) {
                    emptyFields.push({ id: 'calendar-container', label: 'Date Selection', type: 'calendar', validationMessage: 'Please select consecutive dates' });
                } else if (reservationType === 'multiple' && selectedMultipleDates.length < 2) {
                    emptyFields.push({ id: 'calendar-container', label: 'Date Selection', type: 'calendar', validationMessage: 'Please select at least 2 dates' });
                }

                // Check if time slots are selected for each date
                if (reservationType && selectedDate) {
                    const dateGroups = document.querySelectorAll('.date-group');
                    let missingTimes = false;
                    
                    dateGroups.forEach(group => {
                        const timeFrom = group.querySelector('select[name*="time_from"]');
                        const timeTo = group.querySelector('select[name*="time_to"]');
                        
                        if (timeFrom && timeTo) {
                            if (!timeFrom.value || !timeTo.value) {
                                missingTimes = true;
                            } else if (timeFrom.value >= timeTo.value) {
                                missingTimes = true;
                            }
                        }
                    });
                    
                    if (missingTimes) {
                        emptyFields.push({ id: 'date-time-container', label: 'Time Selection', type: 'custom', validationMessage: 'Please select valid start and end times for all dates' });
                    }
                }
            }

            // Check equipment selection if needed
            const needEquipment = document.querySelector('input[name="need_equipment"]:checked')?.value;
            if (needEquipment === 'yes') {
                const equipmentRows = document.querySelectorAll('.equipment-row');
                if (equipmentRows.length === 0) {
                    emptyFields.push({ id: 'equipment-container', label: 'Equipment Selection', type: 'custom', validationMessage: 'Please add at least one equipment item' });
                }
            }

            // Check personal equipment details if needed
            const personalEquipment = document.querySelector('input[name="personal_equipment"]:checked')?.value;
            if (personalEquipment === 'yes') {
                const personalEquipmentDetails = document.getElementById('personal_equipment_details').value;
                if (!personalEquipmentDetails.trim()) {
                    emptyFields.push({ id: 'personal_equipment_details', label: 'Personal Equipment Details', type: 'text', validationMessage: 'Please describe your personal equipment' });
                }
            }

            return {
                isValid: emptyFields.length === 0,
                emptyFields: emptyFields
            };
        }

        function highlightEmptyFields(emptyFields) {
            // Clear previous highlights
            clearFieldHighlights();

            // Only highlight the first field to avoid overwhelming the user
            const firstField = emptyFields[0];
            if (!firstField) return;

            if (firstField.type === 'radio') {
                const radioButtons = document.querySelectorAll(`input[name="${firstField.id}"]`);
                const container = radioButtons[0]?.closest('.flex');
                if (container) {
                    container.style.borderLeft = '4px solid #ef4444';
                    container.style.paddingLeft = '12px';
                    container.style.backgroundColor = '#fef2f2';
                    container.style.borderRadius = '4px';
                }
            } else if (firstField.type === 'calendar') {
                const calendarContainer = document.getElementById('calendar-container');
                if (calendarContainer) {
                    calendarContainer.style.borderLeft = '4px solid #ef4444';
                    calendarContainer.style.paddingLeft = '12px';
                    calendarContainer.style.backgroundColor = '#fef2f2';
                    calendarContainer.style.borderRadius = '4px';
                }
            } else {
                const element = document.getElementById(firstField.id);
                if (element) {
                    element.style.borderColor = '#ef4444';
                    element.style.borderWidth = '2px';
                    element.style.backgroundColor = '#fef2f2';
                    element.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.1)';
                }
            }
        }

        function clearFieldHighlights() {
            // Remove all custom validation styles
            document.querySelectorAll('input, select, textarea').forEach(element => {
                element.style.borderColor = '';
                element.style.borderWidth = '';
                element.style.backgroundColor = '';
                element.style.boxShadow = '';
            });
            
            // Clear container highlights
            document.querySelectorAll('.flex').forEach(container => {
                container.style.borderLeft = '';
                container.style.paddingLeft = '';
                container.style.backgroundColor = '';
                container.style.borderRadius = '';
            });
            
            const calendarContainer = document.getElementById('calendar-container');
            if (calendarContainer) {
                calendarContainer.style.borderLeft = '';
                calendarContainer.style.paddingLeft = '';
                calendarContainer.style.backgroundColor = '';
                calendarContainer.style.borderRadius = '';
            }
        }

        function showMinimalValidationFeedback(firstEmptyField) {
            if (!firstEmptyField) return;

            // Remove any existing feedback
            const existingFeedback = document.querySelector('.field-feedback');
            if (existingFeedback) {
                existingFeedback.remove();
            }

            // Create a small, non-intrusive feedback near the field
            let targetElement;
            if (firstEmptyField.type === 'radio') {
                targetElement = document.querySelector(`input[name="${firstEmptyField.id}"]`)?.closest('.flex');
            } else if (firstEmptyField.type === 'calendar') {
                targetElement = document.getElementById('calendar-container');
            } else {
                targetElement = document.getElementById(firstEmptyField.id);
            }

            if (targetElement) {
                const feedback = document.createElement('div');
                feedback.className = 'field-feedback';
                feedback.style.cssText = `
                    position: absolute;
                    background: #ef4444;
                    color: white;
                    padding: 8px 16px;
                    border-radius: 8px;
                    font-size: 14px;
                    font-weight: 500;
                    z-index: 1000;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                    max-width: 280px;
                    word-wrap: break-word;
                    pointer-events: none;
                `;
                feedback.textContent = firstEmptyField.validationMessage || `${firstEmptyField.label} is required`;

                // Position the feedback right below the input field
                const rect = targetElement.getBoundingClientRect();
                feedback.style.left = (rect.left + window.scrollX) + 'px';
                feedback.style.top = (rect.bottom + window.scrollY + 10) + 'px';

                // For the name field at the top, adjust positioning if needed
                if (firstEmptyField.id === 'name') {
                    // Ensure it's visible after scrolling to top
                    setTimeout(() => {
                        const updatedRect = targetElement.getBoundingClientRect();
                        feedback.style.left = (updatedRect.left + window.scrollX) + 'px';
                        feedback.style.top = (updatedRect.bottom + window.scrollY + 10) + 'px';
                    }, 700); // Wait for scroll to complete
                }

                document.body.appendChild(feedback);

                // Auto-remove after 5 seconds or when user starts interacting
                const removeTimeout = setTimeout(() => {
                    if (feedback && feedback.parentElement) {
                        feedback.remove();
                    }
                }, 5000);

                // Remove when user starts typing in the field
                const removeOnInteraction = () => {
                    clearTimeout(removeTimeout);
                    if (feedback && feedback.parentElement) {
                        feedback.remove();
                    }
                };

                if (firstEmptyField.type === 'radio') {
                    document.querySelectorAll(`input[name="${firstEmptyField.id}"]`).forEach(radio => {
                        radio.addEventListener('change', removeOnInteraction, { once: true });
                    });
                } else if (targetElement) {
                    targetElement.addEventListener('input', removeOnInteraction, { once: true });
                    targetElement.addEventListener('focus', removeOnInteraction, { once: true });
                }
            }
        }

        function scrollToFirstEmptyField(firstEmptyField) {
            if (!firstEmptyField) return;

            let targetElement;
            
            if (firstEmptyField.type === 'radio') {
                targetElement = document.querySelector(`input[name="${firstEmptyField.id}"]`);
            } else if (firstEmptyField.type === 'calendar') {
                targetElement = document.getElementById('calendar-container');
            } else {
                targetElement = document.getElementById(firstEmptyField.id);
            }

            if (targetElement) {
                // For the first field (reservation type), scroll to the very top of the form
                if (firstEmptyField.id === 'reservation_type') {
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                } else {
                    // For other fields, scroll with offset
                    const elementTop = targetElement.getBoundingClientRect().top + window.scrollY;
                    const offset = 100; // Space from top
                    
                    window.scrollTo({
                        top: elementTop - offset,
                        behavior: 'smooth'
                    });
                }

                // Focus the element after scroll completes
                setTimeout(() => {
                    if (firstEmptyField.type === 'radio') {
                        const firstRadio = document.querySelector(`input[name="${firstEmptyField.id}"]`);
                        if (firstRadio) {
                            firstRadio.focus();
                        }
                    } else if (firstEmptyField.type !== 'calendar') {
                        targetElement.focus();
                        // Select text for input fields to make it easy to start typing
                        if (targetElement.select && targetElement.type !== 'file') {
                            targetElement.select();
                        }
                    }
                }, 600); // Wait for scroll animation to complete
            }
        }

        function showValidationMessage(emptyFields) {
            // Remove existing validation messages
            const existingMessage = document.getElementById('validation-message');
            if (existingMessage) {
                existingMessage.remove();
            }

            // Create and show new validation message
            const message = document.createElement('div');
            message.id = 'validation-message';
            message.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-4 rounded-lg shadow-lg z-50 max-w-md';
            
            const fieldsList = emptyFields.map(field => `• ${field.validationMessage || field.label}`).join('\n');
            message.innerHTML = `
                <div class="font-bold">⚠️ Please complete these required fields:</div>
                <div class="mt-2 text-sm whitespace-pre-line">${fieldsList}</div>
                <button onclick="this.parentElement.remove()" class="absolute top-2 right-2 text-white hover:text-gray-200 text-lg leading-none">✕</button>
            `;

            document.body.appendChild(message);

            // Auto-remove after 10 seconds
            setTimeout(() => {
                if (message && message.parentElement) {
                    message.remove();
                }
            }, 10000);
        }



        function addHiddenFields() {
            const form = document.getElementById('reservation-form');
            
            // Remove existing hidden fields (in case of re-submission)
            const existingHidden = form.querySelectorAll('input[type="hidden"][data-dynamic="true"]');
            existingHidden.forEach(field => field.remove());

            console.log('Adding hidden fields for reservation type:', reservationType);

            // Add dates data
            const dateGroups = document.querySelectorAll('.date-time-group');
            console.log('Date groups found:', dateGroups.length);
            
            dateGroups.forEach((group, index) => {
                const date = group.dataset.date;
                const timeFromElement = group.querySelector('.time-from');
                const timeToElement = group.querySelector('.time-to');
                
                if (timeFromElement && timeToElement) {
                    const timeFrom = timeFromElement.value;
                    const timeTo = timeToElement.value;

                    console.log(`Date ${index}: ${date}, ${timeFrom} - ${timeTo}`);

                    addHiddenField(`dates[${index}][date]`, date);
                    addHiddenField(`dates[${index}][time_from]`, timeFrom);
                    addHiddenField(`dates[${index}][time_to]`, timeTo);
                } else {
                    console.error(`Missing time elements for date group ${index}`);
                }
            });

            // Add days_count for consecutive reservations
            if (reservationType === 'consecutive') {
                const daysCount = document.getElementById('days-count').value;
                console.log('Adding days_count:', daysCount);
                addHiddenField('days_count', daysCount);
            }

            // Add equipment data if needed
            const needEquipmentChecked = document.querySelector('input[name="need_equipment"]:checked');
            if (needEquipmentChecked && needEquipmentChecked.value === 'yes') {
                const equipmentGroups = document.querySelectorAll('.equipment-date-group');
                let equipmentIndex = 0;

                console.log('Equipment groups found:', equipmentGroups.length);

                equipmentGroups.forEach(group => {
                    const groupDate = group.dataset.date;
                    const rows = group.querySelectorAll('.equipment-row');
                    
                    console.log(`Processing date ${groupDate} with ${rows.length} rows`);

                    rows.forEach(row => {
                        const select = row.querySelector('.equipment-select');
                        const unitsInput = row.querySelector('.units-input');

                        if (select && unitsInput && select.value && unitsInput.value) {
                            const equipmentId = select.value;
                            const quantity = parseInt(unitsInput.value);
                            
                            if (quantity > 0) {
                                console.log(`Adding equipment: ID=${equipmentId}, Qty=${quantity}, Date=${groupDate}`);
                                
                                addHiddenField(`equipment[${equipmentIndex}][equipment_id]`, equipmentId);
                                addHiddenField(`equipment[${equipmentIndex}][quantity]`, quantity);
                                addHiddenField(`equipment[${equipmentIndex}][date]`, groupDate);
                                equipmentIndex++;
                            }
                        }
                    });
                });

                console.log('Total equipment items added:', equipmentIndex);
            }
            
            console.log('Finished adding hidden fields');
        }

        function addHiddenField(name, value) {
            const form = document.getElementById('reservation-form');
            const hiddenField = document.createElement('input');
            hiddenField.type = 'hidden';
            hiddenField.name = name;
            hiddenField.value = value;
            hiddenField.setAttribute('data-dynamic', 'true');
            form.appendChild(hiddenField);
        }

        function setupRealTimeValidation() {
            // Add event listeners to clear validation errors when users interact with fields
            const textInputs = ['name', 'email', 'organization', 'purpose', 'other_details', 'personal_equipment_details'];
            
            textInputs.forEach(fieldId => {
                const element = document.getElementById(fieldId);
                if (element) {
                    element.addEventListener('input', function() {
                        clearFieldError(this);
                    });
                    element.addEventListener('focus', function() {
                        clearFieldError(this);
                    });
                }
            });

            // Handle select elements
            const selects = ['facility-select', 'days-count'];
            selects.forEach(fieldId => {
                const element = document.getElementById(fieldId);
                if (element) {
                    element.addEventListener('change', function() {
                        clearFieldError(this);
                    });
                }
            });

            // Handle radio button groups
            const radioGroups = ['reservation_type', 'need_equipment', 'personal_equipment'];
            radioGroups.forEach(groupName => {
                const radioButtons = document.querySelectorAll(`input[name="${groupName}"]`);
                radioButtons.forEach(radio => {
                    radio.addEventListener('change', function() {
                        clearRadioGroupError(groupName);
                    });
                });
            });

            // Handle calendar interactions
            document.getElementById('calendar-container').addEventListener('click', function() {
                clearFieldError(this);
            });
        }

        function clearFieldError(element) {
            element.classList.remove('validation-error');
            element.style.borderColor = '';
            element.style.backgroundColor = '';
            
            // Remove validation message if all errors are cleared
            setTimeout(() => {
                const remainingErrors = document.querySelectorAll('.validation-error');
                if (remainingErrors.length === 0) {
                    const validationMessage = document.getElementById('validation-message');
                    if (validationMessage) {
                        validationMessage.remove();
                    }
                }
            }, 100);
        }

        function clearRadioGroupError(groupName) {
            const radioButtons = document.querySelectorAll(`input[name="${groupName}"]`);
            radioButtons.forEach(radio => {
                const container = radio.closest('.flex, .inline-flex');
                if (container) {
                    container.classList.remove('validation-error');
                }
            });
        }

        // All pages are now visible by default - no navigation needed

        // Price calculation functions
        function calculatePriceEstimation() {
            const facilitySelect = document.getElementById('facility-select');
            const selectedFacilityId = facilitySelect.value;
            const facilityName = facilitySelect.selectedOptions[0]?.text || 'Selected facility';
            
            // Calculate daily breakdown for each reservation day
            const dailyBreakdown = calculateDailyBreakdown();
            
            // Calculate totals
            let totalHours = 0;
            let totalFacilityAmount = 0;
            let totalEquipmentAmount = 0;
            
            dailyBreakdown.forEach(day => {
                totalHours += day.hours;
                totalFacilityAmount += day.facility.amount;
                totalEquipmentAmount += day.equipment.amount;
            });
            
            const totalPrice = totalFacilityAmount + totalEquipmentAmount;
            
            // Generate detailed breakdown for facility and equipment
            let facilityBreakdown, equipmentBreakdown;
            
            if (dailyBreakdown.length === 1) {
                // Single day reservation - show detailed breakdown
                facilityBreakdown = dailyBreakdown[0].facility.breakdown;
                equipmentBreakdown = dailyBreakdown[0].equipment.breakdown;
            } else {
                // Multiple days - show summary
                facilityBreakdown = `Total facility cost across ${dailyBreakdown.length} day(s)`;
                equipmentBreakdown = `Total equipment cost across ${dailyBreakdown.length} day(s)`;
            }
            
            return {
                facilityName,
                totalHours,
                dailyBreakdown,
                facility: {
                    amount: totalFacilityAmount,
                    breakdown: facilityBreakdown
                },
                equipment: {
                    amount: totalEquipmentAmount,
                    breakdown: equipmentBreakdown
                },
                total: totalPrice
            };
        }
        
        function calculateDailyBreakdown() {
            const dailyData = [];
            
            // Get all date-time groups
            const dateTimeGroups = document.querySelectorAll('.date-time-group');
            
            dateTimeGroups.forEach((group, index) => {
                const dateLabel = group.querySelector('.start-date')?.value || group.dataset.date;
                const timeFromSelect = group.querySelector('.time-from');
                const timeToSelect = group.querySelector('.time-to');
                
                if (timeFromSelect && timeToSelect && timeFromSelect.value && timeToSelect.value) {
                    const timeFrom = timeFromSelect.value;
                    const timeTo = timeToSelect.value;
                    
                    // Calculate hours for this day
                    const dayHours = calculateHoursForTimeSlot(timeFrom, timeTo);
                    
                    // Calculate facility price for this day
                    const facilityPrice = calculateFacilityPriceForDay(dayHours);
                    
                    // Calculate equipment price for this day
                    const equipmentPrice = calculateEquipmentPriceForDay(group.dataset.date, dayHours);
                    
                    dailyData.push({
                        date: dateLabel,
                        formattedDate: formatDateForDisplay(new Date(group.dataset.date)),
                        timeFrom,
                        timeTo,
                        hours: dayHours,
                        facility: facilityPrice,
                        equipment: equipmentPrice,
                        dailyTotal: facilityPrice.amount + equipmentPrice.amount
                    });
                }
            });
            
            return dailyData;
        }
        
        function calculateHoursForTimeSlot(timeFrom, timeTo) {
            // Parse times
            const [fromHour, fromMin] = timeFrom.split(':').map(Number);
            const [toHour, toMin] = timeTo.split(':').map(Number);
            
            let fromMinutes = fromHour * 60 + fromMin;
            let toMinutes = toHour * 60 + toMin;
            
            // Handle overnight reservations
            if (toMinutes < fromMinutes) {
                toMinutes += 24 * 60; // Add 24 hours
            }
            
            // Handle same time (minimum 1 hour)
            if (toMinutes === fromMinutes) {
                return 1;
            } else {
                const durationMinutes = toMinutes - fromMinutes;
                return durationMinutes / 60;
            }
        }
        
        function calculateTotalHours() {
            let totalHours = 0;
            
            // Get all date-time groups
            const dateTimeGroups = document.querySelectorAll('.date-time-group');
            
            dateTimeGroups.forEach(group => {
                const timeFromSelect = group.querySelector('.time-from');
                const timeToSelect = group.querySelector('.time-to');
                
                if (timeFromSelect && timeToSelect && timeFromSelect.value && timeToSelect.value) {
                    const timeFrom = timeFromSelect.value;
                    const timeTo = timeToSelect.value;
                    
                    totalHours += calculateHoursForTimeSlot(timeFrom, timeTo);
                }
            });
            
            return totalHours;
        }
        
        function calculateFacilityPrice(facilityId, totalHours) {
            const facilitySelect = document.getElementById('facility-select');
            const selectedOption = facilitySelect.options[facilitySelect.selectedIndex];
            
            if (!selectedOption || !selectedOption.value) {
                return { amount: 0, breakdown: 'No facility selected' };
            }
            
            const facility = {
                hourly_rate: parseFloat(selectedOption.dataset.hourly) || 0,
                package1_rate: parseFloat(selectedOption.dataset.package1) || 0,
                package2_rate: parseFloat(selectedOption.dataset.package2) || 0
            };
            
            let amount = 0;
            let breakdown = '';
            
            // Package 1: More than 8 hours (whole day)
            if (totalHours > 8 && facility.package1_rate > 0) {
                amount = facility.package1_rate;
                breakdown = `Package 1 (Whole Day): ₱${amount.toLocaleString('en-PH', {minimumFractionDigits: 2})}`;
            }
            // Package 2: More than 4 hours but less than or equal to 8 hours (half day)
            else if (totalHours > 4 && totalHours <= 8 && facility.package2_rate > 0) {
                amount = facility.package2_rate;
                breakdown = `Package 2 (Half Day): ₱${amount.toLocaleString('en-PH', {minimumFractionDigits: 2})}`;
            }
            // Hourly rate: Less than or equal to 4 hours OR fallback when packages not available
            else if (facility.hourly_rate > 0) {
                amount = facility.hourly_rate * totalHours;
                breakdown = `Hourly Rate: ₱${facility.hourly_rate.toLocaleString('en-PH', {minimumFractionDigits: 2})} × ${totalHours.toFixed(1)} hrs = ₱${amount.toLocaleString('en-PH', {minimumFractionDigits: 2})}`;
            }
            // Fallback to packages if no hourly rate
            else if (facility.package2_rate > 0) {
                amount = facility.package2_rate;
                breakdown = `Package 2 (Fallback): ₱${amount.toLocaleString('en-PH', {minimumFractionDigits: 2})}`;
            }
            else if (facility.package1_rate > 0) {
                amount = facility.package1_rate;
                breakdown = `Package 1 (Fallback): ₱${amount.toLocaleString('en-PH', {minimumFractionDigits: 2})}`;
            }
            
            return { amount, breakdown };
        }
        
        function calculateFacilityPriceForDay(dayHours) {
            const facilitySelect = document.getElementById('facility-select');
            const selectedOption = facilitySelect.options[facilitySelect.selectedIndex];
            
            if (!selectedOption || !selectedOption.value) {
                return { amount: 0, breakdown: 'No facility selected' };
            }
            
            const facility = {
                hourly_rate: parseFloat(selectedOption.dataset.hourly) || 0,
                package1_rate: parseFloat(selectedOption.dataset.package1) || 0,
                package2_rate: parseFloat(selectedOption.dataset.package2) || 0
            };
            
            let amount = 0;
            let breakdown = '';
            
            // Package 1: More than 8 hours (whole day)
            if (dayHours > 8 && facility.package1_rate > 0) {
                amount = facility.package1_rate;
                breakdown = `Package 1 (Whole Day): ₱${amount.toLocaleString('en-PH', {minimumFractionDigits: 2})}`;
            }
            // Package 2: More than 4 hours but less than or equal to 8 hours (half day)
            else if (dayHours > 4 && dayHours <= 8 && facility.package2_rate > 0) {
                amount = facility.package2_rate;
                breakdown = `Package 2 (Half Day): ₱${amount.toLocaleString('en-PH', {minimumFractionDigits: 2})}`;
            }
            // Hourly rate: Less than or equal to 4 hours OR fallback when packages not available
            else if (facility.hourly_rate > 0) {
                amount = facility.hourly_rate * dayHours;
                breakdown = `Hourly Rate: ₱${facility.hourly_rate.toLocaleString('en-PH', {minimumFractionDigits: 2})} × ${dayHours.toFixed(1)} hrs = ₱${amount.toLocaleString('en-PH', {minimumFractionDigits: 2})}`;
            }
            // Fallback to packages if no hourly rate
            else if (facility.package2_rate > 0) {
                amount = facility.package2_rate;
                breakdown = `Package 2 (Fallback): ₱${amount.toLocaleString('en-PH', {minimumFractionDigits: 2})}`;
            }
            else if (facility.package1_rate > 0) {
                amount = facility.package1_rate;
                breakdown = `Package 1 (Fallback): ₱${amount.toLocaleString('en-PH', {minimumFractionDigits: 2})}`;
            }
            
            return { amount, breakdown };
        }
        
        function calculateEquipmentPriceForDay(targetDate, dayHours) {
            let totalAmount = 0;
            const breakdown = [];
            
            // Check if equipment is needed
            const needEquipmentChecked = document.querySelector('input[name="need_equipment"]:checked');
            if (!needEquipmentChecked || needEquipmentChecked.value !== 'yes') {
                return { amount: 0, breakdown: 'No equipment requested' };
            }
            
            // Find equipment group for this specific date
            const equipmentDateGroup = document.querySelector(`.equipment-date-group[data-date="${targetDate}"]`);
            if (!equipmentDateGroup) {
                return { amount: 0, breakdown: 'No equipment for this date' };
            }
            
            // Get equipment selections for this date
            const equipmentRows = equipmentDateGroup.querySelectorAll('.equipment-row');
            
            equipmentRows.forEach(row => {
                const select = row.querySelector('.equipment-select');
                const unitsInput = row.querySelector('.units-input');
                
                if (select && unitsInput && select.value && unitsInput.value) {
                    const selectedOption = select.options[select.selectedIndex];
                    const quantity = parseInt(unitsInput.value);
                    
                    if (quantity > 0 && selectedOption) {
                        const equipment = {
                            name: selectedOption.text,
                            hourly_rate: parseFloat(selectedOption.dataset.hourly) || 0,
                            package1_rate: parseFloat(selectedOption.dataset.package1) || 0,
                            package2_rate: parseFloat(selectedOption.dataset.package2) || 0
                        };
                        
                        let itemAmount = 0;
                        let itemBreakdown = '';
                        
                        // Package 1: More than 8 hours (whole day)
                        if (dayHours > 8 && equipment.package1_rate > 0) {
                            itemAmount = equipment.package1_rate * quantity;
                            itemBreakdown = `${equipment.name} (Package 1 - Fallback): ₱${equipment.package1_rate.toLocaleString('en-PH', {minimumFractionDigits: 2})} × ${quantity} = ₱${itemAmount.toLocaleString('en-PH', {minimumFractionDigits: 2})}`;
                        }
                        // Package 2: More than 4 hours (half day) - Note: Equipment uses Package 1 for whole day pricing
                        else if (dayHours > 4 && dayHours <= 8 && equipment.package1_rate > 0) {
                            itemAmount = equipment.package1_rate * quantity;
                            itemBreakdown = `${equipment.name} (Package 1 - Fallback): ₱${equipment.package1_rate.toLocaleString('en-PH', {minimumFractionDigits: 2})} × ${quantity} = ₱${itemAmount.toLocaleString('en-PH', {minimumFractionDigits: 2})}`;
                        }
                        // Hourly rate: Less than or equal to 4 hours OR fallback when packages not available
                        else if (equipment.hourly_rate > 0) {
                            itemAmount = equipment.hourly_rate * dayHours * quantity;
                            itemBreakdown = `${equipment.name} (Hourly): ₱${equipment.hourly_rate.toLocaleString('en-PH', {minimumFractionDigits: 2})} × ${dayHours.toFixed(1)} hrs × ${quantity} = ₱${itemAmount.toLocaleString('en-PH', {minimumFractionDigits: 2})}`;
                        }
                        // Fallback to packages if no hourly rate
                        else if (equipment.package1_rate > 0) {
                            itemAmount = equipment.package1_rate * quantity;
                            itemBreakdown = `${equipment.name} (Package 1 - Fallback): ₱${equipment.package1_rate.toLocaleString('en-PH', {minimumFractionDigits: 2})} × ${quantity} = ₱${itemAmount.toLocaleString('en-PH', {minimumFractionDigits: 2})}`;
                        }
                        
                        if (itemAmount > 0) {
                            totalAmount += itemAmount;
                            breakdown.push(itemBreakdown);
                        }
                    }
                }
            });
            
            return { 
                amount: totalAmount, 
                breakdown: breakdown.length > 0 ? breakdown.join('; ') : 'No equipment selected for this date'
            };
        }
        
        function calculateEquipmentPrice(totalHours) {
            let totalAmount = 0;
            const breakdown = [];
            
            // Check if equipment is needed
            const needEquipmentChecked = document.querySelector('input[name="need_equipment"]:checked');
            if (!needEquipmentChecked || needEquipmentChecked.value !== 'yes') {
                return { amount: 0, breakdown: 'No equipment requested' };
            }
            
            // Get all equipment selections
            const equipmentRows = document.querySelectorAll('.equipment-row');
            
            equipmentRows.forEach(row => {
                const select = row.querySelector('.equipment-select');
                const unitsInput = row.querySelector('.units-input');
                
                if (select && unitsInput && select.value && unitsInput.value) {
                    const selectedOption = select.options[select.selectedIndex];
                    const quantity = parseInt(unitsInput.value);
                    
                    if (selectedOption && quantity > 0) {
                        const equipment = {
                            name: selectedOption.text,
                            hourly_rate: parseFloat(selectedOption.dataset.hourly) || 0,
                            package1_rate: parseFloat(selectedOption.dataset.package1) || 0,
                            package2_rate: parseFloat(selectedOption.dataset.package2) || 0
                        };
                        
                        let equipmentAmount = 0;
                        let equipmentBreakdown = '';
                        
                        // Package 1: More than 8 hours (whole day)
                        if (totalHours > 8 && equipment.package1_rate > 0) {
                            equipmentAmount = equipment.package1_rate * quantity;
                            equipmentBreakdown = `${equipment.name} (Package 1): ₱${equipment.package1_rate.toLocaleString('en-PH', {minimumFractionDigits: 2})} × ${quantity} = ₱${equipmentAmount.toLocaleString('en-PH', {minimumFractionDigits: 2})}`;
                        }
                        // Package 2: More than 4 hours but less than or equal to 8 hours (half day)
                        else if (totalHours > 4 && totalHours <= 8 && equipment.package2_rate > 0) {
                            equipmentAmount = equipment.package2_rate * quantity;
                            equipmentBreakdown = `${equipment.name} (Package 2): ₱${equipment.package2_rate.toLocaleString('en-PH', {minimumFractionDigits: 2})} × ${quantity} = ₱${equipmentAmount.toLocaleString('en-PH', {minimumFractionDigits: 2})}`;
                        }
                        // Hourly rate
                        else if (equipment.hourly_rate > 0) {
                            const baseAmount = equipment.hourly_rate * totalHours;
                            equipmentAmount = baseAmount * quantity;
                            equipmentBreakdown = `${equipment.name} (Hourly): ₱${equipment.hourly_rate.toLocaleString('en-PH', {minimumFractionDigits: 2})} × ${totalHours.toFixed(1)} hrs × ${quantity} = ₱${equipmentAmount.toLocaleString('en-PH', {minimumFractionDigits: 2})}`;
                        }
                        // Fallback to packages
                        else if (equipment.package2_rate > 0) {
                            equipmentAmount = equipment.package2_rate * quantity;
                            equipmentBreakdown = `${equipment.name} (Package 2 - Fallback): ₱${equipment.package2_rate.toLocaleString('en-PH', {minimumFractionDigits: 2})} × ${quantity} = ₱${equipmentAmount.toLocaleString('en-PH', {minimumFractionDigits: 2})}`;
                        }
                        else if (equipment.package1_rate > 0) {
                            equipmentAmount = equipment.package1_rate * quantity;
                            equipmentBreakdown = `${equipment.name} (Package 1 - Fallback): ₱${equipment.package1_rate.toLocaleString('en-PH', {minimumFractionDigits: 2})} × ${quantity} = ₱${equipmentAmount.toLocaleString('en-PH', {minimumFractionDigits: 2})}`;
                        }
                        
                        if (equipmentAmount > 0) {
                            totalAmount += equipmentAmount;
                            breakdown.push(equipmentBreakdown);
                        }
                    }
                }
            });
            
            return { 
                amount: totalAmount, 
                breakdown: breakdown.length > 0 ? breakdown.join('<br>') : 'No equipment selected' 
            };
        }
        
        function getEquipmentDetailsHtml() {
            const needEquipmentChecked = document.querySelector('input[name="need_equipment"]:checked');
            if (!needEquipmentChecked || needEquipmentChecked.value !== 'yes') {
                return '<div class="text-gray-500 italic">No equipment requested</div>';
            }

            const equipmentGroups = document.querySelectorAll('.equipment-date-group');
            if (equipmentGroups.length === 0) {
                return '<div class="text-gray-500 italic">No equipment selected</div>';
            }

            let equipmentHtml = '';
            const equipmentByDate = {};

            // Group equipment by date
            equipmentGroups.forEach(group => {
                const date = group.dataset.date;
                const formattedDate = formatDateForDisplay(new Date(date));
                equipmentByDate[formattedDate] = [];

                const rows = group.querySelectorAll('.equipment-row');
                rows.forEach(row => {
                    const select = row.querySelector('.equipment-select');
                    const unitsInput = row.querySelector('.units-input');

                    if (select && unitsInput && select.value && unitsInput.value) {
                        const equipmentName = select.options[select.selectedIndex].text;
                        const quantity = parseInt(unitsInput.value);
                        
                        if (quantity > 0) {
                            equipmentByDate[formattedDate].push(`${equipmentName} (${quantity} unit${quantity > 1 ? 's' : ''})`);
                        }
                    }
                });
            });

            // Generate HTML for equipment details
            for (const [date, equipmentList] of Object.entries(equipmentByDate)) {
                if (equipmentList.length > 0) {
                    equipmentHtml += `<div class="mb-2"><strong>${date}:</strong></div>`;
                    equipmentList.forEach(equipment => {
                        equipmentHtml += `<div class="ml-4 text-gray-600">• ${equipment}</div>`;
                    });
                }
            }

            return equipmentHtml || '<div class="text-gray-500 italic">No equipment selected</div>';
        }
        
        function showEnhancedConfirmation(priceEstimation) {
            // Create modal backdrop
            const backdrop = document.createElement('div');
            backdrop.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4';
            backdrop.style.backdropFilter = 'blur(4px)';
            
            // Get form data
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const organization = document.getElementById('organization').value;
            const purpose = document.getElementById('purpose').value;
            
            // Create modal content
            const modal = document.createElement('div');
            modal.className = 'bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-screen overflow-y-auto border border-gray-200';
            
            modal.innerHTML = `
                <div class="p-8">
                    <!-- Header -->
                    <div class="flex items-center justify-between mb-6 pb-4 border-b-2 border-gray-100">
                        <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                            <svg class="w-8 h-8 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Confirm Your Reservation
                        </h2>
                        <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
                    </div>
                    
                    <!-- Personal Information -->
                    <div class="mb-6 bg-blue-50 p-4 rounded-lg border border-blue-200">
                        <h3 class="text-lg font-semibold text-blue-800 mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Personal Information
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                            <div><strong>Name:</strong> ${name}</div>
                            <div><strong>Email:</strong> ${email}</div>
                            <div class="md:col-span-2"><strong>Organization:</strong> ${organization}</div>
                        </div>
                    </div>
                    
                    <!-- Reservation Details -->
                    <div class="mb-6 bg-green-50 p-4 rounded-lg border border-green-200">
                        <h3 class="text-lg font-semibold text-green-800 mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            Reservation Details
                        </h3>
                        <div class="space-y-2 text-sm">
                            <div><strong>Facility:</strong> ${priceEstimation.facilityName}</div>
                            <div><strong>Total Hours:</strong> ${priceEstimation.totalHours.toFixed(1)} hours</div>
                            <div><strong>Purpose:</strong> ${purpose}</div>
                            
                            <!-- Equipment Details -->
                            <div class="mt-3 pt-3 border-t border-green-300">
                                <div class="flex items-center mb-2">
                                    <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    <strong>Equipment:</strong>
                                </div>
                                <div class="ml-6 space-y-1 text-xs">
                                    ${getEquipmentDetailsHtml()}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Price Estimation -->
                    <div class="mb-6 bg-amber-50 p-4 rounded-lg border border-amber-200">
                        <h3 class="text-lg font-semibold text-amber-800 mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                            </svg>
                            Payment Estimation
                        </h3>
                        
                        ${priceEstimation.dailyBreakdown && priceEstimation.dailyBreakdown.length > 1 ? 
                            `<!-- Daily Breakdown for Multiple Days -->
                            <div class="space-y-4">
                                <h4 class="text-md font-semibold text-amber-700">📅 Daily Breakdown:</h4>
                                ${priceEstimation.dailyBreakdown.map((day, index) => `
                                    <div class="bg-white p-4 rounded border border-gray-200">
                                        <!-- Date and Hours Header -->
                                        <div class="font-medium text-gray-800 mb-3">
                                            📅 ${day.formattedDate} (${day.timeFrom} - ${day.timeTo}) - ${day.hours.toFixed(1)} hours
                                        </div>
                                        
                                        <!-- Facility Section -->
                                        <div class="mb-3">
                                            <!-- Facility Calculation -->
                                            <div class="text-sm text-gray-600 mb-2">
                                                ${day.facility.breakdown}
                                            </div>
                                            <!-- Facility Label and Price -->
                                            <div class="flex justify-between items-center">
                                                <span class="font-medium text-gray-700">🏢 Facility Fee:</span>
                                                <span class="font-bold text-green-600">₱${day.facility.amount.toLocaleString('en-PH', {minimumFractionDigits: 2})}</span>
                                            </div>
                                        </div>
                                        
                                        <!-- Equipment Section -->
                                        <div class="mb-3">
                                            <!-- Equipment Calculation -->
                                            <div class="text-sm text-gray-600 mb-2">
                                                ${day.equipment.breakdown.split('; ').map(item => `<div>${item}</div>`).join('')}
                                            </div>
                                            <!-- Equipment Label and Price -->
                                            <div class="flex justify-between items-center">
                                                <span class="font-medium text-gray-700">🔧 Equipment Fee:</span>
                                                <span class="font-bold text-blue-600">₱${day.equipment.amount.toLocaleString('en-PH', {minimumFractionDigits: 2})}</span>
                                            </div>
                                        </div>
                                        
                                        <!-- Day Total -->
                                        <div class="border-t pt-3 mt-3">
                                            <div class="flex justify-between items-center font-semibold">
                                                <span class="text-amber-800">Day Total:</span>
                                                <span class="text-lg text-amber-700">₱${day.dailyTotal.toLocaleString('en-PH', {minimumFractionDigits: 2})}</span>
                                            </div>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>`
                            :
                            `<!-- Single Day Detailed Display -->
                            <div class="space-y-4">
                                <!-- Date and Hours Header -->
                                <div class="font-medium text-gray-800 mb-3 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    📅 ${priceEstimation.dailyBreakdown && priceEstimation.dailyBreakdown[0] ? 
                                        `${priceEstimation.dailyBreakdown[0].formattedDate} (${priceEstimation.dailyBreakdown[0].timeFrom} - ${priceEstimation.dailyBreakdown[0].timeTo}) - ${priceEstimation.dailyBreakdown[0].hours.toFixed(1)} hours` : 
                                        'Single Day Reservation'}
                                </div>
                                
                                <!-- Facility Fee Section -->
                                <div class="mb-3">
                                    <div class="text-sm text-gray-600 mb-2">
                                        ${priceEstimation.facility.breakdown}
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="font-medium text-gray-700">🏢 Facility Fee:</span>
                                        <span class="font-bold text-green-600">₱${priceEstimation.facility.amount.toLocaleString('en-PH', {minimumFractionDigits: 2})}</span>
                                    </div>
                                </div>
                                
                                <!-- Equipment Fee Section -->
                                <div class="mb-3">
                                    <div class="text-sm text-gray-600 mb-2">
                                        ${priceEstimation.equipment.breakdown === 'No equipment requested' ? 
                                            '<div class="text-gray-500 italic">No equipment requested</div>' :
                                            priceEstimation.equipment.breakdown.split('; ').map(item => `<div>${item}</div>`).join('')
                                        }
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="font-medium text-gray-700">🔧 Equipment Fee:</span>
                                        <span class="font-bold text-blue-600">₱${priceEstimation.equipment.amount.toLocaleString('en-PH', {minimumFractionDigits: 2})}</span>
                                    </div>
                                </div>
                                
                                <!-- Day Total -->
                                <div class="border-t pt-3 mt-3">
                                    <div class="flex justify-between items-center font-semibold">
                                        <span class="text-amber-800">Day Total:</span>
                                        <span class="text-lg text-amber-700">₱${priceEstimation.total.toLocaleString('en-PH', {minimumFractionDigits: 2})}</span>
                                    </div>
                                </div>
                            </div>`
                        }
                    </div>
                    
                    <!-- Overall Summary -->
                    <div class="mb-6 bg-amber-50 p-4 rounded-lg border-2 border-amber-300">
                        <h3 class="text-lg font-semibold text-amber-800 mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            📊 Overall Summary:
                        </h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-700">🏢 Total Facility Fee:</span>
                                <span class="font-bold text-green-600">₱${priceEstimation.facility.amount.toLocaleString('en-PH', {minimumFractionDigits: 2})}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-700">🔧 Total Equipment Fee:</span>
                                <span class="font-bold text-blue-600">₱${priceEstimation.equipment.amount.toLocaleString('en-PH', {minimumFractionDigits: 2})}</span>
                            </div>
                            <div class="border-t border-amber-400 pt-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-lg font-bold text-amber-800">💳 Grand Total:</span>
                                    <span class="text-xl font-bold text-amber-900">₱${priceEstimation.total.toLocaleString('en-PH', {minimumFractionDigits: 2})}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Note outside containers -->
                    <div class="mb-6 text-xs text-amber-700 bg-amber-100 p-3 rounded border border-amber-200">
                        <strong>Note:</strong> This is an estimated amount. Final payment may vary based on additional charges or adjustments.
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex gap-4 justify-end">
                        <button onclick="this.closest('.fixed').remove()" 
                                class="px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-all duration-200 shadow-md hover:shadow-lg flex items-center font-semibold">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Cancel & Review
                        </button>
                        <button onclick="confirmSubmission()" 
                                class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-all duration-200 shadow-md hover:shadow-lg flex items-center font-semibold">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Confirm & Submit
                        </button>
                    </div>
                </div>
            `;
            
            backdrop.appendChild(modal);
            document.body.appendChild(backdrop);
            
            // Add confirmation function to window for button access
            window.confirmSubmission = function() {
                console.log('✅ User confirmed submission');
                backdrop.remove();
                
                // Submit form directly without preventDefault interference
                const form = document.getElementById('reservation-form');
                console.log('🚀 Submitting form to:', form.action);
                console.log('Form method:', form.method);
                console.log('Form data being submitted:', new FormData(form));
                
                // Disable the submit button to prevent double submission
                document.getElementById('final-submit-btn').disabled = true;
                document.getElementById('final-submit-btn').innerHTML = '⏳ Submitting...';
                
                // Submit the form
                form.submit();
            };
        }
    });
</script>

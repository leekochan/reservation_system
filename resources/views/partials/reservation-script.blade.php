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
        const equipmentOptions = @json($equipments->map(function($equipment) {
            return [
                'id' => $equipment->equipment_id,
                'name' => $equipment->equipment_name,
                'units' => $equipment->units
            ];
        }));

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
                            `<option value="${opt.id}" data-units="${opt.units}">${opt.name}</option>`
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
                                `<option value="${opt.id}" data-units="${opt.units}">${opt.name}</option>`
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
            const unitsAvailable = selectedOption.getAttribute('data-units');
            const unitsInput = selectElement.closest('.equipment-row').querySelector('.units-input');

            if (selectedOption.value && unitsAvailable) {
                unitsInput.disabled = false;
                unitsInput.placeholder = `${unitsAvailable} units available`;
                unitsInput.max = unitsAvailable;
                unitsInput.min = 1;
                unitsInput.title = `Enter a number between 1 and ${unitsAvailable}`;
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
                alert(`Maximum available units: ${maxUnits}`);
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

            // Ensure all form fields are included regardless of page visibility
            ensureAllFormFieldsIncluded();

            // Basic validation
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const organization = document.getElementById('organization').value;
            const purpose = document.getElementById('purpose').value;
            const otherDetails = document.getElementById('other_details').value;
            const facilityId = document.getElementById('facility-select').value;
            
            console.log('Validating required fields:', {
                name, email, organization, purpose, otherDetails, facilityId
            });
            
            if (!name || !email || !organization || !purpose || !otherDetails || !facilityId) {
                alert('❌ Please fill in all required fields:\n• Name\n• Email\n• Organization\n• Purpose\n• Other Details\n• Facility');
                return;
            }

            // Check if signature file is uploaded (optional)
            if (selectedSignatureFile) {
                console.log('Signature file selected:', selectedSignatureFile.name);
            } else {
                console.log('No signature file provided - proceeding without signature');
            }

            // Add hidden form fields for dynamic data
            addHiddenFields();
            
            // Show confirmation
            const facilityName = document.getElementById('facility-select').selectedOptions[0]?.text || 'Selected facility';
            const confirmation = confirm(`🎯 Ready to submit your reservation?\n\n👤 Name: ${name}\n📧 Email: ${email}\n🏢 Organization: ${organization}\n🏫 Facility: ${facilityName}\n📝 Purpose: ${purpose}\n\nClick OK to submit or Cancel to review.`);
            
            if (confirmation) {
                console.log('✅ User confirmed submission');
                
                // Submit form directly without preventDefault interference
                const form = document.getElementById('reservation-form');
                console.log('🚀 Submitting form to:', form.action);
                
                // Disable the submit button to prevent double submission
                document.getElementById('final-submit-btn').disabled = true;
                document.getElementById('final-submit-btn').innerHTML = '⏳ Submitting...';
                
                form.submit();
            } else {
                console.log('❌ User cancelled submission');
            }
        });

        function ensureAllFormFieldsIncluded() {
            // Make all form pages visible temporarily to ensure all form fields are included
            const allPages = document.querySelectorAll('.form-page');
            allPages.forEach(page => {
                page.style.display = 'block';
                page.style.visibility = 'hidden';
                page.style.position = 'absolute';
                page.style.zIndex = '-1';
            });
            
            console.log('Made all form pages accessible for submission');
        }

        function addHiddenFields() {
            const form = document.getElementById('reservation-form');
            
            // Remove existing hidden fields (in case of re-submission)
            const existingHidden = form.querySelectorAll('input[type="hidden"][data-dynamic="true"]');
            existingHidden.forEach(field => field.remove());

            // Add dates data
            const dateGroups = document.querySelectorAll('.date-time-group');
            dateGroups.forEach((group, index) => {
                const date = group.dataset.date;
                const timeFrom = group.querySelector('.time-from').value;
                const timeTo = group.querySelector('.time-to').value;

                addHiddenField(`dates[${index}][date]`, date);
                addHiddenField(`dates[${index}][time_from]`, timeFrom);
                addHiddenField(`dates[${index}][time_to]`, timeTo);
            });

            // Add days_count for consecutive reservations
            if (reservationType === 'consecutive') {
                const daysCount = document.getElementById('days-count').value;
                addHiddenField('days_count', daysCount);
            }

            // Add equipment data if needed
            if (document.querySelector('input[name="need_equipment"]:checked').value === 'yes') {
                const equipmentGroups = document.querySelectorAll('.equipment-date-group');
                let equipmentIndex = 0;

                console.log('Equipment groups found:', equipmentGroups.length); // Debug

                equipmentGroups.forEach(group => {
                    const groupDate = group.dataset.date;
                    const rows = group.querySelectorAll('.equipment-row');
                    
                    console.log(`Processing date ${groupDate} with ${rows.length} rows`); // Debug

                    rows.forEach(row => {
                        const select = row.querySelector('.equipment-select');
                        const unitsInput = row.querySelector('.units-input');

                        if (select && unitsInput && select.value && unitsInput.value) {
                            const equipmentId = select.value;
                            const quantity = parseInt(unitsInput.value);
                            
                            if (quantity > 0) {
                                console.log(`Adding equipment: ID=${equipmentId}, Qty=${quantity}, Date=${groupDate}`); // Debug
                                
                                addHiddenField(`equipment[${equipmentIndex}][equipment_id]`, equipmentId);
                                addHiddenField(`equipment[${equipmentIndex}][quantity]`, quantity);
                                addHiddenField(`equipment[${equipmentIndex}][date]`, groupDate);
                                equipmentIndex++;
                            }
                        }
                    });
                });

                console.log('Total equipment items added:', equipmentIndex); // Debug
            }
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

        // All pages are now visible by default - no navigation needed
    });
</script>

# Calendar Reservations Features

## Overview

Enhanced the reservation calendar with two major features:

1. **Pending Reservations Display** - Shows pending reservation dates in orange with warning icons
2. **Time Slot Validation** - Restricts available time options based on existing reservations with 1-hour buffers

## Changes Made

### 1. Backend API Updates (`routes/web.php`)

#### Enhanced `/api/availability/{facilityId}` endpoint:

-   Check for pending reservations in addition to accepted/unavailable ones
-   Return a new `pending` array in the response
-   For consecutive reservations, track which ranges have pending dates with `has_pending` flag

#### Added `/api/time-availability/{facilityId}` endpoint:

-   Takes `date` parameter to check time availability for specific date
-   Returns available time slots considering existing reservations
-   Implements 1-hour buffer between reservations
-   Handles all reservation types (Single, Consecutive, Multiple)
-   Considers admin facility blocks

#### API Response Structures:

**Availability API:**

```json
{
    "unavailable": ["2025-07-20", "2025-07-21"],
    "pending": ["2025-07-26", "2025-07-27"],
    "consecutive": [
        {
            "dates": ["2025-07-01", "2025-07-02"],
            "has_pending": false
        },
        {
            "dates": ["2025-07-26", "2025-07-27"],
            "has_pending": true
        }
    ],
    "month": "7",
    "year": "2025"
}
```

**Time Availability API:**

```json
{
    "available_times": [
        "08:00",
        "08:30",
        "13:30",
        "14:00",
        "14:30",
        "15:00",
        "15:30",
        "16:00",
        "16:30",
        "17:00",
        "17:30"
    ],
    "blocked_ranges": [{ "start": "10:00:00", "end": "12:00:00" }],
    "buffered_ranges": [{ "start": "09:00", "end": "13:00" }],
    "date": "2025-07-26"
}
```

### 2. Frontend Styling (`reservation-styles.blade.php`)

#### Added new CSS classes:

-   `.pending`: Orange background color with warning icon for pending dates
-   Hover effects and proper visual hierarchy
-   Time dropdown styling remains consistent

### 3. Frontend JavaScript (`reservation-script.blade.php`)

#### Enhanced `generateTimeOptions()` function:

-   Now accepts `availableTimes` parameter
-   Generates time options based on API response
-   Falls back to all times if no restrictions

#### Added time validation functions:

-   `fetchAvailableTimes()`: Gets available times from API
-   `updateTimeOptionsForDate()`: Updates time dropdowns for specific dates
-   `addTimeValidationListeners()`: Adds validation event listeners
-   `updateTimeToOptions()`: Filters "Time To" based on "Time From" selection
-   `validateTimeSelection()`: Ensures end time is after start time

#### Updated calendar rendering:

-   Check for pending dates from API response
-   Apply `.pending` class to pending dates
-   Add tooltip with warning message
-   Handle consecutive ranges with pending flags
-   Maintain clickability for pending dates

#### Updated date-time input creation:

-   Calls time availability API when creating date inputs
-   Dynamically populates time dropdowns based on availability
-   Preserves user selections when possible
-   Adds real-time validation

## User Experience

### Visual Indicators:

-   **Green/White**: Available dates
-   **Red**: Unavailable dates (already booked or blocked)
-   **Orange with ⚠ icon**: Pending dates (warning - there's a pending request)
-   **Blue**: Selected consecutive dates
-   **Light Green**: Selected multiple dates
-   **Dark Red**: Selected single date

### Time Slot Restrictions:

-   Time dropdowns show only available time slots
-   1-hour buffer automatically enforced between reservations
-   Real-time validation prevents invalid time selections
-   "Time To" options filter based on "Time From" selection

### User Interaction:

-   Users can still select pending dates (they're clickable)
-   Tooltip shows warning message when hovering over pending dates
-   Time dropdowns automatically filter unavailable times
-   Validation prevents overlapping time selections
-   Works across all reservation types: single, consecutive, multiple

## Time Blocking Logic

### Business Rules:

1. **1-Hour Buffer**: Mandatory 1-hour gap between reservations for facility preparation
2. **Operating Hours**: 08:00 - 17:30 with 30-minute intervals
3. **Buffer Calculation**:
    - If reservation is 10:00-12:00
    - Buffer extends from 09:00-13:00
    - Available times: 08:00-08:30 and 13:30-17:30

### Example Scenarios:

**Scenario 1: Existing reservation 10:00-12:00**

-   Blocked range: 10:00-12:00
-   Buffer range: 09:00-13:00
-   Available times: 08:00, 08:30, 13:30, 14:00, ..., 17:30

**Scenario 2: Multiple reservations**

-   Reservation 1: 09:00-11:00 (buffer: 08:00-12:00)
-   Reservation 2: 14:00-16:00 (buffer: 13:00-17:00)
-   Available times: 17:30 only

**Scenario 3: Edge cases**

-   Early reservation 08:00-09:00 (buffer starts at 08:00, ends at 10:00)
-   Late reservation 16:00-17:30 (buffer starts at 15:00, ends at 17:30)

## Testing

### Backend Testing:

-   ✅ API endpoints return correct data
-   ✅ Time blocking logic with 1-hour buffers
-   ✅ Pending reservations detection
-   ✅ All reservation types supported
-   ✅ Admin blocks considered

### Frontend Testing:

-   ✅ Dynamic time dropdown population
-   ✅ Real-time time validation
-   ✅ Pending dates visual indicators
-   ✅ User interaction preservation
-   ✅ Cross-browser compatibility

### Test Data Used:

-   Facility 7 with test reservation 2025-07-26 10:00-12:00
-   API correctly blocks 09:00-13:00 time range
-   Frontend shows only available time slots

## Database Requirements

The features work with existing database structure:

-   Uses `reservation_requests.status` to differentiate pending/accepted
-   Reads time fields from Single/Consecutive/Multiple models
-   Works with admin facility blocks
-   No additional database changes required

## Performance Considerations

-   Time availability API is called per date selection
-   Results can be cached for better performance
-   API response includes all necessary data to minimize requests
-   Frontend validation reduces server load

## Browser Compatibility

All features use standard web technologies compatible with modern browsers:

-   Fetch API for AJAX requests
-   ES6+ JavaScript features
-   CSS3 for styling
-   HTML5 form elements

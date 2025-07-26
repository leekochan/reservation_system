# Pending Reservations Calendar Feature

## Overview

Added functionality to display pending reservation dates in the calendar with an orange color and warning icon to inform users that there are pending requests for those dates.

## Changes Made

### 1. Backend API Updates (`routes/web.php`)

#### Modified `/api/availability/{facilityId}` endpoint to:

-   Check for pending reservations in addition to accepted/unavailable ones
-   Return a new `pending` array in the response
-   For consecutive reservations, track which ranges have pending dates with `has_pending` flag

#### API Response Structure:

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

### 2. Frontend Styling (`reservation-styles.blade.php`)

#### Added new CSS class `.pending`:

-   Orange background color (`#ffe6cc`)
-   Orange text color (`#cc6600`)
-   Orange border (`#ffb366`)
-   Warning icon (⚠) in top-right corner
-   Hover effect with lighter orange

### 3. Frontend JavaScript (`reservation-script.blade.php`)

#### Updated `renderCalendar()` function:

-   Check for pending dates from API response
-   Apply `.pending` class to pending dates
-   Add tooltip with warning message
-   Handle consecutive ranges with pending flags
-   Maintain clickability for pending dates

#### Updated `handleDateSelection()` function:

-   Preserve pending state when selecting/deselecting dates
-   Properly manage class combinations (pending + selected, etc.)
-   Ensure pending visual indicator remains visible

#### Updated reservation type handlers:

-   Preserve pending states when switching reservation types
-   Maintain proper class management for all states

## User Experience

### Visual Indicators:

-   **Green/White**: Available dates
-   **Red**: Unavailable dates (already booked or blocked)
-   **Orange with ⚠ icon**: Pending dates (warning - there's a pending request)
-   **Blue**: Selected consecutive dates
-   **Light Green**: Selected multiple dates
-   **Dark Red**: Selected single date

### User Interaction:

-   Users can still select pending dates (they're clickable)
-   Tooltip shows warning message when hovering over pending dates
-   Pending state is preserved during date selection/deselection
-   Works across all reservation types: single, consecutive, multiple

## Testing

The feature has been tested with:

-   ✅ API endpoint returning correct pending dates
-   ✅ Frontend calendar displaying pending dates in orange
-   ✅ Tooltip warnings on pending dates
-   ✅ All three reservation types (single, consecutive, multiple)
-   ✅ Class state management during user interactions

## Database Requirements

The feature works with existing database structure and queries:

-   Uses `reservation_requests.status = 'pending'` to identify pending reservations
-   Works with all reservation detail types (Single, Consecutive, Multiple)
-   No additional database changes required

## Browser Compatibility

The feature uses standard CSS and JavaScript that works in all modern browsers.

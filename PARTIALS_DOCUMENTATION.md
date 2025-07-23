# Reservation System - View Partials Documentation

## Overview

The user reservation form has been refactored to use partials for better code organization, maintainability, and reusability.

## Partials Structure

### Main File: `user-reservation.blade.php`

This is now a clean, minimal file that includes all the necessary partials:

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    @include('partials.reservation-styles')
</head>

<body class="bg-gray-50 m-0 p-0">
@include('partials.navbar')

<div id="dashboard" class="...">
    <form id="reservation-form" action="{{ route('reservation.store') }}" method="POST">
        @csrf
        <div class="...">
            @include('partials.reservation-header')
            @include('partials.reservation-page1')
            @include('partials.reservation-page2')
            @include('partials.reservation-page3')
            @include('partials.reservation-navigation')
        </div>
    </form>
</div>

@include('partials.reservation-script')

</body>
</html>
```

### Partials Breakdown

#### 1. `partials/reservation-styles.blade.php`

**Purpose**: Contains all CSS styles specific to the reservation form
**Contains**:

-   Calendar styling
-   Form page transitions
-   Equipment container styles
-   Signature pad styling
-   Navigation button styles

#### 2. `partials/reservation-header.blade.php`

**Purpose**: The header section with logo and title
**Contains**:

-   UP Cebu logo
-   Form title
-   Centered layout styling

#### 3. `partials/reservation-page1.blade.php`

**Purpose**: First page of the multi-step form
**Contains**:

-   Transaction date display
-   Reservation type selection (Single/Consecutive/Multiple)
-   Consecutive options (days count)
-   Facility selection dropdown
-   Interactive calendar component
-   Date/time selection containers

#### 4. `partials/reservation-page2.blade.php`

**Purpose**: Second page focusing on purpose and equipment
**Contains**:

-   Purpose of request input
-   Equipment needs radio buttons
-   Dynamic equipment selection container
-   Other details input
-   Personal equipment radio buttons with conditional input

#### 5. `partials/reservation-page3.blade.php`

**Purpose**: Final page for user details and submission
**Contains**:

-   User name input
-   Email input
-   Organization input
-   Signature pad component
-   Submit button

#### 6. `partials/reservation-navigation.blade.php`

**Purpose**: Form navigation controls
**Contains**:

-   Previous button
-   Next button
-   Navigation styling

#### 7. `partials/reservation-script.blade.php`

**Purpose**: All JavaScript functionality
**Contains**:

-   Calendar logic
-   Date selection handling
-   Equipment management
-   Form validation
-   Page navigation
-   Signature pad initialization
-   AJAX calls for availability
-   Form submission handling

## Benefits of This Structure

### 1. **Modularity**

-   Each section can be modified independently
-   Easy to locate specific functionality
-   Reusable components

### 2. **Maintainability**

-   Cleaner, smaller files
-   Easier debugging
-   Clear separation of concerns

### 3. **Reusability**

-   Partials can be reused in other views
-   Common components like navigation can be shared
-   Styles can be imported where needed

### 4. **Team Collaboration**

-   Multiple developers can work on different sections
-   Reduced merge conflicts
-   Clear file responsibilities

## File Dependencies

```
user-reservation.blade.php
├── partials/head.blade.php (existing)
├── partials/navbar.blade.php (existing)
├── partials/reservation-styles.blade.php
├── partials/reservation-header.blade.php
├── partials/reservation-page1.blade.php
├── partials/reservation-page2.blade.php
├── partials/reservation-page3.blade.php
├── partials/reservation-navigation.blade.php
└── partials/reservation-script.blade.php
```

## Data Requirements

The partials expect the following data to be passed from the controller:

-   `$facilities` - Collection of facility objects with `facility_id` and `facility_name`
-   `$equipments` - Collection of equipment objects with `equipment_id`, `equipment_name`, and `units`

## Usage Examples

### Including Individual Partials in Other Views

```blade
<!-- Just the calendar component -->
@include('partials.reservation-page1')

<!-- Just the equipment selection -->
@include('partials.reservation-page2')

<!-- Just the signature component -->
@include('partials.reservation-page3')
```

### Customizing Partials

Each partial can be customized by passing additional data:

```blade
@include('partials.reservation-header', ['title' => 'Custom Title'])
```

## Future Enhancements

1. **Create Sub-Partials**: Break down larger partials into smaller components
2. **Add Configuration**: Make partials configurable through parameters
3. **Create Components**: Convert partials to Laravel Blade Components for better type safety
4. **Add Slots**: Use Blade slots for more flexible content injection

## Best Practices

1. **Keep partials focused**: Each partial should have a single responsibility
2. **Use meaningful names**: Partial names should clearly indicate their purpose
3. **Document dependencies**: Clearly document what data each partial requires
4. **Maintain consistency**: Use consistent naming conventions and structure
5. **Test thoroughly**: Ensure all partials work correctly when included

This refactored structure provides a solid foundation for maintaining and extending the reservation system while keeping the code organized and manageable.

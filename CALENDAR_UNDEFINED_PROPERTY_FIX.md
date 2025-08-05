# Calendar Undefined Property Fix

## ❌ **Error Encountered**

```
Undefined property: stdClass::$organization
```

## 🔍 **Root Cause**

The error occurred because different types of reservations in the calendar have different object structures:

1. **Regular Reservations** (ReservationRequest models) - Have `organization` property
2. **Admin Blocks** (stdClass objects) - Do NOT have `organization` property
3. **Completed Reservations** - May or may not have all properties depending on their source

## ✅ **Fixes Applied**

### 1. **Organization Property Check**

**Before:**

```php
@if($reservation->organization)
    <span class="text-gray-700">{{ $reservation->organization }}</span>
@endif
```

**After:**

```php
@if(isset($reservation->organization) && $reservation->organization)
    <span class="text-gray-700">{{ $reservation->organization }}</span>
@endif
```

### 2. **Admin Block Properties**

**Before:**

```php
$startTime = $reservation->formatted_times['start_time'];
$endTime = $reservation->formatted_times['end_time'];
$title = $reservation->purpose;
$facilityName = $reservation->facility->facility_name ?? 'No Facility';
```

**After:**

```php
$startTime = $reservation->formatted_times['start_time'] ?? 'N/A';
$endTime = $reservation->formatted_times['end_time'] ?? 'N/A';
$title = $reservation->purpose ?? 'Admin Block';
$facilityName = isset($reservation->facility) ? ($reservation->facility->facility_name ?? 'No Facility') : 'No Facility';
```

### 3. **Regular Reservation Facility Check**

**Before:**

```php
$facilityName = $reservation->facility->facility_name ?? 'No Facility';
```

**After:**

```php
$facilityName = isset($reservation->facility) ? ($reservation->facility->facility_name ?? 'No Facility') : 'No Facility';
```

### 4. **Sorting Function Fix**

**Before:**

```php
if (isset($reservation->is_admin_block) && $reservation->is_admin_block) {
    return $reservation->formatted_times['raw_start'];
}
```

**After:**

```php
if (isset($reservation->is_admin_block) && $reservation->is_admin_block) {
    return $reservation->formatted_times['raw_start'] ?? '23:59:59';
}
```

## 🛡️ **Prevention Strategy**

### **Defensive Programming Techniques Applied:**

1. **Property Existence Check**: Always use `isset()` before accessing object properties
2. **Null Coalescing Operator**: Use `??` for fallback values
3. **Nested Property Protection**: Check parent objects before accessing child properties
4. **Type-Specific Handling**: Different logic for different object types

### **Best Practices Implemented:**

```php
// ✅ GOOD: Safe property access
$value = isset($object->property) ? $object->property : 'default';
$value = $object->property ?? 'default';

// ✅ GOOD: Nested property safety
$name = isset($reservation->facility) ? ($reservation->facility->facility_name ?? 'No Facility') : 'No Facility';

// ❌ BAD: Direct property access
$value = $object->property; // Can cause undefined property error
```

## 🧪 **Testing**

-   ✅ Calendar loads without errors
-   ✅ Admin blocks display correctly
-   ✅ Regular reservations display correctly
-   ✅ Completed reservations display correctly
-   ✅ Organization field shows only when available
-   ✅ No PHP errors in logs

## 📝 **Files Modified**

-   `resources/views/calendar-activities.blade.php`

## 🎯 **Result**

The calendar now handles all types of reservations safely without throwing undefined property errors. The application is more robust and provides better fallback values when properties are missing.

## 🔧 **Additional Improvements**

-   Added proper fallback values for missing properties
-   Improved error handling for different object types
-   Enhanced defensive programming practices
-   Better user experience with meaningful default values

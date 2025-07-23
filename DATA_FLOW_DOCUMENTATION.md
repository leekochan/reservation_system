# Reservation System - Data Flow Documentation

## Overview

This document explains how data flows from the user reservation form to the database, including the handling of equipment selection across multiple dates.

## Data Flow Process

### 1. Form Structure

The form is divided into 3 pages:

-   **Page 1**: Date selection, facility selection, reservation type
-   **Page 2**: Purpose, equipment selection, personal equipment details
-   **Page 3**: User details, signature, submission

### 2. JavaScript Data Collection

#### Date Collection

```javascript
// Collects dates with time information
dates[0][date] = "2025-07-23";
dates[0][time_from] = "08:00";
dates[0][time_to] = "17:00";
```

#### Equipment Collection

```javascript
// Collects equipment per date
equipment[0][equipment_id] = "1";
equipment[0][quantity] = "2";
equipment[0][date] = "2025-07-23";

equipment[1][equipment_id] = "2";
equipment[1][quantity] = "1";
equipment[1][date] = "2025-07-24";
```

### 3. Controller Validation

```php
// Basic validation rules
'equipment' => 'sometimes|array',
'equipment.*.equipment_id' => 'required_with:equipment|exists:equipments,equipment_id',
'equipment.*.quantity' => 'required_with:equipment|integer|min:1',
'equipment.*.date' => 'required_with:equipment|date',
```

### 4. Database Structure

#### Main Tables

1. **reservation_requests** - Main reservation data
2. **singles/consecutives/multiples** - Date/time specific data
3. **equipment_reservation** - Pivot table for equipment

#### Equipment Pivot Table Schema

```sql
CREATE TABLE equipment_reservation (
    id BIGINT PRIMARY KEY,
    reservation_id BIGINT,
    equipment_id BIGINT,
    reservation_date DATE,
    quantity INTEGER,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 5. Data Saving Process

#### Step 1: Create Main Reservation

```php
$reservation = ReservationRequest::create([
    'name' => $validated['name'],
    'email' => $validated['email'],
    'organization' => $validated['organization'],
    'purpose' => $validated['purpose'],
    'instruction' => $validated['other_details'],
    'electric_equipment' => $validated['personal_equipment'] === 'yes' ? 'Yes' : 'No',
    'transaction_date' => now()->format('Y-m-d'),
    'reservation_type' => ucfirst($validated['reservation_type']),
    'facility_id' => $validated['facility_id'],
    'signature' => $validated['signature'],
    'status' => 'pending',
    'total_payment' => 0,
]);
```

#### Step 2: Save Date/Time Data

Based on reservation type:

-   **Single**: Save to `singles` table
-   **Consecutive**: Save to `consecutives` table
-   **Multiple**: Save to `multiples` table

#### Step 3: Save Equipment Data

```php
foreach ($equipmentData as $equipment) {
    $reservation->equipments()->attach($equipment['equipment_id'], [
        'quantity' => $equipment['quantity'],
        'reservation_date' => $equipment['date'],
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}
```

## Equipment Handling Examples

### Example 1: Single Date with Multiple Equipment

```
User selects:
- Date: 2025-07-23
- Equipment 1: Microphone (Qty: 2)
- Equipment 2: Speaker (Qty: 1)

Database entries:
equipment_reservation table:
| id | reservation_id | equipment_id | reservation_date | quantity |
|----|----------------|--------------|------------------|----------|
| 1  | 123           | 1            | 2025-07-23       | 2        |
| 2  | 123           | 2            | 2025-07-23       | 1        |
```

### Example 2: Multiple Dates with Different Equipment

```
User selects:
- Date 1: 2025-07-23 with Microphone (Qty: 1)
- Date 2: 2025-07-24 with Speaker (Qty: 2)

Database entries:
equipment_reservation table:
| id | reservation_id | equipment_id | reservation_date | quantity |
|----|----------------|--------------|------------------|----------|
| 1  | 124           | 1            | 2025-07-23       | 1        |
| 2  | 124           | 2            | 2025-07-24       | 2        |
```

### Example 3: Consecutive Dates with Same Equipment

```
User selects:
- Consecutive 3 days: 2025-07-23, 2025-07-24, 2025-07-25
- Each day needs: Projector (Qty: 1)

Database entries:
equipment_reservation table:
| id | reservation_id | equipment_id | reservation_date | quantity |
|----|----------------|--------------|------------------|----------|
| 1  | 125           | 3            | 2025-07-23       | 1        |
| 2  | 125           | 3            | 2025-07-24       | 1        |
| 3  | 125           | 3            | 2025-07-25       | 1        |
```

## Model Relationships

### ReservationRequest Model

```php
public function equipments()
{
    return $this->belongsToMany(Equipment::class, 'equipment_reservation', 'reservation_id', 'equipment_id')
                ->withPivot('reservation_date', 'quantity')
                ->withTimestamps();
}

public function facility()
{
    return $this->belongsTo(Facility::class, 'facility_id', 'facility_id');
}

// Polymorphic relationships for date details
public function single()
{
    return $this->hasOne(Single::class, 'reservation_id', 'reservation_id');
}

public function consecutive()
{
    return $this->hasOne(Consecutive::class, 'reservation_id', 'reservation_id');
}

public function multiple()
{
    return $this->hasOne(Multiple::class, 'reservation_id', 'reservation_id');
}
```

## Error Handling

### Validation Errors

-   Invalid equipment IDs
-   Quantity exceeds available units
-   Missing required fields
-   Invalid date formats

### Database Errors

-   Foreign key constraints
-   Duplicate entries
-   Transaction rollbacks

### Example Error Messages

```php
"Equipment with ID 999 not found"
"Requested quantity (5) exceeds available units (3) for Microphone"
"Please select at least one equipment item with valid quantity"
```

## Debugging

### Enable Logging

The system logs all reservation submissions and equipment processing:

```php
Log::info('Reservation submission data:', [
    'name' => $validated['name'],
    'equipment' => $request->equipment ?? [],
]);

Log::info('Processing equipment data:', $request->equipment);
```

### Check Laravel Logs

```bash
tail -f storage/logs/laravel.log
```

### JavaScript Console Debugging

The form includes console.log statements to track equipment collection:

```javascript
console.log("Equipment groups found:", equipmentGroups.length);
console.log(`Processing date ${groupDate} with ${rows.length} rows`);
console.log(
    `Adding equipment: ID=${equipmentId}, Qty=${quantity}, Date=${groupDate}`
);
```

## Success/Error Messages

### Success Message

"Reservation submitted successfully! Your reservation ID is: 123"

### Error Messages

Flash messages are displayed for 5 seconds with:

-   Green background for success
-   Red background for errors
-   Automatic fade-out after 5 seconds

## Testing the System

### Test Cases

1. **Single date, no equipment**
2. **Single date, multiple equipment**
3. **Multiple dates, different equipment per date**
4. **Consecutive dates, same equipment across dates**
5. **Equipment quantity validation**
6. **Invalid equipment selection**

### Database Verification

After form submission, check:

```sql
SELECT * FROM reservation_requests WHERE reservation_id = [ID];
SELECT * FROM equipment_reservation WHERE reservation_id = [ID];
SELECT * FROM singles/consecutives/multiples WHERE reservation_id = [ID];
```

This system ensures that all equipment selections are properly associated with their respective dates and stored in the database with complete traceability.

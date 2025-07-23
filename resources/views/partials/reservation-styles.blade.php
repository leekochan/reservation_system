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
        width: 30px;
        height: 30px;
        margin-right: 15px;
        cursor: pointer;
    }

    .units-input {
        width: 100px;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    .units-input:disabled {
        background-color: #f5f5f5;
        cursor: not-allowed;
    }    .form-page {
        display: block !important; /* Force all pages to be visible for debugging */
    }
    /* Navigation styles removed - all pages now visible */

    .signature-container {
        margin-top: 20px;
        border-top: 1px solid #ddd;
        padding-top: 20px;
    }

    .signature-upload-container {
        padding: 10px;
        border: 1px dashed #ccc;
        border-radius: 8px;
        background-color: #f9f9f9;
        text-align: center;
    }

    #signature-preview-img {
        max-width: 300px;
        max-height: 150px;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 5px;
        background-color: white;
    }
</style>

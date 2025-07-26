<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="icon" href="{{ asset('logo.ico') }}" type="image/x-icon" />
<title>Reservation System</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<style>
    /* Prevent layout shift when scrollbar appears/disappears */
    html {
        overflow-y: scroll; /* Always show vertical scrollbar */
    }
    
    /* Alternative approach - reserve space for scrollbar */
    body {
        margin-left: calc(100vw - 100%);
        margin-left: 0;
    }
    
    /* For modern browsers, use scrollbar-gutter */
    html {
        scrollbar-gutter: stable;
    }
</style>

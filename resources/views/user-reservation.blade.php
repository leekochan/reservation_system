<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
    @include('partials.reservation-styles')
</head>

<body class="bg-gray-50 m-0 p-0">
@include('partials.navbar')

{{-- Success message removed since payment estimation is shown before submission --}}
@if(session('success'))
    <!-- Simple Alert Dialog Overlay -->
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" id="success-overlay">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-sm w-full mx-4">
            <div class="text-center">
                <div class="text-gray-800 mb-4">
                    Reservation submitted successfully!<br>Please wait for the admin approval of your reservation.
                </div>
                <button onclick="document.getElementById('success-overlay').style.display = 'none'" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded transition-colors">
                    OK
                </button>
            </div>
        </div>
    </div>
    <script>
        // Auto-close after 5 seconds if user doesn't click OK
        setTimeout(() => {
            const successOverlay = document.getElementById('success-overlay');
            if (successOverlay) {
                successOverlay.style.display = 'none';
            }
        }, 5000);
    </script>
@endif

@if(session('error'))
    <div class="fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50" id="error-message">
        {{ session('error') }}
    </div>
    <script>
        setTimeout(() => {
            document.getElementById('error-message').style.display = 'none';
        }, 5000);
    </script>
@endif

@if($errors->any())
    <div class="fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50" id="validation-errors">
        <div class="font-bold">Validation Errors:</div>
        <ul class="mt-2">
            @foreach($errors->all() as $error)
                <li class="text-sm">• {{ $error }}</li>
            @endforeach
        </ul>
    </div>
    <script>
        setTimeout(() => {
            document.getElementById('validation-errors').style.display = 'none';
        }, 10000);
    </script>
@endif

<div id="dashboard" class="flex items-center justify-center min-h-[calc(100vh)] bg-cover bg-center bg-no-repeat bg-fixed backdrop-blur-sm"
     style="background-image: url('{{ asset('pictures/cebuUserBackground.jpg') }}'); background-blend-mode: overlay;">
    <form id="reservation-form" action="{{ route('reservation.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="px-12 py-8 flex mt-28 justify-center flex-col bg-white h-auto w-full max-w-2xl rounded-lg shadow-lg">
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

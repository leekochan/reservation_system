<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
    @include('partials.reservation-styles')
</head>

<body class="bg-gray-50 m-0 p-0">
@include('partials.navbar')

{{-- Success message removed since payment estimation is shown before submission --}}
{{-- @if(session('success'))
    <div class="fixed top-4 right-4 bg-white text-gray-800 px-6 py-3 rounded-lg shadow-lg z-50 max-w-3xl overflow-y-auto max-h-96 border-l-4 border-green-500" id="success-message">
        <div class="flex justify-between items-start">
            <div class="flex-1">
                <div class="flex items-center mb-2">
                    <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center mr-2">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-green-700">Reservation Submitted Successfully!</h3>
                </div>
                <div class="text-sm text-gray-700">
                    {!! session('success') !!}
                </div>
            </div>
            <button onclick="document.getElementById('success-message').style.display = 'none'" class="ml-4 text-gray-400 hover:text-gray-600 text-xl">&times;</button>
        </div>
    </div>
    <script>
        setTimeout(() => {
            const successMsg = document.getElementById('success-message');
            if (successMsg) {
                successMsg.style.display = 'none';
            }
        }, 20000); // Increased timeout for detailed message
    </script>
@endif --}}

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

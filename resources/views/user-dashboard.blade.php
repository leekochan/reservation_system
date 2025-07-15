<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
</head>

<body class="bg-gray-50 m-0 p-0">
@include('partials.navbar')

<div id="dashboard" class="flex items-center justify-center min-h-[calc(100vh)] bg-cover bg-center bg-no-repeat backdrop-blur-sm"
    style="background-image: url('{{ asset('pictures/cebuUserBackground.jpg') }}'); background-blend-mode: overlay;"
>

    <div class="text-center">
        <div class="mb-6 flex justify-center">
            <img
                src="{{ asset('pictures/uplogo-removebg-preview.png') }}"
                alt="UP Cebu Logo"
                class="h-[150px] w-auto object-contain"
            >
        </div>
        <h2 class="text-4xl md:text-4xl font-bold leading-tight text-stroke mb-8" style="color: #7B172E;
  text-shadow:
    0 0 10px rgba(255,255,255,0.8),  /* Blurred spread */
    0 0 10px rgba(255,255,255,0.5);">
            University of the Philippines Cebu
        </h2>
        <h1 class="text-5xl md:text-5xl font-bold leading-tight text-stroke" style="color: #7B172E;
  text-shadow:
    0 0 10px rgba(255,255,255,0.8),  /* Blurred spread */
    0 0 10px rgba(255,255,255,0.5);">
            Online Reservation Form Use of Facilities<br>
            and Other Equipment
        </h1>
    </div>
</div>

<section id="facilities" class="bg-white py-16">
    <div class="max-w-6xl mx-auto px-4">
        <h2 class="text-3xl font-bold mb-10">Facilities</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Card 1 -->
            <div class="bg-gray-100 rounded-lg shadow p-4">
                <img src="{{ asset('pictures/facilities/upgym.jpg') }}" alt="Gymnasium" class="w-full h-40 object-cover rounded">
                <p class="mt-4 text-center font-medium">Gymnasium</p>
            </div>
            <!-- Card 2 -->
            <div class="bg-gray-100 rounded-lg shadow p-4">
                <img src="{{ asset('pictures/facilities/fac.jpg') }}" alt="Classroom" class="w-full h-40 object-cover rounded">
                <p class="mt-4 text-center font-medium">Classroom</p>
            </div>
            <!-- Card 3 -->
            <div class="bg-gray-100 rounded-lg shadow p-4">
                <img src="{{ asset('pictures/facilities/fac1.jpg') }}" alt="Computer Lab" class="w-full h-40 object-cover rounded">
                <p class="mt-4 text-center font-medium">Computer Lab</p>
            </div>
            <!-- Card 4 -->
            <div class="bg-gray-100 rounded-lg shadow p-4">
                <img src="{{ asset('pictures/facilities/soccer_field.jpg') }}" alt="Soccer Field" class="w-full h-40 object-cover rounded">
                <p class="mt-4 text-center font-medium">Soccer Field</p>
            </div>
            <!-- Card 5 -->
            <div class="bg-gray-100 rounded-lg shadow p-4">
                <img src="{{ asset('pictures/facilities/football_field.jpg') }}" alt="Football Field" class="w-full h-40 object-cover rounded">
                <p class="mt-4 text-center font-medium">Football Field</p>
            </div>
            <!-- Card 6 -->
            <div class="bg-gray-100 rounded-lg shadow p-4">
                <img src="{{ asset('pictures/facilities/gym.jpg') }}" alt="Gymnasium" class="w-full h-40 object-cover rounded">
                <p class="mt-4 text-center font-medium">Gymnasium</p>
            </div>
        </div>

        <!-- View All Button -->
        <div class="mt-8 flex justify-end">
            <a href="/facilities" class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded shadow transition">
                View all
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>
    </div>
</section>
@php
    use Carbon\Carbon;
    $today = Carbon::now();
@endphp

<section id="calendar" class="bg-white py-16">
    <div class="max-w-6xl mx-auto px-4">
        <h2 class="text-3xl font-bold mb-10">Calendar of Activities</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-100 p-6 rounded-lg shadow-md">

            <!-- Date Box -->
            <div class="flex items-center justify-center border-2 border-blue-500 bg-white p-6 rounded-md">
                <div class="text-center">
                    <h1 class="text-[150px] font-extrabold text-[#7B172E] leading-none">
                        {{ $today->format('d') }}
                    </h1>
                <div class="flex justify-center flex-row">
                    <div class="text-4xl font-bold text-[#7B172E] mr-2">
                        {{ $today->format('F') }}
                    </div>
                    <div class="text-2xl font-semibold text-[#7B172E] mt-1">
                        | {{ $today->format('Y') }}
                    </div>
                </div>
                </div>
            </div>

            <!-- Events List -->
            <div class="bg-gray-200 rounded-md p-6">
                <h3 class="text-3xl font-bold text-[#7B172E] mb-4">Upcoming Events</h3>
                @for ($i = 0; $i < 5; $i++)
                    <div class="flex items-start mb-4">
                        <span class="text-xl mr-2">→</span>
                        <div>
                            <p class="font-semibold">Intellectual Property Rights Seminar</p>
                            <p class="text-sm text-gray-600">January 12, 7AM - 12PM</p>
                        </div>
                    </div>
                @endfor
            </div>
        </div>

        <!-- View All Button -->
        <div class="mt-8 flex justify-end">
            <a href="#" class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded shadow transition">
                View all
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>
    </div>
</section>
<footer class="bg-[#7B172E] text-white py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid-cols-1 md:grid-cols-3 gap-8 flex items-center">
            <!-- Logo and Left Column - Addresses -->
            <div class="flex items-start space-x-4 md:col-span-2">
                <img
                    src="{{ asset('pictures/uplogo.jpg') }}"
                    alt="UP Cebu Logo"
                    class="h-[100px] object-contain rounded-full backdrop-blur-sm"
                >
                <div class="space-y-4">
                    <h3 class="text-xl font-bold">University of the Philippines Cebu</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <p class="flex items-start">
                                <svg class="h-5 w-5 mr-2 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span>Lahug: Gorordo Avenue, Cebu City 6000</span>
                            </p>
                            <p class="flex items-start">
                                <svg class="h-5 w-5 mr-2 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span>SRP: South Road Properties, Cebu City 6000</span>
                            </p>
                        </div>
                        <div class="space-y-2 ml-[100px]">
                            <p class="flex items-start">
                                <svg class="h-5 w-5 mr-2 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <span>pio.upcebu@edu.ph</span>
                            </p>
                            <p class="flex items-start">
                                <svg class="h-5 w-5 mr-2 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <span>(032) 232 8187</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Reserve Button -->
            <div class="flex justify-end flex-1">
                <a href="#" class="inline-block bg-white text-[#7B172E] px-8 py-3 rounded-full font-bold hover:bg-gray-100 transition-colors duration-300 whitespace-nowrap">
                    RESERVE NOW →
                </a>
            </div>
        </div>
    </div>
</footer>
</body>
</html>

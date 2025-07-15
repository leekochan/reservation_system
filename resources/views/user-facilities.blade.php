<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
</head>

<body class="bg-gray-50 m-0 p-0">
@include('partials.navbar')

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
    </div>
</section>
</body>
</html>


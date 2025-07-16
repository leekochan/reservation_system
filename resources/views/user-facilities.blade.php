<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
</head>

<body class="bg-gray-50 m-0 p-0">
@include('partials.navbar')

<section id="facilities" class="mt-8 bg-white py-16">
    <div class="max-w-6xl mx-auto px-4">
        <h2 class="text-3xl font-bold mb-10">Facilities</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($facilities as $facility)
                <div class="bg-gray-100 rounded-lg shadow p-4">
                    <img src="{{ asset('storage/' . $facility->picture) }}" alt="{{ $facility->facility_name }}" class="w-full h-40 object-cover rounded">
                    <p class="mt-4 text-center font-medium">{{ $facility->facility_name }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
</body>
</html>


<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
</head>

<body class="bg-gray-50 m-0 p-0">
@include('partials.admin-navbar')

<section id="equipments" class="mt-12 bg-white py-16">
    <div class="max-w-6xl mx-auto px-4">
        <div class="flex items-center justify-between mb-10">
            <h2 class="text-3xl font-bold">Equipments</h2>
            <a href="/admin/equipments/manage-equipments" class="w-auto py-2 px-4 bg-green-600 uppercase hover:bg-green-700 text-white rounded-md">
                Manage
            </a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($equipments as $equipment)
                <div class="bg-gray-100 rounded-lg shadow p-4">
                    <img src="{{ asset('storage/' . $equipment->picture) }}" alt="{{ $equipment->equipment_name }}" class="w-full h-40 object-cover rounded">
                    <p class="mt-4 text-start font-medium">{{ $equipment->equipment_name }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
</body>
</html>

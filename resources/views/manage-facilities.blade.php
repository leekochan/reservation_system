<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
    <script src="//unpkg.com/alpinejs" defer></script>
</head>

<body class="bg-gray-50 m-0 p-0">
@include('partials.admin-navbar')

<section id="facilities" class="mt-12 bg-white py-16">
    <div class="max-w-6xl mx-auto px-4" x-data="{ showAddModal: false }">
        <!-- Header with Title and Add Button -->
        <div class="flex items-center justify-between mb-10">
            <h2 class="text-3xl font-bold">Facilities</h2>
            <button @click="showAddModal = true" class="bg-[#7A1D30] hover:bg-[#5c1524] text-white font-semibold py-2 px-5 rounded-md flex items-center gap-2">
                <span class="text-lg">＋</span> ADD
            </button>
        </div>

        <!-- Facilities Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($facilities as $facility)
                <div x-data="{ showModal: false }">
                    <div class="bg-gray-100 rounded-lg shadow p-4 flex flex-col justify-between h-full">
                        <img src="{{ asset('storage/' . $facility->picture) }}" alt="{{ $facility->facility_name }}" class="w-full h-40 object-cover rounded">
                        <p class="mt-4 text-start font-medium text-lg">{{ $facility->facility_name }}</p>

                        <div class="flex justify-end mt-4 gap-2">
                            <button @click="showModal = true">
                                <img src="{{ asset('pictures/icons/edit.png') }}" alt="Edit" class="w-5 h-5">
                            </button>
                            {{--                        <form action="{{ route('facilities.destroy', $facility->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete it?')">--}}
                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete it?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit">
                                    <img src="{{ asset('pictures/icons/delete.png') }}" alt="Delete" class="w-5 h-5 mt-1">
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Edit Modal -->
                    <div x-show="showModal" x-cloak class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                        <div class="bg-white rounded-lg p-6 w-[90%] max-w-md relative border-2 border-[#7A1D30]">
                            <button @click="showModal = false" class="absolute top-2 right-2 text-black text-xl">&times;</button>

                            {{--                        <form action="{{ route('facilities.update', $facility->id) }}" method="POST" enctype="multipart/form-data">--}}
                            <form method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <label class="block font-semibold mb-2">Facility:</label>
                                <input type="text" name="facility_name" value="{{ $facility->facility_name }}" class="w-full border px-3 py-2 mb-4 rounded">

                                <label class="block font-semibold mb-2">Image:</label>
                                <div class="w-full h-40 bg-gray-100 border rounded flex items-center justify-center cursor-pointer mb-4">
                                    <label for="imageInput{{ $facility->id }}" class="cursor-pointer text-gray-500 text-sm text-center w-full h-full flex flex-col items-center justify-center">
                                        <img src="{{ asset('pictures/icons/image-icon.png') }}" alt="Upload" class="w-12 h-12 mb-2">
                                        Upload from a computer
                                    </label>
                                    <input type="file" name="picture" id="imageInput{{ $facility->id }}" class="hidden">
                                </div>

                                <div class="text-center">
                                    <button type="submit" class="bg-[#7A1D30] hover:bg-[#5c1524] text-white px-5 py-2 rounded">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Add Card (Plus Sign) -->
            <button @click="showAddModal = true" class="flex items-center justify-center bg-gray-200 rounded-lg shadow hover:bg-gray-300 transition h-60 w-full">
                <div class="text-5xl text-gray-600 font-light">+</div>
            </button>
        </div>

        <!-- Add Modal -->
        <div x-show="showAddModal" x-cloak class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-[90%] max-w-md relative border-2 border-[#7A1D30]">
                <button @click="showAddModal = false" class="absolute top-2 right-2 text-black text-xl">&times;</button>

                <form method="POST" action="/admin/facilities" enctype="multipart/form-data">
                    @csrf

                    <label class="block font-semibold mb-2">Facility Name:</label>
                    <input type="text" name="facility_name" class="w-full border px-3 py-2 mb-4 rounded" required>

                    <label class="block font-semibold mb-2">Number of Units:</label>
                    <input type="number" name="units" class="w-full border px-3 py-2 mb-4 rounded" required>

                    <label class="block font-semibold mb-2">Image:</label>
                    <div class="w-full h-40 bg-gray-100 border rounded flex items-center justify-center cursor-pointer mb-4">
                        <label for="newImageInput" class="cursor-pointer text-gray-500 text-sm text-center w-full h-full flex flex-col items-center justify-center">
                            <img src="{{ asset('pictures/icons/image-icon.png') }}" alt="Upload" class="w-12 h-12 mb-2">
                            Upload from a computer
                        </label>
                        <input type="file" name="picture" id="newImageInput" class="hidden" required>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="bg-[#7A1D30] hover:bg-[#5c1524] text-white px-5 py-2 rounded">Add Facility</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
    <script src="//unpkg.com/alpinejs" defer></script>
    @include('partials.flash-messages')
</head>

<body class="bg-gray-50 m-0 p-0">
@include('partials.admin-navbar')

<section id="facilities" class="mt-12 bg-white py-16">
    <div class="max-w-6xl mx-auto px-4" x-data="{ showAddModal: false }">
        <!-- Header -->
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
                        <div class="mt-4">
                            <p class="font-semibold text-lg">{{ $facility->facility_name }}</p>
                            <p class="text-sm text-gray-600 mt-1">Status: {{ ucfirst($facility->status) }}</p>
                            @if($facility->details)
                                <div class="mt-2 text-sm">
                                    <p>Hourly Rate: ₱{{ number_format($facility->details->facility_per_hour_rate, 2) }}</p>
                                    <p>Package 1: ₱{{ number_format($facility->details->facility_package_rate1, 2) }}</p>
                                    <p>Package 2: ₱{{ number_format($facility->details->facility_package_rate2, 2) }}</p>
                                </div>
                            @endif
                        </div>

                        <div class="flex justify-end mt-4 gap-2">
                            <button @click="showModal = true" class="hover:bg-gray-200 p-1 rounded">
                                <img src="{{ asset('pictures/icons/edit.png') }}" alt="Edit" class="w-5 h-5">
                            </button>
                            <form action="{{ route('facilities.destroy', $facility->facility_id) }}" method="POST"
                                  x-data="{ confirmDelete() { if(confirm('Are you sure you want to delete this facility?')) { this.$el.submit(); } } }"
                                  @submit.prevent="confirmDelete">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="hover:bg-gray-200 p-1 rounded">
                                    <img src="{{ asset('pictures/icons/delete.png') }}" alt="Delete" class="w-5 h-5">
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Edit Modal -->
                    <div x-show="showModal" x-cloak class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto">
                        <div class="bg-white rounded-lg p-6 w-[90%] max-w-md relative border-2 border-[#7A1D30]">
                            <button @click="showModal = false" class="absolute top-2 right-2 text-black text-xl">&times;</button>
                            <h3 class="text-xl font-bold mb-4">Edit Facility</h3>

                            <form action="{{ route('facilities.update', $facility->facility_id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="space-y-4">
                                    <div>
                                        <label class="block font-semibold mb-1">Facility Name:</label>
                                        <input type="text" name="facility_name" value="{{ $facility->facility_name }}"
                                               class="w-full border px-3 py-2 rounded" required>
                                    </div>

                                    <div>
                                        <label class="block font-semibold mb-1">Condition:</label>
                                        <input type="text" name="facility_condition" value="{{ $facility->facility_condition }}"
                                               class="w-full border px-3 py-2 rounded">
                                    </div>

                                    <div>
                                        <label class="block font-semibold mb-1">Status:</label>
                                        <select name="status" class="w-full border px-3 py-2 rounded" required>
                                            <option value="available" {{ $facility->status == 'available' ? 'selected' : '' }}>Available</option>
                                            <option value="not_available" {{ $facility->status == 'not_available' ? 'selected' : '' }}>Not Available</option>
                                        </select>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block font-semibold mb-1">Hourly Rate:</label>
                                            <input type="number" name="facility_per_hour_rate"
                                                   value="{{ $facility->details->facility_per_hour_rate ?? '' }}"
                                                   step="0.01" min="0" class="w-full border px-3 py-2 rounded">
                                        </div>
                                        <div>
                                            <label class="block font-semibold mb-1">Package 1:</label>
                                            <input type="number" name="facility_package_rate1"
                                                   value="{{ $facility->details->facility_package_rate1 ?? '' }}"
                                                   step="0.01" min="0" class="w-full border px-3 py-2 rounded">
                                        </div>
                                        <div>
                                            <label class="block font-semibold mb-1">Package 2:</label>
                                            <input type="number" name="facility_package_rate2"
                                                   value="{{ $facility->details->facility_package_rate2 ?? '' }}"
                                                   step="0.01" min="0" class="w-full border px-3 py-2 rounded">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block font-semibold mb-1">Current Image:</label>
                                        <img src="{{ asset('storage/' . $facility->picture) }}" class="w-full h-40 object-cover rounded mb-2">
                                    </div>

                                    <div>
                                        <label class="block font-semibold mb-1">Change Image:</label>
                                        <div class="w-full h-40 bg-gray-100 border rounded flex items-center justify-center cursor-pointer mb-2 relative overflow-hidden">
                                            <label for="imageInput{{ $facility->facility_id }}" class="cursor-pointer w-full h-full flex flex-col justify-center items-center text-gray-500 text-sm">
                                                <img src="{{ asset('pictures/icons/image-icon.png') }}" class="w-12 h-12 mb-2">
                                                Upload new image
                                            </label>
                                            <input type="file" name="picture" id="imageInput{{ $facility->facility_id }}" class="hidden" accept="image/*">
                                        </div>
                                        <p class="text-xs text-gray-500">Recommended size: 800x600px</p>
                                    </div>
                                </div>

                                <div class="mt-6 flex justify-end gap-3">
                                    <button type="button" @click="showModal = false" class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-100">
                                        Cancel
                                    </button>
                                    <button type="submit" class="bg-[#7A1D30] hover:bg-[#5c1524] text-white px-5 py-2 rounded">
                                        Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Add Card -->
            <button @click="showAddModal = true" class="flex items-center justify-center bg-gray-200 rounded-lg shadow hover:bg-gray-300 transition h-60 w-full">
                <div class="text-5xl text-gray-600 font-light">+</div>
            </button>
        </div>

        <!-- Add Modal -->
        <div x-show="showAddModal" x-cloak class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto">
            <div class="bg-white rounded-lg p-6 w-[90%] max-w-md relative border-2 border-[#7A1D30]">
                <button @click="showAddModal = false" class="absolute top-2 right-2 text-black text-xl">&times;</button>
                <h3 class="text-xl font-bold mb-4">Add New Facility</h3>

                <form method="POST" action="{{ route('facilities.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="space-y-4">
                        <div>
                            <label class="block font-semibold mb-1">Facility Name:</label>
                            <input type="text" name="facility_name" class="w-full border px-3 py-2 rounded" required>
                        </div>

                        <div>
                            <label class="block font-semibold mb-1">Status:</label>
                            <select name="status" class="w-full border px-3 py-2 rounded" required>
                                <option value="available" selected>Available</option>
                                <option value="not_available">Not Available</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block font-semibold mb-1">Hourly Rate:</label>
                                <input type="number" name="facility_per_hour_rate" step="0.01" min="0"
                                       class="w-full border px-3 py-2 rounded">
                            </div>
                            <div>
                                <label class="block font-semibold mb-1">Package 1:</label>
                                <input type="number" name="facility_package_rate1" step="0.01" min="0"
                                       class="w-full border px-3 py-2 rounded">
                            </div>
                            <div>
                                <label class="block font-semibold mb-1">Package 2:</label>
                                <input type="number" name="facility_package_rate2" step="0.01" min="0"
                                       class="w-full border px-3 py-2 rounded">
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold mb-1">Image:</label>
                            <div class="w-full h-40 bg-gray-100 border rounded flex items-center justify-center cursor-pointer mb-2 relative overflow-hidden">
                                <label for="newImageInput" class="cursor-pointer w-full h-full flex flex-col justify-center items-center text-gray-500 text-sm">
                                    <img src="{{ asset('pictures/icons/image-icon.png') }}" class="w-12 h-12 mb-2">
                                    Upload from computer
                                </label>
                                <input type="file" name="picture" id="newImageInput" class="hidden" accept="image/*" required>
                            </div>
                            <p class="text-xs text-gray-500">Recommended size: 800x600px</p>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="showAddModal = false" class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-100">
                            Cancel
                        </button>
                        <button type="submit" class="bg-[#7A1D30] hover:bg-[#5c1524] text-white px-5 py-2 rounded">
                            Add Facility
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // For add modal
        const newImageInput = document.getElementById('newImageInput');
        if (newImageInput) {
            newImageInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        const label = newImageInput.previousElementSibling;
                        label.innerHTML = `<img src="${event.target.result}" class="w-full h-full object-cover" alt="Preview">`;
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        // For edit modals
        document.querySelectorAll('[id^="imageInput"]').forEach(input => {
            input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        const label = input.previousElementSibling;
                        label.innerHTML = `<img src="${event.target.result}" class="w-full h-full object-cover" alt="Preview">`;
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
    });
</script>
</body>
</html>

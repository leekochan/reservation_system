<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
    <script src="//unpkg.com/alpinejs" defer></script>
    @include('partials.flash-messages')
</head>

<body class="bg-gray-50 m-0 p-0">
@include('partials.admin-navbar')

<section id="equipments" class="mt-12 bg-white py-16">
    <div class="max-w-6xl mx-auto px-4" x-data="{
        showAddModal: false,
        openAddModal() {
            this.showAddModal = true;
            if (window.lockBodyScroll) window.lockBodyScroll();
        },
        closeAddModal() {
            this.showAddModal = false;
            if (window.unlockBodyScroll) window.unlockBodyScroll();
        }
    }" x-init="showAddModal = false">
        <!-- Header -->
        <div class="flex items-center justify-between mb-10">
            <h2 class="text-3xl font-bold">Equipment</h2>
            <button @click="openAddModal()" class="bg-[#7A1D30] hover:bg-[#5c1524] text-white font-semibold py-2 px-5 rounded-md flex items-center gap-2 transition-all duration-500 ease-out transform hover:scale-110 hover:shadow-xl hover:-translate-y-1 active:scale-95 active:translate-y-0">
                <span class="text-lg">＋</span> ADD
            </button>
        </div>

        <!-- Equipment Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($equipments as $equipment)
                <div x-data="{
                    showModal: false,
                    openModal() {
                        this.showModal = true;
                        if (window.lockBodyScroll) window.lockBodyScroll();
                    },
                    closeModal() {
                        this.showModal = false;
                        if (window.unlockBodyScroll) window.unlockBodyScroll();
                    }
                }" x-init="showModal = false">
                    <div class="bg-gray-100 rounded-lg shadow p-4 flex flex-col justify-between h-full">
                        <img src="{{ asset('storage/' . $equipment->picture) }}" alt="{{ $equipment->equipment_name }}" class="w-full h-40 object-cover rounded">
                        <div class="mt-4">
                            <p class="font-semibold text-lg">{{ $equipment->equipment_name }}</p>
                            <p class="text-sm text-gray-600 mt-1">Units: {{ $equipment->units }}</p>
                            <p class="text-sm text-gray-600">Status: {{ ucfirst($equipment->status) }}</p>
                            @if($equipment->details)
                                <div class="mt-2 text-sm">
                                    <p>Hourly Rate: ₱{{ number_format($equipment->details->equipment_per_hour_rate, 2) }}</p>
                                    <p>Package 1: ₱{{ number_format($equipment->details->equipment_package_rate1, 2) }}</p>
                                    <p>Package 2: ₱{{ number_format($equipment->details->equipment_package_rate2, 2) }}</p>
                                </div>
                            @endif
                        </div>

                        <div class="flex justify-end mt-4 gap-2">
                            <button @click="openModal()" class="hover:bg-gray-200 p-1 rounded transition-all duration-300 ease-in-out transform hover:scale-110 hover:shadow-md">
                                <img src="{{ asset('pictures/icons/edit.png') }}" alt="Edit" class="w-5 h-5">
                            </button>
                            <form action="{{ route('equipments.destroy', $equipment->equipment_id) }}" method="POST"
                                  x-data="{ confirmDelete() { if(confirm('Are you sure you want to delete this equipment?')) { this.$el.submit(); } } }"
                                  @submit.prevent="confirmDelete">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="hover:bg-gray-200 p-1 rounded transition-all duration-300 ease-in-out transform hover:scale-110 hover:shadow-md">
                                    <img src="{{ asset('pictures/icons/delete.png') }}" alt="Delete" class="w-5 h-5">
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Edit Modal -->
                    <div x-show="showModal"
                         x-cloak
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         @click.self="closeModal()"
                         @keydown.escape.window="closeModal()"
                         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[9999] p-4"
                         style="display: none;">
                        <div class="bg-white rounded-lg p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto relative border-2 border-[#7A1D30] shadow-2xl"
                             x-transition:enter="transition ease-out duration-300 transform"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-200 transform"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             @click.stop>
                            <button @click="closeModal()" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700 text-2xl font-bold w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors z-10">&times;</button>
                            <h3 class="text-xl font-bold mb-4">Edit Equipment</h3>

                            <form action="{{ route('equipments.update', $equipment->equipment_id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="space-y-4">

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block font-semibold mb-1">Equipment Name:</label>
                                            <input type="text" name="equipment_name" value="{{ $equipment->equipment_name }}"
                                                   class="w-full border border-gray-300 px-3 py-2 rounded focus:ring-2 focus:ring-[#7A1D30] focus:border-transparent" required>
                                        </div>
                                        <div>
                                            <label class="block font-semibold mb-1">Units:</label>
                                            <input type="number" name="units" value="{{ $equipment->units }}"
                                                   min="1" class="w-full border border-gray-300 px-3 py-2 rounded focus:ring-2 focus:ring-[#7A1D30] focus:border-transparent" required>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block font-semibold mb-1">Status:</label>
                                        <select name="status" class="w-full border border-gray-300 px-3 py-2 rounded focus:ring-2 focus:ring-[#7A1D30] focus:border-transparent" required>
                                            <option value="available" {{ $equipment->status == 'available' ? 'selected' : '' }}>Available</option>
                                            <option value="not_available" {{ $equipment->status == 'not_available' ? 'selected' : '' }}>Not Available</option>
                                        </select>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block font-semibold mb-1">Hourly Rate:</label>
                                            <input type="number" name="equipment_per_hour_rate"
                                                   value="{{ $equipment->details->equipment_per_hour_rate ?? '' }}"
                                                   step="0.01" min="0" class="w-full border border-gray-300 px-3 py-2 rounded focus:ring-2 focus:ring-[#7A1D30] focus:border-transparent">
                                        </div>
                                        <div>
                                            <label class="block font-semibold mb-1">Package 1:</label>
                                            <input type="number" name="equipment_package_rate1"
                                                   value="{{ $equipment->details->equipment_package_rate1 ?? '' }}"
                                                   step="0.01" min="0" class="w-full border border-gray-300 px-3 py-2 rounded focus:ring-2 focus:ring-[#7A1D30] focus:border-transparent">
                                        </div>
                                        <div>
                                            <label class="block font-semibold mb-1">Package 2:</label>
                                            <input type="number" name="equipment_package_rate2"
                                                   value="{{ $equipment->details->equipment_package_rate2 ?? '' }}"
                                                   step="0.01" min="0" class="w-full border border-gray-300 px-3 py-2 rounded focus:ring-2 focus:ring-[#7A1D30] focus:border-transparent">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block font-semibold mb-1">Current Image:</label>
                                        <img src="{{ asset('storage/' . $equipment->picture) }}" class="w-full h-40 object-cover rounded mb-2">
                                    </div>

                                    <div>
                                        <label class="block font-semibold mb-1">Change Image:</label>
                                        <div class="w-full h-40 bg-gray-100 border rounded flex items-center justify-center cursor-pointer mb-2 relative overflow-hidden">
                                            <label for="imageInput{{ $equipment->equipment_id }}" class="cursor-pointer w-full h-full flex flex-col justify-center items-center text-gray-500 text-sm">
                                                <img src="{{ asset('pictures/icons/image-icon.png') }}" class="w-12 h-12 mb-2">
                                                Upload new image
                                            </label>
                                            <input type="file" name="picture" id="imageInput{{ $equipment->equipment_id }}" class="hidden" accept="image/*">
                                        </div>
                                        <p class="text-xs text-gray-500">Recommended size: 800x600px</p>
                                    </div>
                                </div>

                                <div class="mt-6 flex flex-col sm:flex-row justify-end gap-3">
                                    <button type="button" @click="closeModal()" class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-100 transition-all duration-300 ease-in-out transform hover:scale-105 hover:shadow-md">
                                        Cancel
                                    </button>
                                    <button type="submit" class="bg-[#7A1D30] hover:bg-[#5c1524] text-white px-5 py-2 rounded transition-all duration-500 ease-out transform hover:scale-110 hover:shadow-xl hover:-translate-y-1 active:scale-95 active:translate-y-0">
                                        Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Add Card -->
            <button @click="openAddModal()" class="flex items-center justify-center bg-gray-200 rounded-lg shadow hover:bg-gray-300 transition-all duration-500 ease-out transform hover:scale-105 hover:shadow-xl hover:-translate-y-2 h-60 w-full">
                <div class="text-5xl text-gray-600 font-light">+</div>
            </button>
        </div>

        <!-- Add Modal -->
        <div x-show="showAddModal"
             x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click.self="closeAddModal()"
             @keydown.escape.window="closeAddModal()"
             class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[9999] p-4"
             style="display: none;">
            <div class="bg-white rounded-lg p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto relative border-2 border-[#7A1D30] shadow-2xl"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 @click.stop>
                <button @click="closeAddModal()" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700 text-2xl font-bold w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors z-10">&times;</button>
                <h3 class="text-xl font-bold mb-4">Add New Equipment</h3>

                <form method="POST" action="{{ route('equipments.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="space-y-4">


                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold mb-1">Equipment Name:</label>
                                <input type="text" name="equipment_name" class="w-full border border-gray-300 px-3 py-2 rounded focus:ring-2 focus:ring-[#7A1D30] focus:border-transparent" required>
                            </div>
                            <div>
                                <label class="block font-semibold mb-1">Units:</label>
                                <input type="number" name="units" min="1" class="w-full border border-gray-300 px-3 py-2 rounded focus:ring-2 focus:ring-[#7A1D30] focus:border-transparent" required>
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold mb-1">Status:</label>
                            <select name="status" class="w-full border border-gray-300 px-3 py-2 rounded focus:ring-2 focus:ring-[#7A1D30] focus:border-transparent" required>
                                <option value="available" selected>Available</option>
                                <option value="not_available">Not Available</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block font-semibold mb-1">Hourly Rate:</label>
                                <input type="number" name="equipment_per_hour_rate" step="0.01" min="0"
                                       class="w-full border border-gray-300 px-3 py-2 rounded focus:ring-2 focus:ring-[#7A1D30] focus:border-transparent">
                            </div>
                            <div>
                                <label class="block font-semibold mb-1">Package 1:</label>
                                <input type="number" name="equipment_package_rate1" step="0.01" min="0"
                                       class="w-full border border-gray-300 px-3 py-2 rounded focus:ring-2 focus:ring-[#7A1D30] focus:border-transparent">
                            </div>
                            <div>
                                <label class="block font-semibold mb-1">Package 2:</label>
                                <input type="number" name="equipment_package_rate2" step="0.01" min="0"
                                       class="w-full border border-gray-300 px-3 py-2 rounded focus:ring-2 focus:ring-[#7A1D30] focus:border-transparent">
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold mb-1">Image:</label>
                            <div class="w-full h-40 bg-gray-100 border rounded flex items-center justify-center cursor-pointer mb-2 relative overflow-hidden">
                                <label for="newImageInput" class="cursor-pointer w-full h-full flex flex-col justify-center items-center text-gray-500 text-sm">
                                    <img src="{{ asset('pictures/icons/image-icon.png') }}" class="w-12 h-12 mb-2">
                                    Upload from computer
                                </label>
                                <input type="file" name="picture" id="newImageInput" class="hidden" accept="image/*">
                            </div>
                            <p class="text-xs text-gray-500">Recommended size: 800x600px</p>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col sm:flex-row justify-end gap-3">
                        <button type="button" @click="closeAddModal()" class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-100 transition-all duration-300 ease-in-out transform hover:scale-105 hover:shadow-md">
                            Cancel
                        </button>
                        <button type="submit" class="bg-[#7A1D30] hover:bg-[#5c1524] text-white px-5 py-2 rounded transition-all duration-500 ease-out transform hover:scale-110 hover:shadow-xl hover:-translate-y-1 active:scale-95 active:translate-y-0">
                            Add Equipment
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

        // Modal body scroll lock/unlock functions
        window.lockBodyScroll = function() {
            document.body.classList.add('modal-open');
        };

        window.unlockBodyScroll = function() {
            document.body.classList.remove('modal-open');
        };
    });
</script>
</body>
</html>

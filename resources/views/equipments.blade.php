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
                <div class="bg-white rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300 border border-gray-200">
                    <div class="relative">
                        <img src="{{ asset('storage/' . $equipment->picture) }}" alt="{{ $equipment->equipment_name }}" class="w-full h-48 object-cover rounded-t-lg">
                        <!-- Status Badge -->
                        <div class="absolute top-3 right-3">
                            @if($equipment->status === 'available')
                                <span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-medium bg-green-100 text-green-800 border border-green-300">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    Available
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-medium bg-red-100 text-red-800 border border-red-300">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                    </svg>
                                    Not Available
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="p-5">
                        <h3 class="text-lg font-bold text-gray-900 mb-3">{{ $equipment->equipment_name }}</h3>
                        
                        <!-- Units Available Section -->
                        <div class="mb-4 p-3 rounded-lg border border-gray-200">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-800 flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                    Units Available:
                                </span>
                                <span class="text-sm font-semibold text-green-500">
                                    @if($equipment->units === null)
                                        <span class="text-green-600">Unlimited</span>
                                    @elseif($equipment->units == 0)
                                        <span class="text-red-600">Out of Stock</span>
                                    @else
                                        {{ $equipment->units }} units
                                    @endif
                                </span>
                            </div>
                        </div>
                        
                        <!-- Cost Per Unit -->
                        @if($equipment->cost_per_unit)
                        <div class="mb-4 p-3 bg-orange-50 rounded-lg border border-orange-200">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-orange-800 flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                    Cost per Unit:
                                </span>
                                <span class="text-sm font-bold text-orange-900">
                                    ₱{{ number_format($equipment->cost_per_unit, 2) }}
                                </span>
                            </div>
                        </div>
                        @endif
                        
                        <!-- Rates Section -->
                        @if($equipment->details)
                        <div class="space-y-3">
                            <div class="p-3 rounded-lg border">
                                <h4 class="text-sm font-semibold text-gray-700 mb-2 flex items-center">
                                    Rental Rates
                                </h4>
                                
                                <div class="space-y-2">
                                    <!-- Hourly Rate -->
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Hourly Rate:</span>
                                        <span class="text-sm font-medium text-green-500">
                                            @if($equipment->details->equipment_per_hour_rate)
                                                ₱{{ number_format($equipment->details->equipment_per_hour_rate, 2) }}
                                            @else
                                                <span class="text-gray-400">Not set</span>
                                            @endif
                                        </span>
                                    </div>
                                    
                                    <!-- Package 1 Rate -->
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Package 1:</span>
                                        <span class="text-sm font-medium text-green-500">
                                            @if($equipment->details->equipment_package_rate1)
                                                ₱{{ number_format($equipment->details->equipment_package_rate1, 2) }}
                                            @else
                                                <span class="text-gray-400">Not set</span>
                                            @endif
                                        </span>
                                    </div>
                                    
                                    <!-- Package 2 Rate -->
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Package 2:</span>
                                        <span class="text-sm font-medium text-green-500">
                                            @if($equipment->details->equipment_package_rate2)
                                                ₱{{ number_format($equipment->details->equipment_package_rate2, 2) }}
                                            @else
                                                <span class="text-gray-400">Not set</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
</body>
</html>

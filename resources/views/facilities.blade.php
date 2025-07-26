<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
</head>

<body class="bg-gray-50 m-0 p-0">
@include('partials.admin-navbar')

<section id="facilities" class="mt-12 bg-white py-16">
    <div class="max-w-6xl mx-auto px-4">
        <div class="flex items-center justify-between mb-10">
            <h2 class="text-3xl font-bold">Facilities</h2>
            <a href="/admin/facilities/manage-facilities" class="w-auto py-2 px-4 bg-green-600 uppercase hover:bg-green-700 text-white rounded-md">
                Manage
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($facilities as $facility)
                <div class="bg-white rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300 border border-gray-200">
                    <div class="relative">
                        <img src="{{ asset('storage/' . $facility->picture) }}" alt="{{ $facility->facility_name }}" class="w-full h-48 object-cover rounded-t-lg">
                        <!-- Status Badge -->
                        <div class="absolute top-3 right-3">
                            @if($facility->status === 'available')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-300">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    Available
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-300">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                    </svg>
                                    Not Available
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="p-5">
                        <h3 class="text-lg font-bold text-gray-900 mb-3">{{ $facility->facility_name }}</h3>
                        
                        <!-- Rates Section -->
                        <div class="space-y-3">
                            <div class="p-3 rounded-lg border">
                                <h4 class="text-sm font-semibold text-gray-700 mb-2 flex items-center">
                                    Pricing Information
                                </h4>
                                
                                <div class="space-y-2">
                                    <!-- Hourly Rate -->
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Hourly Rate:</span>
                                        <span class="text-sm font-medium text-green-500">
                                            @if($facility->details && $facility->details->facility_per_hour_rate)
                                                ₱{{ number_format($facility->details->facility_per_hour_rate, 2) }}
                                            @else
                                                <span class="text-gray-400">Not set</span>
                                            @endif
                                        </span>
                                    </div>
                                    
                                    <!-- Package 1 Rate -->
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Package 1:</span>
                                        <span class="text-sm font-medium text-green-500">
                                            @if($facility->details && $facility->details->facility_package_rate1)
                                                ₱{{ number_format($facility->details->facility_package_rate1, 2) }}
                                            @else
                                                <span class="text-gray-400">Not set</span>
                                            @endif
                                        </span>
                                    </div>
                                    
                                    <!-- Package 2 Rate -->
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Package 2:</span>
                                        <span class="text-sm font-medium text-green-500">
                                            @if($facility->details && $facility->details->facility_package_rate2)
                                                ₱{{ number_format($facility->details->facility_package_rate2, 2) }}
                                            @else
                                                <span class="text-gray-400">Not set</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
</body>
</html>

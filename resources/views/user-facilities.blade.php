<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
</head>

<body class="bg-gray-50 m-0 p-0">
@include('partials.navbar')

<section id="facilities" class="mt-12 bg-white py-16">
    <div class="max-w-6xl mx-auto px-4">
        <div class="flex items-center justify-between mb-10">
            <h2 class="text-3xl font-bold">Facilities</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($facilities as $facility)
                <div class="bg-white rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300 border border-gray-200 flex flex-col h-full">
                    <div class="relative flex-shrink-0">
                        <img src="{{ asset('storage/' . $facility->picture) }}" alt="{{ $facility->facility_name }}" class="w-full h-48 object-cover rounded-t-lg">
                    </div>
                    
                    <div class="p-5 flex flex-col flex-grow">
                        <h3 class="text-lg font-bold text-gray-900 mb-3 min-h-[3.5rem] flex items-center">{{ $facility->facility_name }}</h3>
                        
                        <!-- Rates Section -->
                        <div class="space-y-3 flex-grow">
                            <div class="p-3 rounded-lg border h-full flex flex-col">
                                <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                                    Pricing Information
                                </h4>
                                
                                <div class="space-y-2 flex-grow">
                                    <!-- Hourly Rate -->
                                    <div class="flex justify-between items-center py-1">
                                        <span class="text-sm text-gray-600">Hourly Rate:</span>
                                        <span class="text-sm font-medium text-right">
                                            @if($facility->details && $facility->details->facility_per_hour_rate)
                                                <span class="text-green-600">₱{{ number_format($facility->details->facility_per_hour_rate, 2) }}</span>
                                            @else
                                                <span class="text-gray-400">Not set</span>
                                            @endif
                                        </span>
                                    </div>
                                    
                                    <!-- Package 1 Rate -->
                                    <div class="flex justify-between items-center py-1">
                                        <span class="text-sm text-gray-600">Package 1:</span>
                                        <span class="text-sm font-medium text-right">
                                            @if($facility->details && $facility->details->facility_package_rate1)
                                                <span class="text-green-600">₱{{ number_format($facility->details->facility_package_rate1, 2) }}</span>
                                            @else
                                                <span class="text-gray-400">Not set</span>
                                            @endif
                                        </span>
                                    </div>
                                    
                                    <!-- Package 2 Rate -->
                                    <div class="flex justify-between items-center py-1">
                                        <span class="text-sm text-gray-600">Package 2:</span>
                                        <span class="text-sm font-medium text-right">
                                            @if($facility->details && $facility->details->facility_package_rate2)
                                                <span class="text-green-600">₱{{ number_format($facility->details->facility_package_rate2, 2) }}</span>
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


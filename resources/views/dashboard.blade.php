<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
</head>

<body class="bg-gray-50 m-0 p-0">
@include('partials.admin-navbar')

<div class="p-10 mt-16">
    <h1 class="font-bold text-3xl mb-2 ml-4">Reservation</h1>
    @if($reservations->isEmpty())
        <p class="text-xl text-gray-500 ml-4 mt-4">No accepted reservations found.</p>
    @else
        <div class="grid max-w-7xl grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($reservations as $reservation)
                <div class="block p-4 h-full">
                    <div
                        class="flex flex-col bg-white rounded-lg shadow-xl cursor-pointer group-hover:shadow-xl transition-shadow p-6 h-full">
                        <!-- Session details remain the same -->
                        <p class="text-base text-gray-600 flex-grow mt-2">
                            Name: <span
                                class="font-semibold text-black">{{ $reservation->name }}</span>
                        </p>
                        <p class="text-base text-gray-600 flex-grow mt-2">
                            Reservation Type: <span
                                class="font-semibold text-black">{{ $reservation->reservation_type }}</span>
                        </p>
                        <div class="mb-2 mt-2 border-t border-solid border-gray-300">
                            <p class="text-base text-gray-600 flex-grow mt-2">
                                Facilities: <span
                                    class="font-semibold text-black">{{ $reservation->facility->facility_name }}</span>
                            </p>
                            <p class="text-base text-gray-600 flex-grow mt-2">
                                Equipment: <span
                                    class="font-semibold text-black">{{ $reservation->equipment->equipment_name ?? 'No session' }}</span>
                            </p>
                        </div>
                        @if($reservation->reservationDetail)
                            <div class="border-t border-solid border-gray-300">
                                @if($reservation->reservation_type === 'Single')
                                    <p class="text-base text-gray-600 flex-grow mt-2">
                                        Date: <span
                                            class="font-semibold text-black">{{ $reservation->reservationDetail->start_date }}</span>
                                    </p>
                                    <p class="text-base text-gray-600 flex-grow mt-2">
                                        Time from: <span
                                            class="font-semibold text-black">{{ $reservation->reservationDetail->time_from }}</span>
                                    </p>
                                    <p class="text-base text-gray-600 flex-grow mt-2">
                                        Time to: <span
                                            class="font-semibold text-black">{{ $reservation->reservationDetail->time_to }}</span>
                                    </p>
                                @elseif($reservation->reservation_type === 'Consecutive' || $reservation->reservation_type === 'Multiple')
                                    Multiple dates (see details)
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
    <div class="mt-6 text-right">
        <button class="w-auto py-2 px-4 mr-4 bg-green-600 uppercase hover:bg-green-700 text-white rounded-md w-1/4">
            <a href="/admin/reservation">View all >></a>
        </button>
    </div>
</div>


<div class="p-10">
    <h1 class="font-bold text-2xl mb-2 ml-4">Reservation</h1>
    @if($pendingRequests->isEmpty())
        <p class="text-xl text-gray-500 ml-4 mt-4">No pending reservations found.</p>
    @else
        <div class="grid max-w-7xl grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($pendingRequests->sortBy(['transaction_date']) as $request)
                <div class="block p-4 h-full">
                    <div
                        class="flex flex-col bg-white rounded-lg shadow-xl cursor-pointer group-hover:shadow-xl transition-shadow p-6 h-full">
                        <!-- Session details remain the same -->
                        <p class="text-base text-gray-600 flex-grow mt-2">
                            Name: <span
                                class="font-semibold text-black">{{ $request->name }}</span>
                        </p>
                        <p class="text-base text-gray-600 flex-grow mt-2">
                            Reservation Type: <span
                                class="font-semibold text-black">{{ $request->reservation_type }}</span>
                        </p>
                        <div class="mb-2 mt-2 border-t border-solid border-gray-300">
                            <p class="text-base text-gray-600 flex-grow mt-2">
                                Facilities: <span
                                    class="font-semibold text-black">{{ $request->facility->facility_name }}</span>
                            </p>
                            <p class="text-base text-gray-600 flex-grow mt-2">
                                Equipment: <span
                                    class="font-semibold text-black">{{ $request->equipment->equipment_name ?? 'No session' }}</span>
                            </p>
                        </div>
                        @if($request->reservationDetail)
                            <div class="mt-2">
                                    @if($request->reservation_type === 'Single')
                                    <p class="text-base text-gray-600 flex-grow mt-2">
                                        Date: <span
                                            class="font-semibold text-black">{{ $reservation->reservationDetails->start_date }}</span>
                                    </p>
                                    <p class="text-gray-500 text-sm">Time from:</p>
                                    <p class="font-medium">
                                        {{ $request->reservationDetail->time_from }}
                                    </p>
                                    <p class="text-gray-500 text-sm">Time from:</p>
                                    <p class="font-medium">
                                    {{ $request->reservationDetail->time_to }}
                                    </p>
                                    @elseif($request->reservation_type === 'Consecutive' || $request->reservation_type === 'Multiple')
                                        Multiple dates (see details)
                                    @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
    <div class="mt-6 text-right">
        <button class="w-auto py-2 px-4 mr-4 bg-green-600 uppercase hover:bg-green-700 text-white rounded-md w-1/4">
            <a href="/admin/reservation">View all >></a>
        </button>
    </div>
</div>

<section id="facilities" class="bg-white py-16">
    <div class="max-w-6xl mx-auto px-4">
        <h2 class="text-3xl font-bold mb-10">Facilities</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($facilities as $facility)
                <div class="bg-gray-100 rounded-lg shadow p-4">
                    <img src="{{ asset('storage/' . $facility->picture) }}" alt="{{ $facility->facility_name }}" class="w-full h-40 object-cover rounded">
                    <p class="mt-4 text-start font-medium">{{ $facility->facility_name }}</p>
                </div>
            @endforeach
        </div>

        <!-- View All Button -->
        <div class="mt-8 flex justify-end">
            <div class="mt-6 text-right">
                <button class="w-auto py-2 px-4 mr-4 bg-green-600 uppercase hover:bg-green-700 text-white rounded-md w-1/4">
                    <a href="/admin/facilities">View all >></a>
                </button>
            </div>
        </div>
    </div>
</section>

<section id="extra-equipments" class="bg-white py-16 min-h-screen">
    <div class="max-w-6xl mx-auto px-4">
        <h2 class="text-3xl font-bold mb-10">Extra Equipments</h2>

        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="grid grid-cols-2 bg-gray-200 px-6 py-4 border-b border-gray-300">
                <div class="font-bold text-gray-800">Equipments</div>
                <div class="font-bold text-gray-800">Available Unit</div>
            </div>

            <div class="divide-y divide-gray-200">
                @foreach($equipments as $equipment)
                    <div class="grid grid-cols-2 px-6 py-3 hover:bg-gray-50">
                        <div class="text-gray-800">{{ $equipment->equipment_name }}</div>
                        <div class="text-gray-700">
                            @if($equipment->units === null)
                                Unlimited Units
                            @elseif($equipment->units == 0)
                                No units available
                            @else
                                {{ $equipment->units }} units
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
        <div class="px-6 py-4 bg-white text-right">
            <a href="/admin/equipments" class="w-auto py-2 px-4 bg-green-600 uppercase hover:bg-green-700 text-white rounded-md">
                Manage
            </a>
        </div>
    </div>
</section>

</body>
</html>

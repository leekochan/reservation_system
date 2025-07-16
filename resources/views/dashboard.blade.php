<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
</head>

<body class="bg-gray-50 m-0 p-0">
@include('partials.navbar')

<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-6">Reservation Dashboard</h1>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Reservations Section -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Reservations</h2>
                <div class="flex space-x-2">
                    <a href="{{ route('admin.reservations.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View all</a>
                </div>
            </div>

            @if($reservations->isEmpty())
                <p class="text-gray-500">No accepted reservations found.</p>
            @else
                <div class="space-y-6">
                    @foreach($reservations as $reservation)
                        <div class="border-b pb-4 last:border-b-0 last:pb-0">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-gray-500 text-sm">Name:</p>
                                    <p class="font-medium">{{ $reservation->name }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500 text-sm">Type of reservation:</p>
                                    <p class="font-medium">{{ $reservation->reservation_type }}</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4 mt-2">
                                <div>
                                    <p class="text-gray-500 text-sm">Facilities:</p>
                                    <p class="font-medium">{{ $reservation->facility->facility_name ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500 text-sm">Equipment:</p>
                                    <p class="font-medium">{{ $reservation->equipment->equipment_name ?? 'N/A' }}</p>
                                </div>
                            </div>
                            @if($reservation->reservationDetail)
                                <div class="mt-2">
                                    <p class="text-gray-500 text-sm">Date/Time:</p>
                                    <p class="font-medium">
                                        @if($reservation->reservation_type === 'Single')
                                            {{ $reservation->reservationDetail->start_date }}
                                            {{ $reservation->reservationDetail->time_from }} - {{ $reservation->reservationDetail->time_to }}
                                        @elseif($reservation->reservation_type === 'Consecutive' || $reservation->reservation_type === 'Multiple')
                                            Multiple dates (see details)
                                        @endif
                                    </p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Pending Requests Section -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Pending Requests</h2>
                <div class="flex space-x-2">
                    <a href="{{ route('admin.requests.pending') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View all</a>
                </div>
            </div>

            @if($pendingRequests->isEmpty())
                <p class="text-gray-500">No pending requests found.</p>
            @else
                <div class="space-y-6">
                    @foreach($pendingRequests as $request)
                        <div class="border-b pb-4 last:border-b-0 last:pb-0">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-gray-500 text-sm">Name:</p>
                                    <p class="font-medium">{{ $request->name }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500 text-sm">Type of reservation:</p>
                                    <p class="font-medium">{{ $request->reservation_type }}</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4 mt-2">
                                <div>
                                    <p class="text-gray-500 text-sm">Facilities:</p>
                                    <p class="font-medium">{{ $request->facility->facility_name ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500 text-sm">Transaction date:</p>
                                    <p class="font-medium">{{ $request->transaction_date }}</p>
                                </div>
                            </div>
                            <div class="mt-2 flex justify-end space-x-2">
                                <form action="{{ route('admin.requests.approve', $request->reservation_id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 bg-green-600 text-white rounded text-sm hover:bg-green-700">Approve</button>
                                </form>
                                <form action="{{ route('admin.requests.reject', $request->reservation_id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 bg-red-600 text-white rounded text-sm hover:bg-red-700">Reject</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<div class="bg-white shadow-md rounded-lg p-10">
    <h1 class="font-bold text-2xl mb-2 ml-4">Reservation</h1>
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
                            <div class="mt-2">
                                <p class="text-gray-500 text-sm">Date/Time:</p>
                                <p class="font-medium">
                                    @if($reservation->reservation_type === 'Single')
                                        {{ $reservation->reservationDetail->start_date }}
                                        {{ $reservation->reservationDetail->time_from }} - {{ $reservation->reservationDetail->time_to }}
                                    @elseif($reservation->reservation_type === 'Consecutive' || $reservation->reservation_type === 'Multiple')
                                        Multiple dates (see details)
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
    <div class="mt-6 text-right">
        <button class="w-auto py-2 px-4 mr-4 bg-green-600 uppercase hover:bg-green-700 text-white rounded-md w-1/4">
            <a href="/reservation">View all >></a>
        </button>
    </div>
</div>
</body>
</html>

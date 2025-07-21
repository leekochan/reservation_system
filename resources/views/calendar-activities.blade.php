<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
</head>

<body class="bg-gray-50 m-0 p-0">
@include('partials.navbar')

@php
    use Carbon\Carbon;
    $today = Carbon::now();
    $monthName = $today->format('F');
    $year = $today->year;
    $daysInMonth = $today->daysInMonth;
    $firstDayOfMonth = Carbon::createFromDate($year, $today->month, 1)->dayOfWeek;
@endphp

<div class="container mx-auto mt-[60px]">
    <!-- Top Layout -->
    <div class="flex flex-wrap gap-4 justify-between items-center mb-6">
        <!-- Left Section: Date -->
        <div class="flex items-center justify-center p-6 rounded-md ml-[100px]">
            <div class="flex items-end"> <!-- Changed to horizontal alignment -->
                <h1 class="text-[250px] font-extrabold text-[#7B172E] leading-none mr-4">
                    {{ $today->format('d') }}
                </h1>
                <div class="flex flex-col justify-center items-center"> <!-- Month and year stacked vertically -->
                    <div class="text-8xl font-bold text-[#7B172E] mt-8">
                        {{ $today->format('F') }}
                    </div>
                    <div class="text-4xl font-semibold text-[#7B172E]">
                        {{ $today->format('Y') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Calendar -->
        <div class="bg-white rounded shadow p-4 ml-auto mt-[50px] mr-[10px]">
            <div class="text-center mb-2 text-lg font-medium">{{ $monthName }}, {{ $year }}</div>
            <div class="grid grid-cols-7 gap-2 text-sm text-center font-semibold">
                @foreach(['SUN','MON','TUE','WED','THU','FRI','SAT'] as $day)
                    <div>{{ $day }}</div>
                @endforeach

                @for($i = 0; $i < $firstDayOfMonth; $i++)
                    <div></div>
                @endfor

                @for($day = 1; $day <= $daysInMonth; $day++)
                    <div class="p-1 {{ $day == $today->day ? 'bg-maroon-800 text-white rounded-full' : '' }}">
                        {{ $day }}
                    </div>
                @endfor
            </div>
        </div>
    </div>

    <!-- Static Sample Schedule Section -->
    <div class="mt-6 space-y-4">
        <div class="grid grid-cols-[80px_1fr_1fr_1fr] border-t border-b border-black">
            <div class ="flex items-center justify-center flex-col">
                <div class="text-center font-bold text-sm">TUE</div>
                <div class="text-[#7B172E] font-extrabold text-2xl">01</div>
            </div>
            <div class="bg-gray-100 w-auto p-4 border-l border-black">
                <div class="font-bold">Intellectual Property Rights Seminar</div>
                <div class="text-sm">Gymnasium</div>
                <div class="text-sm">7:00 AM - 12:00 PM</div>
            </div>

            <div class="bg-gray-100 p-4 border-l border-black">
                <div class="font-bold">Faculty Meeting</div>
                <div class="text-sm">AVR</div>
                <div class="text-sm">8:00 AM - 10:00 AM</div>
            </div>

            <div class="bg-yellow-700 text-white p-4 border-l border-black">
                <div class="font-bold">University of the Philippines Exercises Practice</div>
                <div class="text-sm">Gymnasium</div>
                <div class="text-sm">1:00 PM - 4:00 PM</div>
            </div>
        </div>

        <div class="grid grid-cols-[80px_1fr_1fr] border-b border-black">
            <div class ="flex items-center justify-center flex-col">
                <div class="text-center font-bold text-sm">WED</div>
                <div class="text-[#7B172E] font-extrabold text-2xl">02</div>
            </div>
            <div class="bg-gray-100 p-4 border-l border-black">
                <div class="font-bold">Faculty Meeting</div>
                <div class="text-sm">AVR</div>
                <div class="text-sm">8:00 AM - 10:00 AM</div>
            </div>
            <div class="bg-cyan-700 text-white p-4 border-l border-black">
                <div class="font-bold">University of the Philippines Commencement Exercises Practice</div>
                <div class="text-sm">Gymnasium</div>
                <div class="text-sm">7:30 AM - 4:00 PM</div>
            </div>
        </div>

        <div class="grid grid-cols-[80px_1fr_1fr] border-b border-black">
            <div class ="flex items-center justify-center flex-col">
                <div class="text-center font-bold text-sm">THU</div>
                <div class="text-[#7B172E] font-extrabold text-2xl">03</div>
            </div>
            <div class="bg-gray-100 p-4 border-l border-black">
                <div class="font-bold">Student Body Organization Meeting</div>
                <div class="text-sm">Gymnasium</div>
                <div class="text-sm">9:00 AM - 10:00 AM</div>
            </div>
            <div class="bg-teal-700 text-white p-4 border-l border-black">
                <div class="font-bold">CSS Training</div>
                <div class="text-sm">Computer Laboratory 2</div>
                <div class="text-sm">7:30 AM - 4:00 PM</div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-maroon-800 {
        background-color: #6A1B1A;
    }
</style>

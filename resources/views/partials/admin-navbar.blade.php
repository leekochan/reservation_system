<nav class="fixed top-0 w-full text-white shadow-2xl z-50" style="background: #7B172E">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20">
            <div class="flex items-center">
                <div class="flex items-center -ml-36">
                    <div class="flex items-center">
                        <img
                            src="{{ asset('pictures/uplogo.jpg') }}"
                            alt="UP Cebu Logo"
                            class="h-[50px] object-contain rounded-full backdrop-blur-sm max-w-2xl mx-4 w-full max-h-[70vh]"
                        >
                    </div>
                </div>
                <div class="flex-shrink-0 flex items-center ml-2">
                    <span class="text-2xl font-bold">University of the Philippines Cebu</span>
                </div>
            </div>
            <div class="sm:ml-6 sm:flex sm:space-x-8">
                <a href="/admin" class="nav-link border-transparent text-gray-300 hover:border-white hover:text-white inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium" data-section="dashboard">Dashboard</a>
                <a href="/admin/reservations" class="nav-link border-transparent text-gray-300 hover:border-white hover:text-white inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium" data-section="reservations">Reservations</a>
                <a href="/admin/facilities" class="nav-link border-transparent text-gray-300 hover:border-white hover:text-white inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium" data-section="facilities">Facilities</a>
                <a href="/admin/equipments" class="nav-link border-transparent text-gray-300 hover:border-white hover:text-white inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium" data-section="equipments">Equipment</a>
                <a href="/admin/calendar" class="nav-link border-transparent text-gray-300 hover:border-white hover:text-white inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium" data-section="calendar">Calendar</a>
            </div>
        </div>
    </div>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const navLinks = document.querySelectorAll('.nav-link');
        const currentPath = window.location.pathname;

        // Highlight active nav item based on current path
        navLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (href) {
                // Exact match for dashboard
                if (currentPath === '/admin' && href === '/admin') {
                    link.classList.add('border-white', 'text-white');
                    link.classList.remove('border-transparent', 'text-gray-300');
                }
                // For other routes, check if current path starts with the href
                else if (href !== '/admin' && currentPath.startsWith(href)) {
                    link.classList.add('border-white', 'text-white');
                    link.classList.remove('border-transparent', 'text-gray-300');
                }
                // Default inactive state
                else {
                    link.classList.remove('border-white', 'text-white');
                    link.classList.add('border-transparent', 'text-gray-300');
                }
            }
        });
    });
</script>

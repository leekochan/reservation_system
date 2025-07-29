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
                <a href="/user" class="nav-link border-transparent text-gray-300 hover:border-white hover:text-white inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium" data-section="dashboard">Dashboard</a>
                <a href="/user/facilities" class="nav-link border-transparent text-gray-300 hover:border-white hover:text-white inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium" data-section="facilities">Facilities</a>
                <a href="/calendar_of_activities" class="nav-link border-transparent text-gray-300 hover:border-white hover:text-white inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium" data-section="calendar">Calendar</a>
                <a href="/user/reservation" class="nav-link border-transparent text-gray-300 hover:border-white hover:text-white inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium" data-section="reservation">Reservation</a>
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
                // Exact match for user dashboard
                if (currentPath === '/user' && href === '/user') {
                    link.classList.add('border-white', 'text-white');
                    link.classList.remove('border-transparent', 'text-gray-300');
                }
                // Exact match for calendar_of_activities
                else if (currentPath === '/calendar_of_activities' && href === '/calendar_of_activities') {
                    link.classList.add('border-white', 'text-white');
                    link.classList.remove('border-transparent', 'text-gray-300');
                }
                // Exact match for reservation
                else if (currentPath === '/reservation' && href === '/reservation') {
                    link.classList.add('border-white', 'text-white');
                    link.classList.remove('border-transparent', 'text-gray-300');
                }
                // For other routes (like /user/facilities), check if current path starts with the href
                else if (href !== '/user' && href !== '/calendar_of_activities' && href !== '/reservation' && currentPath.startsWith(href)) {
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

        const sections = document.querySelectorAll('section, div[id]');
        const headerHeight = document.querySelector('nav').offsetHeight;

        // Smooth scroll for nav links only when target is an in-page section
        navLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                const targetId = this.getAttribute('href');

                // Only prevent default and handle smooth scroll if it's a hash link
                if (targetId.startsWith('#')) {
                    e.preventDefault();
                    const targetSection = document.querySelector(targetId);
                    if (targetSection) {
                        window.scrollTo({
                            top: targetSection.offsetTop - headerHeight,
                            behavior: 'smooth'
                        });
                    }
                }
                // Otherwise, let the default anchor behavior happen (normal navigation)
            });
        });

        // Rest of your Intersection Observer code remains the same...
        const observerOptions = {
            root: null,
            rootMargin: `-${headerHeight}px 0px -50% 0px`,
            threshold: 0.1
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const sectionId = entry.target.id;
                    navLinks.forEach(link => {
                        if (link.dataset.section === sectionId) {
                            link.classList.add('border-white', 'text-white');
                            link.classList.remove('border-transparent', 'text-gray-300');
                        } else {
                            link.classList.remove('border-white', 'text-white');
                            link.classList.add('border-transparent', 'text-gray-300');
                        }
                    });
                }
            });
        }, observerOptions);

        sections.forEach(section => {
            if (section.id) {
                observer.observe(section);
            }
        });
    });
</script>

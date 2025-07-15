<nav class="fixed top-0 w-full text-white shadow-2xl z-50" style="background: #7B172E">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
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
                    <span class="text-xl font-bold">University of the Philippines Cebu</span>
                </div>
            </div>
            <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                <a href="#dashboard" class="nav-link border-transparent text-gray-300 hover:border-white hover:text-white inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium" data-section="dashboard">Dashboard</a>
                <a href="#facilities" class="nav-link border-transparent text-gray-300 hover:border-white hover:text-white inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium" data-section="facilities">Facilities</a>
                <a href="#calendar" class="nav-link border-transparent text-gray-300 hover:border-white hover:text-white inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium" data-section="calendar">Calendar</a>
                <a href="#reservation" class="nav-link border-transparent text-gray-300 hover:border-white hover:text-white inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium" data-section="reservation">Reservation</a>
            </div>
        </div>
    </div>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sections = document.querySelectorAll('section, div[id]');
        const navLinks = document.querySelectorAll('.nav-link');
        const headerHeight = document.querySelector('nav').offsetHeight;

        // Smooth scroll for nav links
        navLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                const targetSection = document.querySelector(targetId);
                if (targetSection) {
                    window.scrollTo({
                        top: targetSection.offsetTop - headerHeight,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Intersection Observer for scroll detection
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

        // Observe all sections
        sections.forEach(section => {
            if (section.id) {
                observer.observe(section);
            }
        });
    });
</script>

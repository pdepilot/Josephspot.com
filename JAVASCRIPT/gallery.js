        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Menu toggle function
        function toggleMenu() {
            const navLinks = document.querySelector('.nav-links');
            navLinks.classList.toggle('active');
        }

        // Scroll to top button
        const scrollTopBtn = document.getElementById('scrollTopBtn');

        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                scrollTopBtn.style.display = 'block';
            } else {
                scrollTopBtn.style.display = 'none';
            }
        });

        scrollTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Gallery filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            const filterButtons = document.querySelectorAll('.filter-btn');
            const galleryItems = document.querySelectorAll('.gallery-item');

            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    // Add active class to clicked button
                    this.classList.add('active');

                    const filterValue = this.getAttribute('data-filter');

                    galleryItems.forEach(item => {
                        if (filterValue === 'all' || item.classList.contains(filterValue)) {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            });
        });

        // Lightbox functionality
        function openLightbox(element) {
            const lightbox = document.getElementById('lightbox');
            const lightboxImg = document.getElementById('lightbox-img');
            const lightboxVideo = document.getElementById('lightbox-video');
            const lightboxTitle = document.getElementById('lightbox-title');
            const lightboxDescription = document.getElementById('lightbox-description');
            const lightboxLabel = document.getElementById('lightbox-label');

            // Get the label content from the clicked item
            const label = element.querySelector('.gallery-label');
            const title = label ? label.querySelector('.label-title').textContent : '';
            const description = label ? label.querySelector('.label-description').textContent : '';

            // Set lightbox content
            lightboxTitle.textContent = title;
            lightboxDescription.textContent = description;

            // Show label only if there's content
            if (title || description) {
                lightboxLabel.style.display = 'block';
            } else {
                lightboxLabel.style.display = 'none';
            }

            // Check if it's an image or video
            if (element.querySelector('img')) {
                const imgSrc = element.querySelector('img').getAttribute('src');
                lightboxImg.setAttribute('src', imgSrc);
                lightboxImg.style.display = 'block';
                lightboxVideo.style.display = 'none';
            } else if (element.querySelector('video')) {
                const videoSrc = element.querySelector('video').getAttribute('src');
                lightboxVideo.setAttribute('src', videoSrc);
                lightboxVideo.style.display = 'block';
                lightboxImg.style.display = 'none';
            }

            lightbox.style.display = 'flex';
        }

        function closeLightbox() {
            const lightbox = document.getElementById('lightbox');
            const lightboxVideo = document.getElementById('lightbox-video');
            
            lightbox.style.display = 'none';
            lightboxVideo.pause();
        }

        // Add click event to gallery items
        document.querySelectorAll('.gallery-item').forEach(item => {
            item.addEventListener('click', function() {
                openLightbox(this);
            });
        });

        // Close lightbox when clicking outside the content
        document.getElementById('lightbox').addEventListener('click', function(e) {
            if (e.target === this) {
                closeLightbox();
            }
        });
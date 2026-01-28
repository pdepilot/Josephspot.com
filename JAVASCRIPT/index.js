// ============================================
// PRE-LOADER SECTION STARTS HERE
// ============================================
document.addEventListener("DOMContentLoaded", function () {
  const progressBar = document.getElementById("progress-bar");
  const percentage = document.getElementById("percentage");
  const preloader = document.querySelector(".preloader");
  const content = document.querySelector(".content");
  const ingredients = document.querySelectorAll(".ingredient");
  const knife = document.querySelector(".knife-container");

  // ultra-fast progress simulation
  let progress = 0;
  const interval = setInterval(() => {
    progress += Math.random() * 20 + 10;
    if (progress >= 100) {
      progress = 100;
      clearInterval(interval);
      setTimeout(() => {
        preloader.style.opacity = "0";
        setTimeout(() => {
          preloader.style.display = "none";
          content.style.display = "block";
        }, 2000);
      }, 2000);
    }
    progressBar.style.width = progress + "%";
    percentage.textContent = Math.round(progress) + "%";
  }, 80);

  // Chop effect simulation (unchanged)
  setInterval(() => {
    ingredients.forEach((ing) => {
      if (ing.classList.contains("chopped")) return;
      const knifeRect = knife.getBoundingClientRect();
      const ingRect = ing.getBoundingClientRect();
      const overlap = !(
        knifeRect.right < ingRect.left ||
        knifeRect.left > ingRect.right ||
        knifeRect.bottom < ingRect.top ||
        knifeRect.top > ingRect.bottom
      );
      if (overlap && Math.random() > 0.5) {
        chopIngredient(ing);
      }
    });
  }, 300);

  function chopIngredient(ing) {
    ing.classList.add("chopped");
    const parent = ing.parentElement;

    // create slices
    for (let i = 0; i < 2; i++) {
      const slice = document.createElement("div");
      slice.className = "slice";
      slice.style.left = ing.offsetLeft + "px";
      slice.style.top = ing.offsetTop + (i === 0 ? 0 : 25) + "px";
      slice.style.height = "25px";
      const img = document.createElement("img");
      img.src = ing.querySelector("img").src;
      slice.appendChild(img);
      parent.appendChild(slice);
      setTimeout(() => slice.remove(), 500);
    }

    // create juice particles
    for (let i = 0; i < 6; i++) {
      const particle = document.createElement("div");
      particle.className = "particle";
      particle.style.left = ing.offsetLeft + 25 + "px";
      particle.style.top = ing.offsetTop + 25 + "px";
      const dx = (Math.random() - 0.5) * 100 + "px";
      const dy = (Math.random() - 0.5) * 100 + "px";
      particle.style.setProperty("--dx", dx);
      particle.style.setProperty("--dy", dy);
      particle.style.background = getJuiceColor(ing.alt);
      ing.parentElement.appendChild(particle);
      setTimeout(() => particle.remove(), 1000);
    }
  }

  function getJuiceColor(name) {
    if (name.includes("Tomato")) return "rgba(220,0,0,0.8)";
    if (name.includes("Chili")) return "rgba(255,60,0,0.8)";
    if (name.includes("Basil")) return "rgba(50,200,50,0.8)";
    if (name.includes("Garlic")) return "rgba(255,255,200,0.8)";
    if (name.includes("Onion")) return "rgba(180,80,180,0.8)";
    return "rgba(255,255,255,0.8)";
  }
});
// ============================================
// PRE-LOADER SECTION ENDS HERE
// ============================================

// ============================================
// SCROLL REVEAL SECTION STARTS HERE
// ============================================
document.addEventListener("DOMContentLoaded", function () {
  const revealElements = document.querySelectorAll(".reveal-on-scroll");

  function checkScroll() {
    const windowHeight = window.innerHeight;
    const revealPoint = 150;

    revealElements.forEach((element) => {
      const elementTop = element.getBoundingClientRect().top;

      if (elementTop < windowHeight - revealPoint) {
        element.classList.add("revealed");
      }
    });
  }

  // Initial check
  checkScroll();

  // Check on scroll
  window.addEventListener("scroll", checkScroll);

  // Check on resize
  window.addEventListener("resize", checkScroll);
});
// ============================================
// SCROLL REVEAL SECTION ENDS HERE
// ============================================

// ============================================
// BACKGROUND SLIDER SECTION STARTS HERE
// ============================================
document.addEventListener("DOMContentLoaded", function () {
  const images = [
    "./images/IM4.jpg",
    "./images/IM30.jpg",
    "./images/IM17.jpg",
    "./images/IM35.jpg",
  ];

  let index = 0;
  const animationClasses = [
    "slide-from-top",
    "slide-from-bottom",
    "slide-from-diagonal",
    "slide-from-left",
  ];

  const slider = document.createElement("div");
  slider.className = "bg-slider";
  document.querySelector(".welcome-hero").prepend(slider);

  function nextSlide() {
    slider.classList.remove("active", ...animationClasses);
    slider.style.backgroundImage = `url(${images[index]})`;
    const animation = animationClasses[index % animationClasses.length];
    slider.classList.add(animation);
    void slider.offsetWidth;
    slider.classList.add("active");
    index = (index + 1) % images.length;
  }

  // Start with first image immediately
  slider.style.backgroundImage = `url(${images[0]})`;
  slider.classList.add("slide-from-top", "active");
  setInterval(nextSlide, 5000);
});
// ============================================
// BACKGROUND SLIDER SECTION ENDS HERE
// ============================================

// ============================================
// NAVBAR SCROLL SECTION STARTS HERE
// ============================================
window.addEventListener("scroll", function () {
  const navbar = document.getElementById("navbar");
  if (window.scrollY > 50) {
    navbar.classList.add("scrolled");
  } else {
    navbar.classList.remove("scrolled");
  }
});
// ============================================
// NAVBAR SCROLL SECTION ENDS HERE
// ============================================

// ============================================
// MOBILE MENU SECTION STARTS HERE
// ============================================
function toggleMenu() {
  const navLinks = document.querySelector(".nav-links");
  navLinks.classList.toggle("active");
}
// ============================================
// MOBILE MENU SECTION ENDS HERE
// ============================================

// ============================================
// SCROLL TO TOP SECTION STARTS HERE
// ============================================
const scrollBtn = document.getElementById("scrollTopBtn");
window.onscroll = function () {
  if (
    document.body.scrollTop > 300 ||
    document.documentElement.scrollTop > 300
  ) {
    scrollBtn.style.display = "block";
  } else {
    scrollBtn.style.display = "none";
  }
};
scrollBtn.onclick = function () {
  window.scrollTo({ top: 0, behavior: "smooth" });
};
// ============================================
// SCROLL TO TOP SECTION ENDS HERE
// ============================================

// ============================================
// TYPING ANIMATION SECTION STARTS HERE
// ============================================
const phrases = [
  "Where Tradition Meets Taste...",
  "Bold Flavours. Rich Culture.",
  "Taste the Heart of Igbo Heritage.",
];

const typingElement = document.getElementById("typing-text");
let phraseIndex = 0;
let charIndex = 0;
let isDeleting = false;
let isMobile = window.innerWidth <= 768;

// Adjusted typing speeds (slower)
const typingSpeeds = {
  typing: 150,
  deleting: 80,
  pauseBeforeDelete: 4000,
  pauseBetweenPhrases: 1500,
};

function type() {
  const currentPhrase = phrases[phraseIndex];
  const currentText = currentPhrase.substring(0, charIndex);

  // Mobile optimization
  if (isMobile) {
    typingElement.textContent = currentText;
  } else {
    typingElement.innerHTML =
      currentText + (isDeleting ? "" : '<span class="cursor">|</span>');
  }

  if (!isDeleting && charIndex < currentPhrase.length) {
    // Typing
    charIndex++;
    setTimeout(type, typingSpeeds.typing);
  } else if (isDeleting && charIndex > 0) {
    // Deleting
    charIndex--;
    setTimeout(type, typingSpeeds.deleting);
  } else {
    // Switch between phrases
    isDeleting = !isDeleting;
    if (!isDeleting) {
      phraseIndex = (phraseIndex + 1) % phrases.length;
    }
    setTimeout(
      type,
      isDeleting
        ? typingSpeeds.pauseBeforeDelete
        : typingSpeeds.pauseBetweenPhrases
    );
  }
}

// Handle responsive changes
function handleResize() {
  const newIsMobile = window.innerWidth <= 768;
  if (newIsMobile !== isMobile) {
    isMobile = newIsMobile;
    // Reset animation when switching between mobile/desktop
    phraseIndex = 0;
    charIndex = 0;
    isDeleting = false;
    type();
  }
}

// Initialize
document.addEventListener("DOMContentLoaded", () => {
  type();
  window.addEventListener("resize", handleResize);

  // Set initial mobile state
  if (isMobile) {
    typingElement.classList.add("mobile-typing");
  }
});
// ============================================
// TYPING ANIMATION SECTION ENDS HERE
// ============================================

// ============================================
// FLIP CARD MENU SECTION STARTS HERE
// ============================================
function toggleFlip(card) {
  card.classList.toggle("flipped");

  if (window.innerWidth <= 768) {
    setTimeout(() => {
      card.classList.remove("flipped");
    }, 5000); // Flip back after 5 seconds
  }
}
document.querySelectorAll(".flip-card").forEach((card) => {
  card.addEventListener("click", function () {
    if (window.innerWidth <= 768) {
      this.classList.toggle("flip");
      setTimeout(() => this.classList.remove("flip"), 3500);
    }
  });

  // Navigate on click (optional)
  const link = card.querySelector("a");
  link.addEventListener("click", (e) => {
    e.stopPropagation();
    window.location.href = link.getAttribute("href");
  });
});

document.addEventListener("DOMContentLoaded", () => {
  const cards = document.querySelectorAll(".flip-card");
  const isMobile = window.matchMedia(
    "(hover: none), (pointer: coarse)"
  ).matches;

  if (isMobile) {
    cards.forEach((card) => {
      card.addEventListener("click", (e) => {
        // prevent double navigation
        if (e.target.classList.contains("flip-btn")) return;

        card.classList.toggle("flipped");
        setTimeout(() => {
          card.classList.remove("flipped");
        }, 5000);
      });
    });
  }
});

document.addEventListener("DOMContentLoaded", () => {
  const isMobile = window.innerWidth < 768;
  if (!isMobile) return;
  const hints = document.querySelectorAll(".tap-hint");
  const allFlipCards = document.querySelectorAll(".flip-card");
  let hintDismissed = false;

  // Use IntersectionObserver to show hints only when card is visible
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        const hint = entry.target.querySelector(".tap-hint");
        if (entry.isIntersecting && hint && !hintDismissed) {
          hint.style.display = "block";
        }
      });
    },
    {
      threshold: 0.5,
    }
  );

  allFlipCards.forEach((card) => {
    observer.observe(card);
    // Dismiss all hints on first user tap
    card.addEventListener(
      "click",
      () => {
        if (!hintDismissed) {
          hintDismissed = true;
          hints.forEach((h) => (h.style.display = "none"));
        }
      },
      { once: true }
    );
  });
});
// ============================================
// FLIP CARD MENU SECTION ENDS HERE
// ============================================

// ============================================
// UPCOMING EVENTS SECTION STARTS HERE (DYNAMIC)
// ============================================
let events = [];
let currentEventIndex = 0;
let countdownInterval = null;
let autoRotateInterval = null;
let isAnimating = false;
let autoRotateEnabled = true; // Will be set from database
let rotationInterval = 10000; // 10 seconds

// Fetch events and settings from database
async function fetchEventsFromDatabase() {
    try {
        const response = await fetch('fetch-events.php?t=' + Date.now());
        if (!response.ok) {
            throw new Error('Failed to fetch events');
        }
        const data = await response.json();
        
        events = data.events || [];
        autoRotateEnabled = data.settings?.auto_rotate !== false; // Default to true
        rotationInterval = data.settings?.interval || 5000;
        
        // If no events from database, use fallback
        if (events.length === 0 || (events.length === 1 && events[0].title === 'No Upcoming Events')) {
            events = [{
                title: 'No Upcoming Events',
                description: 'Check back soon for upcoming culinary experiences at Joseph\'s Pot!',
                date: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString(),
                eventImage: './images/IM41.jpg',
                status: 'upcoming'
            }];
        }
        
        // Initialize events display
        updateEventDisplay(0, 'next');
        
        // Show auto-rotation status (optional - add to your HTML)
        // updateRotationStatus();
        
        // Start auto-rotation if enabled
        if (autoRotateEnabled && events.length > 1) {
            startAutoRotation();
        }
    } catch (error) {
        console.error('Error fetching events:', error);
        events = [{
            title: 'No Events Available',
            description: 'We\'re currently preparing amazing events for you! Please check back later.',
            date: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString(),
            eventImage: './images/IM41.jpg',
            status: 'upcoming'
        }];
        updateEventDisplay(0, 'next');
        updateRotationStatus();
    }
}

// Update rotation status display (optional)
function updateRotationStatus() {
    const statusElement = document.getElementById('rotationStatus');
    if (!statusElement) {
        // Create status element if it doesn't exist
        const controls = document.querySelector('.event-controls');
        if (controls) {
            const statusDiv = document.createElement('div');
            statusDiv.id = 'rotationStatus';
            statusDiv.className = 'rotation-status';
            statusDiv.style.cssText = `
                font-size: 0.9rem;
                color: ${autoRotateEnabled ? '#4CAF50' : '#FF9800'};
                margin-top: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
            `;
            
            const icon = document.createElement('i');
            icon.className = autoRotateEnabled ? 'fas fa-play-circle' : 'fas fa-pause-circle';
            
            const text = document.createElement('span');
            text.textContent = autoRotateEnabled ? 
                'Auto-rotation: ON (slides every 10 seconds)' : 
                'Auto-rotation: OFF';
            
            statusDiv.appendChild(icon);
            statusDiv.appendChild(text);
            controls.appendChild(statusDiv);
        }
    } else {
        // Update existing status
        const icon = statusElement.querySelector('i');
        const text = statusElement.querySelector('span');
        
        if (icon) icon.className = autoRotateEnabled ? 'fas fa-play-circle' : 'fas fa-pause-circle';
        if (text) {
            text.textContent = autoRotateEnabled ? 
                'Auto-rotation: ON (slides every 10 seconds)' : 
                'Auto-rotation: OFF';
        }
        statusElement.style.color = autoRotateEnabled ? '#4CAF50' : '#FF9800';
    }
}

// Format date as "15 Aug"
function formatShortDate(dateStr) {
    const options = { day: "numeric", month: "short" };
    return new Date(dateStr).toLocaleDateString("en-US", options);
}

function updateEventDisplay(index, direction = 'next') {
    if (isAnimating || events.length === 0) return;
    isAnimating = true;
    
    const event = events[index];
    const eventDate = new Date(event.event_date || event.date);
    const eventCard = document.querySelector('.event-card');
    
    // Clear any existing interval first
    if (countdownInterval) {
        clearInterval(countdownInterval);
        countdownInterval = null;
    }
    
    // Add slide animation class
    if (eventCard) {
        eventCard.classList.add(`slide-${direction}`);
    }
    
    // After animation, update content
    setTimeout(() => {
        // Update all event content
        const eventTitle = document.getElementById("eventTitle");
        const eventDesc = document.getElementById("eventDesc");
        const eventImage = document.getElementById("eventImage");
        const eventDay = document.getElementById("eventDay");
        const eventDateElement = document.getElementById("eventDate");
        
        if (eventTitle) eventTitle.textContent = event.title;
        if (eventDesc) eventDesc.textContent = event.description;
        if (eventImage) {
            eventImage.src = event.image_url || event.eventImage || './images/IM41.jpg';
            // Add fade-in animation for image
            eventImage.style.opacity = '0';
            setTimeout(() => {
                eventImage.style.opacity = '1';
                eventImage.style.transition = 'opacity 0.5s ease';
            }, 100);
        }
        if (eventDay) eventDay.textContent = [
            "Sunday",
            "Monday",
            "Tuesday",
            "Wednesday",
            "Thursday",
            "Friday",
            "Saturday"
        ][eventDate.getDay()];
        if (eventDateElement) eventDateElement.textContent = formatShortDate(event.event_date || event.date);
        
        // Remove animation class
        if (eventCard) {
            setTimeout(() => {
                eventCard.classList.remove(`slide-${direction}`);
            }, 500);
        }
        
        // Update and start countdown for this event
        updateCountdown(event.event_date || event.date);
        countdownInterval = setInterval(() => updateCountdown(event.event_date || event.date), 1000);
        
        isAnimating = false;
        currentEventIndex = index;
    }, 500);
}

function updateCountdown(eventDateStr) {
    const now = new Date();
    const targetDate = new Date(eventDateStr);
    const diff = targetDate - now;

    // Get the countdown container
    const countdownContainer = document.getElementById("countdown");
    if (!countdownContainer) return;

    if (diff <= 0) {
        // Event has started or passed
        countdownContainer.innerHTML = `
            <div class="countdown-item">
                <span class="days countdown-value">00</span>
                <span class="countdown-label">Days</span>
            </div>
            <div class="countdown-separator">:</div>
            <div class="countdown-item">
                <span class="hours countdown-value">00</span>
                <span class="countdown-label">Hours</span>
            </div>
            <div class="countdown-separator">:</div>
            <div class="countdown-item">
                <span class="minutes countdown-value">00</span>
                <span class="countdown-label">Min</span>
            </div>
            <div class="countdown-separator">:</div>
            <div class="countdown-item">
                <span class="seconds countdown-value">00</span>
                <span class="countdown-label">Sec</span>
            </div>
            <div class="event-started">Event in Progress</div>
        `;
        
        if (countdownInterval) {
            clearInterval(countdownInterval);
            countdownInterval = null;
        }
        return;
    }

    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
    const minutes = Math.floor((diff / (1000 * 60)) % 60);
    const seconds = Math.floor((diff / 1000) % 60);

    // Update the countdown values
    const daysElement = document.querySelector('.days');
    const hoursElement = document.querySelector('.hours');
    const minutesElement = document.querySelector('.minutes');
    const secondsElement = document.querySelector('.seconds');
    
    // Remove any "event-started" message if it exists
    const eventStartedMsg = countdownContainer.querySelector('.event-started');
    if (eventStartedMsg) {
        eventStartedMsg.remove();
    }
    
    // Update countdown values only if elements exist
    if (daysElement) daysElement.textContent = days.toString().padStart(2, "0");
    if (hoursElement) hoursElement.textContent = hours.toString().padStart(2, "0");
    if (minutesElement) minutesElement.textContent = minutes.toString().padStart(2, "0");
    if (secondsElement) secondsElement.textContent = seconds.toString().padStart(2, "0");
}

// Initialize auto-rotation
function startAutoRotation() {
    if (!autoRotateEnabled || events.length <= 1) {
        stopAutoRotation();
        return;
    }
    
    if (autoRotateInterval) {
        clearInterval(autoRotateInterval);
    }
    
    autoRotateInterval = setInterval(() => {
        if (!autoRotateEnabled || events.length <= 1) {
            stopAutoRotation();
            return;
        }
        
        const nextIndex = (currentEventIndex + 1) % events.length;
        updateEventDisplay(nextIndex, 'next');
    }, rotationInterval);
}

function stopAutoRotation() {
    if (autoRotateInterval) {
        clearInterval(autoRotateInterval);
        autoRotateInterval = null;
    }
}

// Toggle auto-rotation manually (optional - add button to frontend)
function toggleFrontendAutoRotation() {
    autoRotateEnabled = !autoRotateEnabled;
    updateRotationStatus();
    
    if (autoRotateEnabled && events.length > 1) {
        startAutoRotation();
    } else {
        stopAutoRotation();
    }
    
    // Optional: Save user preference to localStorage
    localStorage.setItem('user_auto_rotate_pref', autoRotateEnabled);
}

// Navigation with proper slide animation
document.getElementById("nextEventBtn")?.addEventListener("click", () => {
    if (events.length <= 1) return;
    const nextIndex = (currentEventIndex + 1) % events.length;
    updateEventDisplay(nextIndex, 'next');
    
    // Reset auto-rotation timer on manual navigation
    if (autoRotateEnabled) {
        stopAutoRotation();
        startAutoRotation();
    }
});

// Add previous button if you want (optional)
const prevBtn = document.createElement('button');
prevBtn.id = 'prevEventBtn';
prevBtn.className = 'event-nav-button';
prevBtn.innerHTML = `
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M19 12H5M12 19l-7-7 7-7"/>
    </svg>
`;
prevBtn.setAttribute('aria-label', 'Previous event');

prevBtn.addEventListener('click', () => {
    if (events.length <= 1) return;
    const prevIndex = (currentEventIndex - 1 + events.length) % events.length;
    updateEventDisplay(prevIndex, 'prev');
    
    // Reset auto-rotation timer on manual navigation
    if (autoRotateEnabled) {
        stopAutoRotation();
        startAutoRotation();
    }
});

// Add previous button to controls
const eventControls = document.querySelector('.event-controls');
if (eventControls && !document.getElementById('prevEventBtn')) {
    eventControls.insertBefore(prevBtn, document.getElementById('nextEventBtn'));
}

// Pause auto-rotation on hover/focus
const eventContainer = document.getElementById("eventContainer");
if (eventContainer) {
    eventContainer.addEventListener("mouseenter", () => {
        if (autoRotateInterval) {
            clearInterval(autoRotateInterval);
        }
    });
    
    eventContainer.addEventListener("mouseleave", () => {
        if (autoRotateEnabled && !autoRotateInterval && events.length > 1) {
            startAutoRotation();
        }
    });
    
    // Also handle touch devices
    eventContainer.addEventListener("touchstart", () => {
        if (autoRotateInterval) {
            clearInterval(autoRotateInterval);
        }
    });
    
    eventContainer.addEventListener("touchend", () => {
        setTimeout(() => {
            if (autoRotateEnabled && !autoRotateInterval && events.length > 1) {
                startAutoRotation();
            }
        }, 3000);
    });
}

// Clean up intervals when page is hidden
document.addEventListener("visibilitychange", () => {
    if (document.hidden) {
        if (countdownInterval) {
            clearInterval(countdownInterval);
            countdownInterval = null;
        }
        if (autoRotateInterval) {
            clearInterval(autoRotateInterval);
            autoRotateInterval = null;
        }
    } else {
        // Resume when page becomes visible again
        if (!countdownInterval && events.length > 0) {
            const currentEvent = events[currentEventIndex];
            updateCountdown(currentEvent.event_date || currentEvent.date);
            countdownInterval = setInterval(() => updateCountdown(currentEvent.event_date || currentEvent.date), 1000);
        }
        if (!autoRotateInterval && autoRotateEnabled && events.length > 1) {
            startAutoRotation();
        }
    }
});

// Initialize events when DOM is ready
function initEventsSlider() {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            fetchEventsFromDatabase();
        });
    } else {
        fetchEventsFromDatabase();
    }
}

// Call initialization
initEventsSlider();

// Add CSS for slide animations and status indicator
function addEventSliderStyles() {
    if (!document.getElementById('event-slider-styles')) {
        const style = document.createElement('style');
        style.id = 'event-slider-styles';
        style.textContent = `
            /* Animation styles */
            .event-card {
                transition: transform 0.5s ease, opacity 0.5s ease;
            }
            
            .event-card.slide-next {
                animation: slideNext 0.5s ease forwards;
            }
            
            .event-card.slide-prev {
                animation: slidePrev 0.5s ease forwards;
            }
            
            @keyframes slideNext {
                0% {
                    transform: translateX(100%);
                    opacity: 0;
                }
                100% {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slidePrev {
                0% {
                    transform: translateX(-100%);
                    opacity: 0;
                }
                100% {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            /* Countdown pulse animation */
            .countdown-value {
                animation: countdownPulse 1s infinite alternate;
            }
            
            @keyframes countdownPulse {
                from {
                    transform: scale(1);
                }
                to {
                    transform: scale(1.05);
                }
            }
            
            /* Event image fade animation */
            .event-image {
                transition: opacity 0.5s ease;
            }
            
            /* Event started indicator */
            .event-started {
                width: 100%;
                text-align: center;
                font-size: 1.5rem;
                color: #4CAF50;
                font-weight: bold;
                margin-top: 10px;
                padding: 10px;
                background: rgba(76, 175, 80, 0.1);
                border-radius: 5px;
                border: 2px solid #4CAF50;
                animation: pulse 2s infinite;
            }
            
            @keyframes pulse {
                0% {
                    opacity: 0.7;
                }
                50% {
                    opacity: 1;
                }
                100% {
                    opacity: 0.7;
                }
            }
            
            /* Auto-rotation status indicator */
            .rotation-status {
                font-size: 0.9rem;
                margin-top: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                animation: fadeIn 0.5s ease;
            }
            
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            
            /* Event controls container */
            .event-controls {
                display: flex;
                justify-content: center;
                align-items: center;
                gap: 20px;
                margin-top: 30px;
                flex-wrap: wrap;
            }
            
            /* Navigation buttons */
            .event-nav-button {
                width: 50px;
                height: 50px;
                border-radius: 50%;
                background: #8b4513;
                border: none;
                color: white;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.3s ease;
                box-shadow: 0 4px 15px rgba(139, 69, 19, 0.3);
            }
            
            .event-nav-button:hover {
                background: #654321;
                transform: scale(1.1);
                box-shadow: 0 6px 20px rgba(139, 69, 19, 0.5);
            }
            
            .event-nav-button:active {
                transform: scale(0.95);
            }
            
            /* Hide navigation if only one event */
            .event-controls.hidden {
                display: none;
            }
            
            /* Responsive adjustments */
            @media (max-width: 768px) {
                .event-controls {
                    gap: 10px;
                    margin-top: 20px;
                }
                
                .event-nav-button {
                    width: 40px;
                    height: 40px;
                }
                
                .rotation-status {
                    font-size: 0.8rem;
                }
            }
        `;
        document.head.appendChild(style);
    }
}

// Add the styles
addEventSliderStyles();
// ============================================
// UPCOMING EVENTS SECTION ENDS HERE (DYNAMIC)
// ============================================


// ============================================
// RESERVATION FORM SECTION STARTS HERE

document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("reservationForm");
  const successNotification = document.getElementById("successMessage");
  const errorNotification = document.getElementById("errorMessage");

  // Initialize date picker with min/max dates
  const dateInput = document.getElementById("date");
  const today = new Date();
  const maxDate = new Date();
  maxDate.setMonth(today.getMonth() + 3);

  dateInput.min = today.toISOString().split("T")[0];
  dateInput.max = maxDate.toISOString().split("T")[0];

  // Set default time to next available slot
  const timeInput = document.getElementById("time");
  const hours = today.getHours();
  let defaultTime = "19:00"; // Default to 7 PM

  if (hours < 11) defaultTime = "11:00";
  else if (hours < 13) defaultTime = "12:00";
  else if (hours < 14) defaultTime = "13:00";
  else if (hours < 18) defaultTime = "14:00";
  else if (hours < 19) defaultTime = "18:00";
  else if (hours < 20) defaultTime = "19:00";
  else if (hours < 21) defaultTime = "20:00";
  else if (hours < 22) defaultTime = "21:00";

  timeInput.querySelector(`option[value="${defaultTime}"]`).selected = true;

  // Form submission handler - UPDATED TO SAVE TO DATABASE
  form.addEventListener("submit", async function (e) {
    e.preventDefault();

    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.querySelector(".btn-text").textContent;
    submitBtn.querySelector(".btn-text").textContent = "Processing...";
    submitBtn.disabled = true;

    try {
      // Get form data
      const formData = new FormData(form);
      const formObject = {};
      formData.forEach((value, key) => {
        formObject[key] = value;
      });

      // Submit to our PHP handler
      const response = await fetch('submit-reservation.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(formObject)
      });

      const result = await response.json();

      if (result.success) {
        // Show success message
        successNotification.style.display = "flex";
        errorNotification.style.display = "none";
        
        // Optionally reset the form
        form.reset();
        
        // Reset floating labels
        document.querySelectorAll(".floating input, .floating textarea, .floating select")
          .forEach((el) => {
            el.dispatchEvent(new Event("change"));
          });
        
        // Hide success message after 5 seconds
        setTimeout(() => {
          successNotification.style.display = "none";
        }, 5000);
      } else {
        throw new Error(result.message || "Form submission failed");
      }
    } catch (error) {
      console.error("Error:", error);
      errorNotification.style.display = "flex";
      successNotification.style.display = "none";
      
      // Hide error message after 5 seconds
      setTimeout(() => {
        errorNotification.style.display = "none";
      }, 5000);
    } finally {
      // Reset button state
      submitBtn.querySelector(".btn-text").textContent = originalBtnText;
      submitBtn.disabled = false;
    }
  });

  // Rest of your existing JavaScript remains the same...
  // Enhance select elements
  document.querySelectorAll("select").forEach((select) => {
    select.addEventListener("change", function () {
      this.dispatchEvent(new Event("input"));
    });
  });

  // Add animation to form elements on page load
  setTimeout(() => {
    document.querySelectorAll(".form-group").forEach((el, i) => {
      el.style.animation = `fadeInUp 0.5s ease ${i * 0.1}s forwards`;
      el.style.opacity = "0";
    });
  }, 100);
});

// Add CSS animation
const style = document.createElement("style");
style.textContent = `
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
`;
document.head.appendChild(style);
// ============================================
// RESERVATION FORM SECTION ENDS HERE
// ============================================

// ============================================
// REVIEWS SLIDER SECTION STARTS HERE
// ============================================
document.addEventListener("DOMContentLoaded", () => {
  let slides = [];
  const dotsContainer = document.getElementById("sliderDots");
  const prevBtn = document.getElementById("prevBtn");
  const nextBtn = document.getElementById("nextBtn");
  const headings = document.querySelectorAll(".review-heading");
  const reviewContainer = document.querySelector(".review-container");
  let currentIndex = 0;
  let currentHeading = 0;
  let slideInterval;
  const slideDirections = [
    "slide-in-left",
    "slide-in-right",
    "slide-in-top",
    "slide-in-bottom",
  ];

  // New review functionality
  const addReviewBtn = document.getElementById("addReviewBtn");
  const reviewFormOverlay = document.getElementById("reviewFormOverlay");
  const closeReviewForm = document.getElementById("closeReviewForm");
  const submitReviewForm = document.getElementById("submitReviewForm");
  const cancelReviewBtn = document.getElementById("cancelReviewBtn");
  const stars = document.querySelectorAll(".star");
  const reviewRatingInput = document.getElementById("reviewRating");

  // Star rating functionality
  stars.forEach((star) => {
    star.addEventListener("click", () => {
      const value = parseInt(star.getAttribute("data-value"));
      reviewRatingInput.value = value;
      updateStars(value);
    });
  });

  function updateStars(rating) {
    stars.forEach((star) => {
      const value = parseInt(star.getAttribute("data-value"));
      star.classList.toggle("active", value <= rating);
    });
  }

  // Initialize with 5 stars
  updateStars(5);

  // Load reviews from database
  async function loadReviews() {
    try {
      const response = await fetch('fetch-reviews.php');
      const reviews = await response.json();

      // Clear existing slides
      reviewContainer.innerHTML = "";

      if (reviews.length === 0) {
        reviewContainer.innerHTML = "<p>No reviews yet. Be the first to review!</p>";
        return;
      }

      reviews.forEach((review, index) => {
        const newSlide = document.createElement("div");
        newSlide.className = `review-slide ${
          slideDirections[index % slideDirections.length]
        }`;

        newSlide.innerHTML = `
          <div class="slide-image-container">
            <img class="review-img" src="${
              review.imageUrl ||
              "https://randomuser.me/api/portraits/neutral/default.jpg"
            }" alt="${review.name}" loading="lazy">
          </div>
          <div class="review-text">"${review.text}"</div>
          <div class="review-stars">${"★".repeat(review.rating)}${"☆".repeat(5 - review.rating)}</div>
          <div class="review-author">– ${review.name}</div>
          <div class="review-date">${review.date}</div>
        `;

        reviewContainer.appendChild(newSlide);
      });

      // Update slides array
      slides = document.querySelectorAll(".review-slide");

      // Start with first slide
      if (slides.length > 0) {
        showSlide(0);
        startSlideShow();
      }

      updateDots();
    } catch (error) {
      console.error('Error loading reviews:', error);
      // Fallback to localStorage if database fails
      loadFromLocalStorage();
    }
  }

  // Fallback to localStorage
  function loadFromLocalStorage() {
    // Initialize with sample reviews if none exist
    function initializeSampleReviews() {
      const sampleReviews = [
        {
          id: "1",
          name: "Apama Tv",
          text: "I blacked out after the first spoonful. I swear I don't remember finishing the plate. One bite of that egusi soup and I woke up with tears in my eyes and an empty bowl. This food doesn't play!",
          rating: 5,
          imageUrl: "https://randomuser.me/api/portraits/men/32.jpg",
          date: "March 15, 2025"
        },
        {
          id: "2",
          name: "Ken Udosi",
          text: 'My ancestors whispered "thank you" after the meal. That okra soup must\'ve been made with palm oil straight from heaven. I felt my lineage rejoicing.',
          rating: 4,
          imageUrl: "https://randomuser.me/api/portraits/men/44.jpg",
          date: "March 14, 2025"
        },
        {
          id: "3",
          name: "Priya S",
          text: "I ordered takeaway and ate it before I reached my car. I was meant to save it for later. That aroma seduced me in the parking lot. I never stood a chance.",
          rating: 5,
          imageUrl: "https://randomuser.me/api/portraits/women/63.jpg",
          date: "March 13, 2025"
        }
      ];

      if (!localStorage.getItem("userReviews")) {
        localStorage.setItem("userReviews", JSON.stringify(sampleReviews));
      }
    }

    initializeSampleReviews();
    const savedReviews = JSON.parse(localStorage.getItem("userReviews")) || [];

    // Clear existing slides
    reviewContainer.innerHTML = "";

    if (savedReviews.length === 0) {
      reviewContainer.innerHTML = "<p>No reviews yet. Be the first to review!</p>";
      return;
    }

    savedReviews.forEach((review, index) => {
      const newSlide = document.createElement("div");
      newSlide.className = `review-slide ${
        slideDirections[index % slideDirections.length]
      }`;

      newSlide.innerHTML = `
        <div class="slide-image-container">
          <img class="review-img" src="${
            review.imageUrl ||
            "https://randomuser.me/api/portraits/neutral/default.jpg"
          }" alt="${review.name}" loading="lazy">
        </div>
        <div class="review-text">"${review.text}"</div>
        <div class="review-stars">${"★".repeat(review.rating)}${"☆".repeat(5 - review.rating)}</div>
        <div class="review-author">– ${review.name}</div>
        ${review.date ? `<div class="review-date">${review.date}</div>` : ''}
      `;

      reviewContainer.appendChild(newSlide);
    });

    // Update slides array
    slides = document.querySelectorAll(".review-slide");

    // Start with first slide
    if (slides.length > 0) {
      showSlide(0);
      startSlideShow();
    }

    updateDots();
  }

  // Toggle review form visibility
  addReviewBtn.addEventListener("click", () => {
    reviewFormOverlay.style.display = "flex";
    stopSlideShow();
  });

  closeReviewForm.addEventListener("click", () => {
    closeReviewFormHandler();
  });

  cancelReviewBtn.addEventListener("click", () => {
    closeReviewFormHandler();
  });

  function closeReviewFormHandler() {
    reviewFormOverlay.style.display = "none";
    submitReviewForm.reset();
    reviewRatingInput.value = "5";
    updateStars(5);
    startSlideShow();
  }

  // Close form when clicking outside
  reviewFormOverlay.addEventListener("click", (e) => {
    if (e.target === reviewFormOverlay) {
      closeReviewFormHandler();
    }
  });

  // Form submission
  submitReviewForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    const name = document.getElementById("reviewName").value.trim();
    const text = document.getElementById("reviewText").value.trim();
    const rating = parseInt(reviewRatingInput.value);
    const imageInput = document.getElementById("reviewImage");

    if (!name || !text) {
      showActionMessage("Please fill in all required fields", "#f44336");
      return;
    }

    // Create FormData object
    const formData = new FormData();
    formData.append('name', name);
    formData.append('review', text);
    formData.append('rating', rating);
    
    // Add image if exists
    if (imageInput.files.length > 0) {
      formData.append('image', imageInput.files[0]);
    }

    try {
      const response = await fetch('submit-review.php', {
        method: 'POST',
        body: formData
      });

      const result = await response.json();
      
      if (result.success) {
        // Reset form
        submitReviewForm.reset();
        reviewRatingInput.value = "5";
        updateStars(5);
        reviewFormOverlay.style.display = "none";
        
        // Show success message
        showActionMessage("Review submitted successfully! It will appear after approval.", "#4CAF50");
        
        // Refresh reviews
        loadReviews();
      } else {
        showActionMessage("Error: " + result.message, "#f44336");
      }
    } catch (error) {
      console.error('Error submitting review:', error);
      showActionMessage("Network error. Please try again.", "#f44336");
    }
  });

  // Slider functions
  function updateDots() {
    dotsContainer.innerHTML = "";
    slides.forEach((_, index) => {
      const dot = document.createElement("span");
      dot.classList.add("dot");
      if (index === currentIndex) dot.classList.add("active");
      dot.dataset.index = index;
      dot.addEventListener("click", () => {
        showSlide(index);
        resetSlideShow();
      });
      dotsContainer.appendChild(dot);
    });
  }

  function showSlide(index) {
    // Wrap around if at ends
    if (index >= slides.length) index = 0;
    if (index < 0) index = slides.length - 1;

    // Hide all slides
    slides.forEach((slide) => {
      slide.classList.remove("active");
    });

    // Show the selected slide with animation
    slides[index].classList.add("active");

    // Update current index
    currentIndex = index;

    // Update dots
    document
      .querySelectorAll(".dot")
      .forEach((dot) => dot.classList.remove("active"));
    const activeDot = document.querySelector(`.dot[data-index="${index}"]`);
    if (activeDot) activeDot.classList.add("active");
  }

  function nextSlide() {
    showSlide(currentIndex + 1);
  }

  function prevSlide() {
    showSlide(currentIndex - 1);
  }

  function startSlideShow() {
    stopSlideShow();
    slideInterval = setInterval(nextSlide, 5000);
  }

  function stopSlideShow() {
    clearInterval(slideInterval);
  }

  function resetSlideShow() {
    stopSlideShow();
    startSlideShow();
  }

  // Header animation
  function cycleHeadings() {
    headings.forEach((h) => (h.style.display = "none"));
    headings[currentHeading].style.display = "block";
    currentHeading = (currentHeading + 1) % headings.length;
    setTimeout(cycleHeadings, 3000);
  }
  
  // Start header animation
  if (headings.length > 0) {
    cycleHeadings();
  }

  // Event listeners for navigation
  if (prevBtn) {
    prevBtn.addEventListener("click", () => {
      prevSlide();
      resetSlideShow();
    });
  }

  if (nextBtn) {
    nextBtn.addEventListener("click", () => {
      nextSlide();
      resetSlideShow();
    });
  }

  // Keyboard navigation
  document.addEventListener("keydown", (e) => {
    if (e.key === "ArrowLeft") {
      prevSlide();
      resetSlideShow();
    } else if (e.key === "ArrowRight") {
      nextSlide();
      resetSlideShow();
    } else if (
      e.key === "Escape" &&
      reviewFormOverlay.style.display === "flex"
    ) {
      closeReviewFormHandler();
    }
  });

  // Show action message
  function showActionMessage(text, color) {
    // Remove existing message if any
    const existingMessage = document.querySelector('.action-message');
    if (existingMessage) {
      existingMessage.remove();
    }

    const message = document.createElement("div");
    message.className = "action-message";
    message.textContent = text;
    message.style.cssText = `
      position: fixed;
      top: 20px;
      right: 20px;
      background-color: ${color};
      color: white;
      padding: 12px 24px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      z-index: 10000;
      animation: slideInRight 0.3s ease, fadeOut 0.3s ease 3s forwards;
    `;
    
    // Add animation styles if not already present
    if (!document.querySelector('#action-message-styles')) {
      const style = document.createElement('style');
      style.id = 'action-message-styles';
      style.textContent = `
        @keyframes slideInRight {
          from { transform: translateX(100%); opacity: 0; }
          to { transform: translateX(0); opacity: 1; }
        }
        @keyframes fadeOut {
          to { opacity: 0; transform: translateX(100%); }
        }
      `;
      document.head.appendChild(style);
    }
    
    document.body.appendChild(message);
    setTimeout(() => {
      if (message.parentNode) {
        message.remove();
      }
    }, 3000);
  }

  // Initialize the reviews slider
  loadReviews();
});
// ============================================
// REVIEWS SLIDER SECTION ENDS HERE
// ============================================

// ============================================
// WHATSAPP LINK SECTION STARTS HERE
// ============================================
const whatsappNumber = "2349064296917";
const message = "Hello! I'd like to place an order or ask about your menu.";
const whatsappURL = `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(
  message
)}`;

// Function to open WhatsApp
function openWhatsApp() {
  window.open(whatsappURL, "_blank");
}
// ============================================
// WHATSAPP LINK SECTION ENDS HERE
// ============================================

/* ============================================
   AI CHATBOT SECTION STARTS HERE
   ============================================ */

const chatContainer = document.getElementById("aiChat-container");
const messagesDiv = document.getElementById("aiChat-messages");
const openBtn = document.getElementById("aiChat-open-btn");
const closeBtn = document.getElementById("aiChat-close");
const liveBtn = document.getElementById("ai-start-live");
const waBtn = document.getElementById("ai-start-whatsapp");
const welcomeBox = document.getElementById("aiChat-welcome-options");
const inputArea = document.getElementById("aiChat-input-area");
const userInput = document.getElementById("aiChat-user-input");
const sendBtn = document.getElementById("aiChat-send-btn");
const micBtn = document.getElementById("aiChat-mic-btn");

// Defensive checks
if (!chatContainer) console.warn("aiChat-container not found");
if (!messagesDiv) console.warn("aiChat-messages not found");
if (!openBtn) console.warn("aiChat-open-btn not found");
if (!liveBtn) console.warn("ai-start-live not found");

// --- Sounds ---
const openSound = new Audio(
  "https://assets.mixkit.co/sfx/preview/mixkit-game-notification-alert-1075.mp3"
);
const messageSound = new Audio(
  "https://assets.mixkit.co/sfx/preview/mixkit-positive-interface-echo-3157.mp3"
);

// ====== SMALL-TALK DICTIONARY ======
const smallTalkDictionary = {
  // Greetings
  hello: ["Hello there!", "Hi!", "Hey! How can I help you today?"],
  hi: ["Hello!", "Hi there!", "Hey! What can I do for you?"],
  hey: ["Hello!", "Hi there!", "Hey! How can I assist you?"],
  "good morning": [
    "Good morning! How are you today?",
    "Morning! Ready for some delicious food?",
  ],
  "good afternoon": [
    "Good afternoon! How's your day going?",
    "Afternoon! What can I get for you?",
  ],
  "good evening": [
    "Good evening! How was your day?",
    "Evening! Ready to order some tasty food?",
  ],

  // How are you
  "how are you": [
    "I'm doing great, thanks for asking! Ready to help you with your order.",
    "I'm wonderful! Excited to help you get some delicious food.",
    "I'm doing well, thank you! How about you?",
  ],

  // Who are you
  "who are you": [
    "I'm Joseph's Pot assistant! I'm here to help you order delicious Nigerian cuisine.",
    "I'm your friendly food assistant at Joseph's Pot. I can help you place orders and answer questions.",
    "I'm an AI assistant created to help you enjoy the best food from Joseph's Pot!",
  ],

  // What can you do
  "what can you do": [
    "I can help you order food, answer questions about our menu, tell you about specials, and more!",
    "I can take your order, suggest menu items, help with checkout, and answer any questions you have about Joseph's Pot.",
    "I'm here to help you browse our menu, place orders, and make your food experience delightful!",
  ],

  // Thank you
  "thank you": [
    "You're welcome!",
    "My pleasure!",
    "Happy to help!",
    "Anytime!",
  ],
  thanks: ["You're welcome!", "No problem!", "Happy to assist!"],

  // Goodbye
  bye: [
    "Goodbye! Hope to see you again soon.",
    "Bye! Come back whenever you're hungry!",
    "See you later!",
  ],
  goodbye: [
    "Goodbye! Enjoy your day!",
    "Farewell! Don't hesitate to return if you need anything.",
  ],

  // Compliments
  "you are helpful": [
    "Thank you! I'm here to make your ordering experience easy and enjoyable.",
    "I appreciate that! Making your food journey smooth is my goal.",
  ],
  "you are smart": [
    "Thank you! I'm always learning to serve you better.",
    "That's kind of you to say! I try my best to be helpful.",
  ],

  // Menu questions
  "what do you serve": [
    "We serve authentic Nigerian cuisine like Nkwobi, Abacha, Nsala, Asun Rice, Ofe Owerri, and more!",
    "Our menu features delicious Nigerian dishes including Nkwobi, Abacha, Jollof rice, Palm Wine, and various drinks.",
  ],
  "what is on the menu": [
    "Our menu includes Nigerian favorites like Nkwobi, Abacha, Nsala, Asun Rice, Ofe Owerri, Palm Wine, Drinks, and Jollof rice.",
    "You can choose from various Nigerian dishes: Nkwobi, Abacha, Nsala, Asun Rice, Ofe Owerri, plus drinks and Palm Wine.",
  ],

  // Restaurant info
  "where are you located": [
    "Joseph's Pot is located at [Your Address Here]. We offer delivery and pickup options!",
    "You can find us at [Your Address Here]. Would you like delivery or pickup?",
  ],
  "what are your hours": [
    "We're open [Your Hours Here]. Feel free to order anytime through our website!",
    "Our operating hours are [Your Hours Here]. You can place orders for delivery or pickup during these times.",
  ],

  // Special requests
  "do you have vegan options": [
    "We offer some plant-based Nigerian dishes. Let me check what's available for you!",
    "While traditional Nigerian cuisine often includes meat, we do have some vegan-friendly options. Let me help you find something!",
  ],
  "do you deliver": [
    "Yes, we deliver! Please provide your address so we can check if you're in our delivery area.",
    "We certainly deliver! Let me know your location and I'll check our delivery coverage.",
  ],
};

// Enhanced pattern matching for small talk
function matchSmallTalk(input) {
  const lowerInput = input.toLowerCase().trim();

  // Exact matches
  if (smallTalkDictionary[lowerInput]) {
    const responses = smallTalkDictionary[lowerInput];
    return responses[Math.floor(Math.random() * responses.length)];
  }

  // Pattern matches
  for (const [pattern, responses] of Object.entries(smallTalkDictionary)) {
    if (lowerInput.includes(pattern)) {
      return responses[Math.floor(Math.random() * responses.length)];
    }
  }

  // Special cases with more complex matching
  if (/how('s| is) it going/.test(lowerInput)) {
    return "It's going well, thank you! How about you?";
  }

  if (/what('s| is) up/.test(lowerInput)) {
    return "Not much, just here to help you with your food order! What can I get for you?";
  }

  if (/how('s| is) your day/.test(lowerInput)) {
    return "My day is great so far! How's yours going?";
  }

  if (/(nice|good|great) to meet you/.test(lowerInput)) {
    return "Likewise! I'm excited to help you discover our delicious food options.";
  }

  if (/what('s| is) new/.test(lowerInput)) {
    return "We have some delicious specials today! Would you like to hear about them?";
  }

  if (/what('s| is) cooking/.test(lowerInput)) {
    return "Lots of delicious Nigerian dishes are cooking! Would you like to hear about our menu?";
  }

  if (/i'm (hungry|starving)/.test(lowerInput)) {
    return "You've come to the right place! Let me show you some delicious options.";
  }

  return null;
}

// ====== TEXT-TO-SPEECH (TTS) ======
let voicesList = [];
let preferredVoice = null;
let voicesLoaded = false;
let pendingSpeech = null;
let speechUnlocked = false;

function loadVoices() {
  voicesList =
    window.speechSynthesis && window.speechSynthesis.getVoices
      ? window.speechSynthesis.getVoices()
      : [];
  preferredVoice = voicesList.find(
    (v) =>
      /ng/i.test(v.lang || "") && /female/i.test((v.name || "").toLowerCase())
  );
  if (!preferredVoice) {
    preferredVoice = voicesList.find((v) => /ng/i.test(v.lang || ""));
    if (!preferredVoice)
      preferredVoice = voicesList.find((v) =>
        /female/i.test((v.name || "").toLowerCase())
      );
    if (!preferredVoice)
      preferredVoice = voicesList.find((v) => /en/i.test(v.lang || ""));
  }
  voicesLoaded = voicesList.length > 0;
}
if (typeof window.speechSynthesis !== "undefined") {
  window.speechSynthesis.onvoiceschanged = loadVoices;
  loadVoices();
}

function speakBot(text) {
  if (!("speechSynthesis" in window)) return false;
  try {
    window.speechSynthesis.cancel();
    if (!voicesLoaded) loadVoices();
    const utter = new SpeechSynthesisUtterance(text);
    if (preferredVoice) utter.voice = preferredVoice;
    utter.lang =
      preferredVoice && preferredVoice.lang ? preferredVoice.lang : "en-NG";
    utter.rate = 1;
    utter.pitch = 1;
    window.speechSynthesis.speak(utter);
    return true;
  } catch (err) {
    console.warn("speakBot error:", err);
    return false;
  }
}

function trySpeak(text) {
  try {
    if (typeof text === "string") {
      const isCartConfirmation =
        /added[\s\S]*\b(cart|to your cart|to cart)\b/i.test(text) ||
        /\badded to your cart\b/i.test(text) ||
        /\badded to cart\b/i.test(text);
      if (isCartConfirmation) return;
    }
  } catch (e) {
    console.warn("trySpeak cart-suppression check error:", e);
  }
  if (speechUnlocked) {
    const ok = speakBot(text);
    if (!ok) pendingSpeech = text;
  } else {
    const ok = speakBot(text);
    if (!ok) pendingSpeech = text;
  }
}

function unlockSpeech() {
  if (speechUnlocked) return;
  speechUnlocked = true;
  setTimeout(() => {
    if (pendingSpeech) {
      trySpeak(pendingSpeech);
      pendingSpeech = null;
    }
  }, 100);
}

document.addEventListener("click", unlockSpeech, { once: true });
document.addEventListener("keydown", unlockSpeech, { once: true });

// ====== TIME-BASED GREETING ======
function getTimeGreeting() {
  const hour = new Date().getHours();
  if (hour < 12) return "Good morning";
  if (hour < 18) return "Good afternoon";
  return "Good evening";
}

// --- State ---
let awaitingName = false;
let awaitingAddress = false;
let suggestionShown = false;
let cartClickCount = 0;
let previouslySuggestedItems = new Set(); // Track suggested items to avoid repetition

let savedName = localStorage.getItem("aiChat-name") || null;
let savedAddress = localStorage.getItem("aiChat-address") || null;
let lastOrder = JSON.parse(localStorage.getItem("aiChat-lastOrder") || "[]");

// === Map chat food names -> your real menu item IDs (update IDs to match your menuItems) ===
const chatFoodMap = {
  Nkwobi: 1,
  Abacha: 2,
  Nsala: 3,
  "Asun Rice": 4,
  "Ofe Owerri": 5,
  "Palm Wine": 6,
  Drinks: 7,
  Jollof: 8,
};

// --- Enhanced price map with categories for intelligent pairing ---
const menuPrices = {
  nkwobi: 3000,
  abacha: 1500,
  nsala: 2500,
  "asun rice": 3500,
  "ofe owerri": 3200,
  "palm wine": 800,
  drinks: 500,
  jollof: 2500,
};

// --- Food categories for intelligent pairing ---
const foodCategories = {
  soups: ["nsala", "ofe owerri", "nkwobi"],
  rice: ["asun rice", "jollof"],
  swallow: ["abacha"], // Can add more swallow items
  drinks: ["palm wine", "drinks"],
  proteins: ["nkwobi", "abacha"]
};

// --- Conversational responses ---
const conversationalResponses = {
  itemAdded: [
    "Perfect! I've added {item} to your cart.",
    "Great choice! {item} is now in your cart.",
    "Excellent! {item} added successfully.",
    "Done! I've added {item} for you."
  ],
  cartUpdate: [
    "Your cart now has {count} item{plural} with a total of ₦{total}.",
    "You've got {count} item{plural} in your cart totaling ₦{total}.",
    "Cart updated: {count} item{plural} - ₦{total} total."
  ],
  checkoutPrompt: [
    "Would you like to checkout now or add more items?",
    "Ready to checkout, or would you like to add more?",
    "Should we proceed to checkout, or keep shopping?",
    "Checkout now or continue adding items?"
  ],
  continueShopping: [
    "Sure thing! Let me suggest some items that would go great with what you have.",
    "Of course! Here are some perfect additions to complete your meal.",
    "Great! Let me help you find the perfect additions."
  ]
};

// --- helper to get local widget cart (used for checkout summary) ---
function getLocalWidgetCart() {
  return JSON.parse(localStorage.getItem("aiChat-localCart") || "[]");
}
function setLocalWidgetCart(arr) {
  localStorage.setItem("aiChat-localCart", JSON.stringify(arr));
}

// --- Get cart count and total ---
function getCartInfo() {
  const localCart = getLocalWidgetCart();
  const count = localCart.length;
  let total = 0;
  localCart.forEach((it) => {
    const key = (it.name || "").toLowerCase();
    total += menuPrices[key] || 0;
  });
  return { count, total };
}

// --- Show cart summary after item addition ---
function showCartUpdate() {
  const { count, total } = getCartInfo();
  const responses = conversationalResponses.cartUpdate;
  const response = responses[Math.floor(Math.random() * responses.length)]
    .replace("{count}", count)
    .replace("{plural}", count === 1 ? "" : "s")
    .replace("{total}", total.toLocaleString());
  appendBubble("bot", `🛒 ${response}`);
}

// --- Get items currently in cart ---
function getCartItems() {
  const localCart = getLocalWidgetCart();
  return localCart.map(item => item.name.toLowerCase());
}

// --- Intelligent food pairing suggestions based on cart ---
function getIntelligentSuggestions() {
  const cartItems = getCartItems();
  const suggestions = [];
  const suggested = new Set();
  
  // Reset previously suggested if cart is empty
  if (cartItems.length === 0) {
    previouslySuggestedItems.clear();
  }
  
  // Check for soups -> suggest swallow or side
  const hasSoup = cartItems.some(item => 
    foodCategories.soups.some(soup => item.includes(soup))
  );
  if (hasSoup && !cartItems.some(item => item.includes("abacha"))) {
    suggestions.push("Abacha");
    suggested.add("abacha");
  }
  
  // Check for rice -> suggest protein or drink
  const hasRice = cartItems.some(item => 
    foodCategories.rice.some(rice => item.includes(rice))
  );
  if (hasRice) {
    if (!cartItems.some(item => foodCategories.proteins.some(p => item.includes(p)))) {
      suggestions.push("Nkwobi");
      suggested.add("nkwobi");
    }
    if (!cartItems.some(item => foodCategories.drinks.some(d => item.includes(d)))) {
      suggestions.push("Palm Wine");
      suggested.add("palm wine");
    }
  }
  
  // Check for meat/protein -> suggest drink
  const hasProtein = cartItems.some(item => 
    foodCategories.proteins.some(p => item.includes(p))
  );
  if (hasProtein && !cartItems.some(item => foodCategories.drinks.some(d => item.includes(d)))) {
    suggestions.push("Palm Wine");
    suggested.add("palm wine");
  }
  
  // Check for multiple heavy meals -> suggest drink
  const heavyMeals = cartItems.filter(item => 
    foodCategories.soups.some(s => item.includes(s)) || 
    foodCategories.rice.some(r => item.includes(r))
  );
  if (heavyMeals.length >= 2 && !cartItems.some(item => foodCategories.drinks.some(d => item.includes(d)))) {
    if (!suggested.has("palm wine")) {
      suggestions.push("Palm Wine");
      suggested.add("palm wine");
    }
  }
  
  // If no drink exists -> suggest drink
  const hasDrink = cartItems.some(item => 
    foodCategories.drinks.some(d => item.includes(d))
  );
  if (!hasDrink && !suggested.has("palm wine") && !suggested.has("drinks")) {
    suggestions.push("Palm Wine");
    suggested.add("palm wine");
  }
  
  // Filter out items already in cart
  const filtered = suggestions.filter(item => {
    const itemLower = item.toLowerCase();
    return !cartItems.some(cartItem => cartItem.includes(itemLower));
  });
  
  // Filter out previously suggested items
  const finalSuggestions = filtered.filter(item => {
    const itemLower = item.toLowerCase();
    return !previouslySuggestedItems.has(itemLower);
  });
  
  // If we have suggestions, mark them as suggested
  finalSuggestions.forEach(item => {
    previouslySuggestedItems.add(item.toLowerCase());
  });
  
  // If no intelligent suggestions, return some general ones (excluding cart items)
  if (finalSuggestions.length === 0) {
    const allItems = Object.keys(chatFoodMap);
    const available = allItems.filter(item => {
      const itemLower = item.toLowerCase();
      return !cartItems.some(cartItem => cartItem.includes(itemLower)) &&
             !previouslySuggestedItems.has(itemLower);
    });
    return available.slice(0, 3); // Return up to 3 general suggestions
  }
  
  return finalSuggestions.slice(0, 3); // Return up to 3 intelligent suggestions
}

// --- Get conversational response ---
function getConversationalResponse(key, replacements = {}) {
  const responses = conversationalResponses[key] || [];
  if (responses.length === 0) return "";
  let response = responses[Math.floor(Math.random() * responses.length)];
  Object.keys(replacements).forEach(key => {
    response = response.replace(`{${key}}`, replacements[key]);
  });
  return response;
}

// --- safeAddToCart: tries site addToCart(), else local fallback. Always updates widget storage and lastOrder.
function safeAddToCart(itemId, itemName) {
  // call site's addToCart if available (non-destructive)
  if (typeof addToCart === "function" && itemId) {
    try {
      addToCart(itemId);
    } catch (err) {
      console.warn("site addToCart threw:", err);
    }
  }

  // persist lastOrder (human-readable history)
  lastOrder = JSON.parse(localStorage.getItem("aiChat-lastOrder") || "[]");
  lastOrder.push(itemName);
  localStorage.setItem("aiChat-lastOrder", JSON.stringify(lastOrder));

  // persist widget local cart for checkout totals
  const localCart = getLocalWidgetCart();
  localCart.push({ id: itemId || null, name: itemName });
  setLocalWidgetCart(localCart);
  
  // Show cart update after addition
  setTimeout(() => {
    showCartUpdate();
  }, 500);
}

// --- small helper to append bubbles consistently (keeps UI unchanged) ---
function appendBubble(type, msg, isTyping = false) {
  if (!messagesDiv) return;
  const p = document.createElement("p");
  p.className = isTyping
    ? "bot-bubble typing-indicator"
    : type === "bot"
    ? "bot-bubble"
    : "user-bubble";
  p.textContent = msg;
  messagesDiv.appendChild(p);
  messagesDiv.scrollTop = messagesDiv.scrollHeight;

  if (type === "bot" && !isTyping) {
    try {
      messageSound.play();
    } catch (e) {
      /* ignore autoplay errors */
    }
    // speak selectively using trySpeak elsewhere
  }
}

// --- show intelligent food suggestions with prices (cleans previous suggestions first) ---
function showFoodSuggestions(useIntelligentSuggestions = true) {
  if (!messagesDiv) return;

  // remove existing suggestion blocks so we don't duplicate
  const prev = messagesDiv.querySelectorAll(".chat-suggestions");
  prev.forEach((el) => el.remove());

  const suggestionDiv = document.createElement("div");
  suggestionDiv.className = "chat-suggestions";
  suggestionDiv.style.display = "flex";
  suggestionDiv.style.flexWrap = "wrap";
  suggestionDiv.style.gap = "8px";
  suggestionDiv.style.margin = "8px 0";

  // Get items to suggest (intelligent or all)
  let itemsToShow = [];
  if (useIntelligentSuggestions) {
    const intelligent = getIntelligentSuggestions();
    if (intelligent.length > 0) {
      itemsToShow = intelligent;
    } else {
      // Fallback to all items if no intelligent suggestions
      itemsToShow = Object.keys(chatFoodMap);
    }
  } else {
    itemsToShow = Object.keys(chatFoodMap);
  }

  // Show suggestions with prices
  itemsToShow.forEach((food) => {
    const btn = document.createElement("button");
    btn.className = "chat-suggestion-btn";
    btn.setAttribute("data-name", food);
    btn.setAttribute("data-id", chatFoodMap[food]);
    btn.type = "button";
    
    // Get price for this item
    const priceKey = food.toLowerCase();
    const price = menuPrices[priceKey] || 0;
    
    // Create button content with price
    const btnContent = document.createElement("div");
    btnContent.style.display = "flex";
    btnContent.style.flexDirection = "column";
    btnContent.style.alignItems = "flex-start";
    btnContent.style.gap = "2px";
    
    const foodName = document.createElement("span");
    foodName.textContent = food;
    foodName.style.fontWeight = "500";
    
    const priceSpan = document.createElement("span");
    priceSpan.textContent = `₦${price.toLocaleString()}`;
    priceSpan.style.fontSize = "0.85em";
    priceSpan.style.color = "#666";
    priceSpan.style.fontWeight = "400";
    
    btnContent.appendChild(foodName);
    btnContent.appendChild(priceSpan);
    btn.appendChild(btnContent);
    
    // styling is minimal — won't override site design (you can style .chat-suggestion-btn in your CSS)
    btn.style.padding = "8px 12px";
    btn.style.borderRadius = "6px";
    btn.style.border = "1px solid rgba(0,0,0,0.08)";
    btn.style.background = "white";
    btn.style.cursor = "pointer";
    btn.style.minWidth = "100px";
    btn.style.textAlign = "left";
    
    suggestionDiv.appendChild(btn);
  });

  // Add Clear Orders button but DON'T give it the same class used by food buttons
  const clearBtn = document.createElement("button");
  clearBtn.className = "chat-clear-btn";
  clearBtn.type = "button";
  clearBtn.style.padding = "6px 10px";
  clearBtn.style.borderRadius = "6px";
  clearBtn.style.border = "1px solid rgba(0,0,0,0.08)";
  clearBtn.style.background = "#ffecec";
  clearBtn.style.cursor = "pointer";
  clearBtn.textContent = "🗑️ Clear Orders";
  clearBtn.onclick = () => {
    if (!confirm("Are you sure you want to clear your order history?")) return;
    localStorage.removeItem("aiChat-lastOrder");
    lastOrder = [];
    // also clear local widget cart
    setLocalWidgetCart([]);
    previouslySuggestedItems.clear();
    cartClickCount = 0;
    appendBubble("bot", "✅ Your order history has been cleared.");
    trySpeak("Your order history has been cleared.");
  };
  suggestionDiv.appendChild(clearBtn);

  messagesDiv.appendChild(suggestionDiv);
  messagesDiv.scrollTop = messagesDiv.scrollHeight;
}

// --- helper: show checkout summary (count + total) using widget local cart ---
function showCartSummary() {
  const { count, total } = getCartInfo();
  appendBubble(
    "bot",
    `🛒 You have ${count} item${
      count === 1 ? "" : "s"
    } in your cart.\n💰 Total: ₦${total.toLocaleString()}`
  );
}

// --- Sync chatbot cart to main site cart (for order-online.php) ---
function syncCartToMainSite() {
  const localCart = getLocalWidgetCart();
  if (localCart.length === 0) return;
  
  // Get main site cart from localStorage (order-online.php uses "cart" key)
  let mainCart = [];
  try {
    const storedCart = localStorage.getItem("cart");
    if (storedCart) {
      mainCart = JSON.parse(storedCart);
    }
  } catch (e) {
    console.warn("Could not read main cart:", e);
  }
  
  // Merge chatbot items into main cart
  localCart.forEach(chatItem => {
    const existingIndex = mainCart.findIndex(item => item.id === chatItem.id);
    if (existingIndex >= 0) {
      // Item exists, increment quantity
      mainCart[existingIndex].quantity = (mainCart[existingIndex].quantity || 1) + 1;
    } else {
      // New item, add to cart
      // Try to get full item details from menuItems if available
      const menuItem = typeof menuItems !== 'undefined' && Array.isArray(menuItems) 
        ? menuItems.find(item => item.id === chatItem.id)
        : null;
      
      if (menuItem) {
        mainCart.push({
          ...menuItem,
          quantity: 1
        });
      } else {
        // Fallback: create basic item structure
        const priceKey = (chatItem.name || "").toLowerCase();
        mainCart.push({
          id: chatItem.id,
          title: chatItem.name,
          price: menuPrices[priceKey] || 0,
          quantity: 1,
          image: "./images/default-food.jpg" // Default image
        });
      }
    }
  });
  
  // Save merged cart
  localStorage.setItem("cart", JSON.stringify(mainCart));
}

// --- Redirect to order-online.php with cart sync ---
function redirectToCheckout() {
  syncCartToMainSite();
  appendBubble("bot", "Perfect! Taking you to checkout now...");
  trySpeak("Redirecting you to checkout.");
  setTimeout(() => {
    window.location.href = "./order-online.php";
  }, 1000);
}

// --- Show checkout prompt with natural conversation ---
function showCheckoutPrompt() {
  const { count, total } = getCartInfo();
  const responses = conversationalResponses.checkoutPrompt;
  const response = responses[Math.floor(Math.random() * responses.length)];
  
  appendBubble("bot", `🛒 You've added ${count} item${count === 1 ? "" : "s"} to your cart (₦${total.toLocaleString()} total). ${response}`);
  trySpeak(`You have ${count} items in your cart. ${response}`);
  
  // Remove old suggestion blocks
  const prevBlocks = messagesDiv.querySelectorAll(".chat-suggestions");
  prevBlocks.forEach((el) => el.remove());
  
  const choiceDiv = document.createElement("div");
  choiceDiv.className = "chat-suggestions";
  choiceDiv.style.display = "flex";
  choiceDiv.style.flexWrap = "wrap";
  choiceDiv.style.gap = "8px";
  choiceDiv.style.margin = "8px 0";
  
  const checkoutBtn = document.createElement("button");
  checkoutBtn.type = "button";
  checkoutBtn.className = "chat-suggestion-btn";
  checkoutBtn.textContent = "Checkout";
  checkoutBtn.style.padding = "8px 16px";
  checkoutBtn.style.borderRadius = "6px";
  checkoutBtn.style.border = "1px solid rgba(0,0,0,0.08)";
  checkoutBtn.style.background = "#4CAF50";
  checkoutBtn.style.color = "white";
  checkoutBtn.style.cursor = "pointer";
  checkoutBtn.style.fontWeight = "500";
  checkoutBtn.onclick = () => {
    redirectToCheckout();
  };
  
  const continueBtn = document.createElement("button");
  continueBtn.type = "button";
  continueBtn.className = "chat-suggestion-btn";
  continueBtn.textContent = "Add More";
  continueBtn.style.padding = "8px 16px";
  continueBtn.style.borderRadius = "6px";
  continueBtn.style.border = "1px solid rgba(0,0,0,0.08)";
  continueBtn.style.background = "white";
  continueBtn.style.cursor = "pointer";
  continueBtn.onclick = () => {
    const responses = conversationalResponses.continueShopping;
    const response = responses[Math.floor(Math.random() * responses.length)];
    appendBubble("bot", response);
    cartClickCount = 0; // Reset so we prompt again after next additions
    setTimeout(() => {
      showFoodSuggestions(true); // Use intelligent suggestions
    }, 500);
  };
  
  choiceDiv.appendChild(checkoutBtn);
  choiceDiv.appendChild(continueBtn);
  messagesDiv.appendChild(choiceDiv);
  messagesDiv.scrollTop = messagesDiv.scrollHeight;
}

// --- event delegation for suggestion button clicks (keeps code small and robust) ---
if (messagesDiv) {
  messagesDiv.addEventListener("click", (e) => {
    // only target suggestion buttons that have data-name (food items)
    const btn = e.target.closest && e.target.closest(".chat-suggestion-btn");
    if (!btn) return;
    if (!btn.hasAttribute("data-name")) return; // ignore other suggestion-like buttons

    const foodName = btn.getAttribute("data-name");
    const itemIdRaw = btn.getAttribute("data-id");
    const itemId = itemIdRaw ? parseInt(itemIdRaw, 10) : null;

    // use safeAddToCart to avoid "addToCart not found" issues
    safeAddToCart(itemId, foodName);

    // Confirm to user with conversational response
    const confirmMsg = getConversationalResponse("itemAdded", { item: foodName });
    appendBubble("bot", `✅ ${confirmMsg || `${foodName} added to your cart.`}`);
    // speak confirmation (queued if necessary). trySpeak suppresses cart-add messages via regex already.
    trySpeak(`${foodName} added to your cart.`);

    // Update click count and possibly prompt checkout after 2 additions (more natural)
    cartClickCount += 1;

    if (cartClickCount >= 2) {
      // Show checkout prompt after 2+ items
      setTimeout(() => {
        showCheckoutPrompt();
      }, 1000);
    } else {
      // After short delay show intelligent suggestions
      setTimeout(() => {
        const { count } = getCartInfo();
        appendBubble(
          "bot",
          `Great! Would you like to add something else? I can suggest items that pair well with what you have.`
        );
        showFoodSuggestions(true); // Use intelligent suggestions
      }, 1000);
    }
  });
}

// --- Ask for order (bot prompt that triggers suggestions) ---
function askForOrder() {
  const { count } = getCartInfo();
  if (count === 0) {
    appendBubble(
      "bot",
      "What would you like to order today? Here are some of our popular dishes:"
    );
    showFoodSuggestions(false); // Show all items for first order
  } else {
    appendBubble(
      "bot",
      "What else would you like to add? Here are some great options:"
    );
    showFoodSuggestions(true); // Use intelligent suggestions
  }
}

// --- Multi free fallback: tries Dictionary -> DuckDuckGo -> Wikipedia -> generic ---
async function getFallbackAnswer(query) {
  // First check if it's small talk
  const smallTalkResponse = matchSmallTalk(query);
  if (smallTalkResponse) {
    return smallTalkResponse;
  }

  // small helper to return gracefully
  const generic =
    "🤔 I'm not sure about that. Can you rephrase or ask something else?";

  // 1) dictionaryapi.dev for single-word definitions (best-effort)
  try {
    // only attempt dictionary for single tokens or short words
    if (typeof query === "string" && query.trim().split(/\s+/).length <= 2) {
      const dRes = await fetch(
        `https://api.dictionaryapi.dev/api/v2/entries/en/${encodeURIComponent(
          query
        )}`
      );
      if (dRes.ok) {
        const dData = await dRes.json();
        if (Array.isArray(dData) && dData[0]?.meanings?.length) {
          const meaning = dData[0].meanings[0].definitions[0].definition;
          return `📖 Definition of "${query}": ${meaning}`;
        }
      }
    }
  } catch (e) {
    console.warn("dictionary fallback failed", e);
  }

  // 2) DuckDuckGo Instant Answer (abstract)
  try {
    const ddgRes = await fetch(
      `https://api.duckduckgo.com/?q=${encodeURIComponent(
        query
      )}&format=json&no_redirect=1&no_html=1`
    );
    if (ddgRes.ok) {
      const ddgData = await ddgRes.json();
      if (ddgData?.AbstractText) {
        return `🔎 ${ddgData.AbstractText}`;
      }
      // if RelatedTopics contains a snippet, use it
      if (
        Array.isArray(ddgData?.RelatedTopics) &&
        ddgData.RelatedTopics.length
      ) {
        const topic = ddgData.RelatedTopics[0];
        if (topic?.Text) return `🔎 ${topic.Text}`;
      }
    }
  } catch (e) {
    console.warn("duckduckgo fallback failed", e);
  }

  // 3) Wikipedia summary
  try {
    const wpRes = await fetch(
      `https://en.wikipedia.org/api/rest_v1/page/summary/${encodeURIComponent(
        query
      )}`
    );
    if (wpRes.ok) {
      const wpData = await wpRes.json();
      if (wpData?.extract) return wpData.extract;
    }
  } catch (e) {
    console.warn("wikipedia fallback failed", e);
  }

  // final fallback
  return generic;
}

// --- Welcome "pulp out" on load and open/close handlers (fixed) ---
window.addEventListener("load", () => {
  // Delay so page finishes rendering — consistent with previous behavior
  setTimeout(() => {
    if (chatContainer) {
      chatContainer.style.display = "flex"; // popup chat
      try {
        openSound.play();
      } catch (e) {
        /* ignore */
      }
    }
    // show welcome choices if welcomeBox exists
    if (welcomeBox) welcomeBox.style.display = "flex";
    if (inputArea) inputArea.style.display = "none";

    // show a friendly welcome message (time-based) and queue/speak it safely
    const greetMsg = `${getTimeGreeting()}! Welcome to Joseph's Pot! How would you like to chat with us?`;
    appendBubble("bot", greetMsg);
    trySpeak(greetMsg);
    // (if they have name/address saved, we don't auto-fill here; that happens on AI Chat click)
  }, 1200);
});

// Open button click should show the chat window and welcome box
if (openBtn) {
  openBtn.addEventListener("click", () => {
    if (chatContainer) chatContainer.style.display = "flex";
    if (welcomeBox) welcomeBox.style.display = "flex";
    if (inputArea) inputArea.style.display = "none";
    try {
      openSound.play();
    } catch (e) {}
    // If speech was queued, opening the chat counts as a user gesture — unlock and flush
    unlockSpeech();
  });
}

// Close button
if (closeBtn) {
  closeBtn.addEventListener("click", () => {
    if (chatContainer) chatContainer.style.display = "none";
  });
}

// --- AI Chat button behavior (when user clicks 'AI Chat' from welcome box) ---
if (liveBtn) {
  liveBtn.addEventListener("click", () => {
    if (welcomeBox) welcomeBox.style.display = "none";
    if (inputArea) inputArea.style.display = "flex";

    savedName = localStorage.getItem("aiChat-name") || null;
    savedAddress = localStorage.getItem("aiChat-address") || null;

    if (savedName && savedAddress) {
      appendBubble(
        "bot",
        `Welcome back ${savedName}! I have your delivery address as "${savedAddress}". What would you like to order today?`
      );
      // show last order suggestion (if any)
      const stored = JSON.parse(
        localStorage.getItem("aiChat-lastOrder") || "[]"
      );
      if (stored && stored.length > 0) {
        appendBubble(
          "bot",
          `Last time you ordered: ${stored.join(
            ", "
          )}. Would you like the same or modify it?`
        );
      }
      askForOrder();
      if (!suggestionShown) {
        appendBubble(
          "bot",
          'Tip: If you want to update your details, just type "change my details".'
        );
        suggestionShown = true;
      }
    } else if (savedName && !savedAddress) {
      appendBubble(
        "bot",
        `Welcome back ${savedName}! Please provide your delivery address:`
      );
      awaitingAddress = true;
    } else {
      appendBubble("bot", "Great! Let's chat. May I have your name please?");
      awaitingName = true;
    }

    // user clicked a chat control -> unlock speech if pending
    unlockSpeech();
  });
}

// --- WhatsApp button quick link ---
if (waBtn) {
  waBtn.addEventListener("click", () => {
    window.open("https://wa.me/2348104344994", "_blank");
    unlockSpeech();
  });
}

// --- Input/Send handling (keeps existing flows safe) ---
if (userInput) {
  userInput.addEventListener("keypress", (e) => {
    if (e.key === "Enter" && sendBtn) sendBtn.click();
  });
}

if (sendBtn) {
  sendBtn.addEventListener("click", async () => {
    const text = userInput && userInput.value ? userInput.value.trim() : "";
    if (!text) return;

    appendBubble("user", text);
    if (userInput) userInput.value = "";

    // this user action can unlock speech queue
    unlockSpeech();

    // name/address flows
    if (awaitingName) {
      localStorage.setItem("aiChat-name", text);
      awaitingName = false;
      awaitingAddress = true;
      appendBubble(
        "bot",
        `Thanks ${text}! Please enter your delivery address:`
      );
      return;
    }
    if (awaitingAddress) {
      localStorage.setItem("aiChat-address", text);
      awaitingAddress = false;
      appendBubble("bot", "Great! What would you like to order?");
      askForOrder();
      if (!suggestionShown) {
        appendBubble(
          "bot",
          'Tip: If you want to update your details, just type "change my details".'
        );
        suggestionShown = true;
      }
      return;
    }

    // Change details command
    if (text.toLowerCase().includes("change my details")) {
      localStorage.removeItem("aiChat-name");
      localStorage.removeItem("aiChat-address");
      savedName = null;
      savedAddress = null;
      awaitingName = true;
      appendBubble(
        "bot",
        "Alright — let's update your details. What's your name?"
      );
      return;
    }

    // Clear order via typed command (keeps previous behavior)
    if (
      text.toLowerCase().includes("clear order") ||
      text.toLowerCase().includes("reset") ||
      text.toLowerCase().includes("clear cart")
    ) {
      if (!confirm("Are you sure you want to clear your order history?"))
        return;
      localStorage.removeItem("aiChat-lastOrder");
      lastOrder = [];
      setLocalWidgetCart([]);
      previouslySuggestedItems.clear();
      cartClickCount = 0;
      appendBubble("bot", "✅ Your order history has been cleared.");
      trySpeak("Your order history has been cleared.");
      return;
    }

    // Handle checkout commands
    const checkoutKeywords = ["checkout", "i'm done", "done", "finish", "complete order", "proceed to checkout"];
    if (checkoutKeywords.some(keyword => text.toLowerCase().includes(keyword))) {
      const { count } = getCartInfo();
      if (count === 0) {
        appendBubble("bot", "Your cart is empty. Would you like to add some items first?");
        showFoodSuggestions(true);
      } else {
        redirectToCheckout();
      }
      return;
    }
    
    // Handle continue/add more commands
    const continueKeywords = ["yes", "continue", "add more", "more", "keep shopping", "i want more"];
    if (continueKeywords.some(keyword => text.toLowerCase().includes(keyword))) {
      const responses = conversationalResponses.continueShopping;
      const response = responses[Math.floor(Math.random() * responses.length)];
      appendBubble("bot", response);
      cartClickCount = 0;
      setTimeout(() => {
        showFoodSuggestions(true); // Use intelligent suggestions
      }, 500);
      return;
    }
    
    // Handle "no" - ask if they want to checkout
    if (text.toLowerCase().trim() === "no" || text.toLowerCase().includes("no thanks")) {
      const { count } = getCartInfo();
      if (count > 0) {
        appendBubble("bot", "No problem! Would you like to proceed to checkout then?");
        setTimeout(() => {
          showCheckoutPrompt();
        }, 500);
      } else {
        appendBubble("bot", "Alright! Let me know if you change your mind.");
      }
      return;
    }

    // If user typed a food item explicitly, try to map and add
    const typedFood = Object.keys(chatFoodMap).find((f) =>
      text.toLowerCase().includes(f.toLowerCase())
    );
    if (typedFood) {
      const itemId = chatFoodMap[typedFood];
      // use safeAddToCart so we don't display "(addToCart not found)"
      safeAddToCart(itemId, typedFood);
      
      // Confirm with conversational response
      const confirmMsg = getConversationalResponse("itemAdded", { item: typedFood });
      appendBubble("bot", `✅ ${confirmMsg || `${typedFood} added to your cart.`}`);
      trySpeak(`${typedFood} added to your cart.`);

      // save last order already handled in safeAddToCart
      cartClickCount++;
      if (cartClickCount >= 2) {
        setTimeout(() => {
          showCheckoutPrompt();
        }, 1000);
      } else {
        setTimeout(() => {
          appendBubble("bot", "Great! Would you like to add something else?");
          showFoodSuggestions(true); // Use intelligent suggestions
        }, 1000);
      }
      return;
    }

    // Default behavior: call the fallback AI helpers if not a menu command

    if (/order|menu|food|want|buy/i.test(text)) {
      askForOrder();
      return;
    }

    // If not a known food/command, use multi-service fallback
    const fallback = await getFallbackAnswer(text);
    appendBubble("bot", fallback);
    trySpeak(fallback);
  });
}

// --- Optional: Microphone toggle (robust) ---
if (micBtn) {
  const SpeechRecognition =
    window.SpeechRecognition || window.webkitSpeechRecognition;
  if (SpeechRecognition) {
    const recognition = new SpeechRecognition();
    recognition.continuous = false;
    recognition.interimResults = false;
    recognition.lang = "en-US";
    let listening = false;

    micBtn.addEventListener("click", () => {
      if (!listening) {
        try {
          recognition.start();
          listening = true;
          micBtn.classList && micBtn.classList.add("listening");
          appendBubble("bot", "🎤 Listening... speak now");
        } catch (e) {
          appendBubble("bot", "⚠️ Cannot start microphone. Check permissions.");
        }
      } else {
        recognition.stop();
        listening = false;
        micBtn.classList && micBtn.classList.remove("listening");
      }
      // clicking mic is a user gesture — unlock TTS
      unlockSpeech();
    });

    recognition.onresult = (ev) => {
      const transcript =
        ev.results &&
        ev.results[0] &&
        ev.results[0][0] &&
        ev.results[0][0].transcript;
      if (transcript) {
        if (userInput) userInput.value = transcript;
        if (sendBtn) sendBtn.click();
      }
      listening = false;
      micBtn.classList && micBtn.classList.remove("listening");
    };

    recognition.onerror = (ev) => {
      console.error("Speech error:", ev);
      appendBubble("bot", "⚠️ Microphone error. Please try again.");
      listening = false;
      micBtn.classList && micBtn.classList.remove("listening");
    };

    recognition.onend = () => {
      listening = false;
      micBtn.classList && micBtn.classList.remove("listening");
    };
  } else {
    // hide mic if not supported
    if (micBtn) micBtn.style.display = "none";
  }
}
/* ============================================
   AI CHATBOT SECTION ENDS HERE
   ============================================ */

// ============================================
// PAGE LOAD ANIMATION SECTION STARTS HERE
// ============================================
window.addEventListener("load", () => {
  const preloader = document.querySelector(".preloader");
  const content = document.querySelector(".content");
  const progressBar = document.getElementById("progress-bar");
  const percentage = document.getElementById("percentage");

  let progress = 0;
  const interval = setInterval(() => {
    if (progress < 100) {
      progress++;
      progressBar.style.width = progress + "%";
      percentage.textContent = progress + "%";
    } else {
      clearInterval(interval);
      preloader.style.transition = "opacity 1s ease";
      preloader.style.opacity = "0";

      setTimeout(() => {
        preloader.style.display = "none";
        content.style.opacity = "1";
      }, 2000);
    }
  }, 25);
});
// ============================================
// PAGE LOAD ANIMATION SECTION ENDS HERE
// ============================================
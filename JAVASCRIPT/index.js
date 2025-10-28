// PRE-LOADER
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

// BACKGROUND SLIDER – VERTICAL / DIAGONAL ENTRY
document.addEventListener("DOMContentLoaded", function () {
  const images = [
    "./images/image20.jpg",
    "./images/spaghetti.jpg",
    "./images/unnamed.webp",
    "./images/image50.jpg",
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

// NAVBAR COLOR ON SCROLL
window.addEventListener("scroll", function () {
  const navbar = document.getElementById("navbar");
  if (window.scrollY > 50) {
    navbar.classList.add("scrolled");
  } else {
    navbar.classList.remove("scrolled");
  }
});

// Mobile menu toggle function
function toggleMenu() {
  const navLinks = document.querySelector(".nav-links");
  navLinks.classList.toggle("active");
}

// Scroll To Top Button
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

// ABOUT US SECTION OF HOW WE STARTED
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

// OUR MENU FLIP CARD
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

// UPCOMING EVENTS SECTION - MODERN VERSION
const events = [
  {
    title: "Birthday Celebration for Princeton (AKA, Pricelesshacker1)",
    description:
      "Join us for an exclusive evening of fine dine-in Birthday celebration of our amiable friend a brother  paired with gourmet dishes and arabian.",
    date: "2025-10-10T19:00:00",
    eventImage: "./images/princeton.jpg",
  },
  {
    title: "Cultural Night Experience",
    description:
      "Celebrate Igbo heritage through an immersive culinary journey featuring traditional cuisine, live music, and cultural performances.",
    date: "2025-10-30T19:00:00",
    eventImage: "./images/image51.jpg",
  },
  {
    title: "Palm Wine & Poetry",
    description:
      "An intimate evening of locally sourced palm wine tasting accompanied by live poetry readings under the stars in our garden terrace.",
    date: "2025-11-30T19:30:00",
    eventImage: "./images/image50.jpg",
  },
];

let currentEventIndex = 0;
let countdownInterval;
let autoRotateInterval;

// Format date as "15 Aug"
function formatShortDate(dateStr) {
  const options = { day: "numeric", month: "short" };
  return new Date(dateStr).toLocaleDateString("en-US", options);
}

function updateEventDisplay(index) {
  const event = events[index];
  const eventDate = new Date(event.date);

  document.getElementById("eventTitle").textContent = event.title;
  document.getElementById("eventDesc").textContent = event.description;
  document.getElementById("eventImage").src = event.eventImage;
  document.getElementById("eventDay").textContent = [
    "Sunday",
    "Monday",
    "Tuesday",
    "Wednesday",
    "Thursday",
    "Friday",
    "Saturday",
  ][eventDate.getDay()];
  document.getElementById("eventDate").textContent = formatShortDate(
    event.date
  );
  // Update countdown immediately and then every second
  clearInterval(countdownInterval);
  updateCountdown(event.date);
  countdownInterval = setInterval(() => updateCountdown(event.date), 1000);
}

function updateCountdown(eventDateStr) {
  const now = new Date();
  const targetDate = new Date(eventDateStr);
  const diff = targetDate - now;

  if (diff <= 0) {
    document.getElementById("countdown").innerHTML = `
      <div class="event-started">Event in progress</div>
    `;
    clearInterval(countdownInterval);
    return;
  }

  const days = Math.floor(diff / (1000 * 60 * 60 * 24));
  const hours = Math.floor((diff / (1000 * 60 * 60)) % 24);
  const minutes = Math.floor((diff / (1000 * 60)) % 60);
  const seconds = Math.floor((diff / 1000) % 60);

  // Ensure countdown elements exist before updating
  const daysElement = document.querySelector(".days");
  const hoursElement = document.querySelector(".hours");
  const minutesElement = document.querySelector(".minutes");
  const secondsElement = document.querySelector(".seconds");

  if (daysElement) daysElement.textContent = days.toString().padStart(2, "0");
  if (hoursElement)
    hoursElement.textContent = hours.toString().padStart(2, "0");
  if (minutesElement)
    minutesElement.textContent = minutes.toString().padStart(2, "0");
  if (secondsElement)
    secondsElement.textContent = seconds.toString().padStart(2, "0");
}
// Navigation
document.getElementById("nextEventBtn")?.addEventListener("click", () => {
  currentEventIndex = (currentEventIndex + 1) % events.length;
  updateEventDisplay(currentEventIndex);
});

// Touch events for mobile
let touchStartX = 0;
const eventContent = document.querySelector(".event-card");

eventContent?.addEventListener("touchstart", (e) => {
  touchStartX = e.changedTouches[0].screenX;
});

eventContent?.addEventListener("touchend", (e) => {
  const touchEndX = e.changedTouches[0].screenX;
  const threshold = 50;

  if (touchEndX < touchStartX - threshold) {
    currentEventIndex = (currentEventIndex + 1) % events.length;
  } else if (touchEndX > touchStartX + threshold) {
    currentEventIndex = (currentEventIndex - 1 + events.length) % events.length;
  } else {
    return;
  }

  updateEventDisplay(currentEventIndex);
});

// Initialize auto-rotation
function startAutoRotation() {
  autoRotateInterval = setInterval(() => {
    currentEventIndex = (currentEventIndex + 1) % events.length;
    updateEventDisplay(currentEventIndex);
  }, 10000);
}
// Pause auto-rotation on hover/focus
const eventContainer = document.getElementById("eventContainer");
eventContainer?.addEventListener("mouseenter", () =>
  clearInterval(autoRotateInterval)
);
eventContainer?.addEventListener("mouseleave", startAutoRotation);

// Initialize
updateEventDisplay(currentEventIndex);
startAutoRotation();

// EMAIL ALERT SECTION BOOK A TABLE

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

  // Form submission handler
  form.addEventListener("submit", async function (e) {
    e.preventDefault();

    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.querySelector(".btn-text").textContent;
    submitBtn.querySelector(".btn-text").textContent = "Processing...";
    submitBtn.disabled = true;

    try {
      const formData = new FormData(form);
      // Simulate API call delay for demo
      await new Promise((resolve) => setTimeout(resolve, 1500));
      const response = await fetch(form.action, {
        method: "POST",
        body: formData,
        headers: {
          Accept: "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
      });

      if (response.ok) {
        // Show success message
        form.reset();
        successNotification.style.display = "flex";
        errorNotification.style.display = "none";
        // Reset floating labels
        document
          .querySelectorAll(
            ".floating input, .floating textarea, .floating select"
          )
          .forEach((el) => {
            el.dispatchEvent(new Event("change"));
          });
        // Hide success message after 5 seconds
        setTimeout(() => {
          successNotification.style.display = "none";
        }, 5000);
      } else {
        throw new Error("Form submission failed");
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

// REVIEW SECTION
// ================ MAIN SLIDER CODE ================
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

  // Admin elements
  const adminLoginBtn = document.getElementById("adminLoginBtn");
  const adminToggle = document.getElementById("adminToggle");
  const adminPanel = document.getElementById("adminPanel");
  const adminTabs = document.querySelectorAll(".admin-tab");
  const adminTabContents = document.querySelectorAll(".admin-tab-content");
  let adminMode = false;

  // Initialize with sample reviews if none exist
  function initializeSampleReviews() {
    const sampleReviews = [
      {
        id: "1",
        name: "Apama Tv",
        text: "I blacked out after the first spoonful. I swear I don't remember finishing the plate. One bite of that egusi soup and I woke up with tears in my eyes and an empty bowl. This food doesn't play!",
        rating: 5,
        imageUrl: "https://randomuser.me/api/portraits/men/32.jpg",
        approved: true,
        date: new Date().toISOString(),
      },
      {
        id: "2",
        name: "Ken Udosi",
        text: 'My ancestors whispered "thank you" after the meal. That okra soup must\'ve been made with palm oil straight from heaven. I felt my lineage rejoicing.',
        rating: 4,
        imageUrl: "https://randomuser.me/api/portraits/men/44.jpg",
        approved: true,
        date: new Date().toISOString(),
      },
      {
        id: "3",
        name: "Priya S",
        text: "I ordered takeaway and ate it before I reached my car. I was meant to save it for later. That aroma seduced me in the parking lot. I never stood a chance.",
        rating: 5,
        imageUrl: "https://randomuser.me/api/portraits/women/63.jpg",
        approved: true,
        date: new Date().toISOString(),
      },
      {
        id: "4",
        name: "Alex T",
        text: "My husband proposed again after tasting the yam porridge. We've been married 8 years. He took one bite, looked me in the eye, and said, \"Let's start over.\" It was that deep.",
        rating: 5,
        imageUrl: "https://randomuser.me/api/portraits/men/22.jpg",
        approved: true,
        date: new Date().toISOString(),
      },
    ];

    if (!localStorage.getItem("userReviews")) {
      localStorage.setItem("userReviews", JSON.stringify(sampleReviews));
    }
  }

  // Load saved reviews from localStorage
  function loadSavedReviews() {
    initializeSampleReviews();
    const savedReviews = JSON.parse(localStorage.getItem("userReviews")) || [];

    // Clear existing slides
    reviewContainer.innerHTML = "";

    // Filter approved reviews and sort by date (newest first)
    const approvedReviews = savedReviews
      .filter((review) => review.approved)
      .sort((a, b) => new Date(b.date) - new Date(a.date));

    if (approvedReviews.length === 0) {
      reviewContainer.innerHTML =
        "<p>No reviews yet. Be the first to review!</p>";
      return;
    }

    approvedReviews.forEach((review, index) => {
      const newSlide = document.createElement("div");
      newSlide.className = `review-slide ${
        slideDirections[index % slideDirections.length]
      }`;
      newSlide.dataset.id = review.id;

      newSlide.innerHTML = `
                        <div class="slide-image-container">
                            <img class="review-img" src="${
                              review.imageUrl ||
                              "https://randomuser.me/api/portraits/neutral/default.jpg"
                            }" alt="${review.name}">
                        </div>
                        <div class="review-text">"${review.text}"</div>
                        <div class="review-stars">${"★".repeat(
                          review.rating
                        )}${"☆".repeat(5 - review.rating)}</div>
                        <div class="review-author">– ${review.name}</div>
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

  // Call this when page loads
  loadSavedReviews();

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

    // Handle image upload
    let imageUrl = "https://randomuser.me/api/portraits/neutral/default.jpg";
    if (imageInput.files.length > 0) {
      try {
        imageUrl = await uploadImage(imageInput.files[0]);
      } catch (error) {
        console.error("Image upload error:", error);
        showActionMessage("Error uploading image", "#f44336");
        return;
      }
    }

    // Create new review object
    const newReview = {
      id: Date.now().toString(),
      name,
      text,
      rating,
      imageUrl,
      approved: false, // New reviews need admin approval
      date: new Date().toISOString(),
    };

    // Save to localStorage
    saveReviewToStorage(newReview);

    // Reset form and hide it
    submitReviewForm.reset();
    reviewRatingInput.value = "5";
    updateStars(5);
    reviewFormOverlay.style.display = "none";

    // Show success message
    showActionMessage("Review submitted for approval", "#4CAF50");

    // If admin is viewing, refresh pending reviews
    if (adminMode) {
      loadPendingReviews();
    }

    startSlideShow();
  });

  // Save review to localStorage
  function saveReviewToStorage(review) {
    const savedReviews = JSON.parse(localStorage.getItem("userReviews")) || [];
    savedReviews.unshift(review); // Add new review at beginning
    localStorage.setItem("userReviews", JSON.stringify(savedReviews));
  }

  // Image upload simulation (in a real app, you'd upload to a server)
  function uploadImage(file) {
    return new Promise((resolve) => {
      const reader = new FileReader();
      reader.onload = (e) => {
        resolve(e.target.result);
      };
      reader.readAsDataURL(file);
    });
  }

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
  cycleHeadings();

  // Initialize dots if we have slides
  if (slides.length > 0) {
    updateDots();
    startSlideShow();
  }

  // Event listeners for navigation
  prevBtn.addEventListener("click", () => {
    prevSlide();
    resetSlideShow();
  });

  nextBtn.addEventListener("click", () => {
    nextSlide();
    resetSlideShow();
  });

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
    const message = document.createElement("div");
    message.className = "action-message";
    message.textContent = text;
    message.style.backgroundColor = color;
    document.body.appendChild(message);
    setTimeout(() => message.remove(), 3000);
  }

  // ================ ADMIN FUNCTIONALITY ================

  // Admin login button functionality
  adminLoginBtn.addEventListener("click", () => {
    const password = prompt("Enter admin password:");
    if (password === "admin1982") {
      adminMode = !adminMode;
      adminPanel.style.display = adminMode ? "block" : "none";
      adminToggle.style.display = adminMode ? "block" : "none";
      if (adminMode) {
        loadPendingReviews();
        const activeTab = document.querySelector(".admin-tab.active");
        if (activeTab.dataset.tab === "approved") {
          loadApprovedReviews();
        }
      }
    } else if (password !== null) {
      alert("Incorrect password");
    }
  });

  // Tab switching functionality
  adminTabs.forEach((tab) => {
    tab.addEventListener("click", () => {
      adminTabs.forEach((t) => t.classList.remove("active"));
      adminTabContents.forEach((c) => c.classList.remove("active"));

      // Add active class to clicked tab and corresponding content
      tab.classList.add("active");
      const tabContent = document.querySelector(
        `.admin-tab-content[data-tab-content="${tab.dataset.tab}"]`
      );
      tabContent.classList.add("active");

      // Load the appropriate content
      if (tab.dataset.tab === "approved") {
        loadApprovedReviews();
      } else {
        loadPendingReviews();
      }
    });
  });

  // Load pending reviews
  function loadPendingReviews() {
    const pendingContainer = document.getElementById("pendingReviews");
    pendingContainer.innerHTML = "";

    const pending = getPendingReviews();

    if (pending.length === 0) {
      pendingContainer.innerHTML = "<p>No pending reviews</p>";
      return;
    }

    pending.forEach((review) => {
      const reviewElement = createAdminReviewElement(review, true);
      pendingContainer.appendChild(reviewElement);
    });
  }

  // Load approved reviews
  function loadApprovedReviews() {
    const approvedContainer = document.getElementById("approvedReviews");
    approvedContainer.innerHTML = "";

    const approved = getApprovedReviews();

    if (approved.length === 0) {
      approvedContainer.innerHTML = "<p>No approved reviews</p>";
      return;
    }

    approved.forEach((review) => {
      const reviewElement = createAdminReviewElement(review, false);
      approvedContainer.appendChild(reviewElement);
    });
  }

  // Get pending reviews
  function getPendingReviews() {
    const allReviews = JSON.parse(localStorage.getItem("userReviews")) || [];
    return allReviews.filter((review) => !review.approved);
  }

  // Get approved reviews
  function getApprovedReviews() {
    const allReviews = JSON.parse(localStorage.getItem("userReviews")) || [];
    return allReviews.filter((review) => review.approved);
  }

  // Create admin review element (for both pending and approved)
  function createAdminReviewElement(review, isPending = true) {
    const element = document.createElement("div");
    element.className = isPending ? "pending-review" : "approved-review";
    element.dataset.id = review.id;

    element.innerHTML = `
                    <div class="review-edit-fields" style="display:none">
                        <textarea class="edit-text">${review.text}</textarea>
                        <div class="edit-rating">
                            ${Array(5)
                              .fill()
                              .map(
                                (_, i) =>
                                  `<span class="edit-star" data-value="${
                                    i + 1
                                  }">${i < review.rating ? "★" : "☆"}</span>`
                              )
                              .join("")}
                        </div>
                        <input type="text" class="edit-name" value="${
                          review.name
                        }">
                        <div class="image-preview" style="margin: 10px 0;">
                            <img src="${
                              review.imageUrl
                            }" style="max-width: 100px; max-height: 100px; border-radius: 50%;">
                        </div>
                    </div>
                    <div class="review-display">
                        <div class="review-text">"${review.text}"</div>
                        <div class="review-stars">${"★".repeat(
                          review.rating
                        )}${"☆".repeat(5 - review.rating)}</div>
                        <div class="review-author">– ${review.name}</div>
                        <div class="review-date" style="font-size: 0.8em; color: #aaa; margin-top: 5px;">
                            ${new Date(review.date).toLocaleDateString()}
                        </div>
                        ${
                          isPending
                            ? ""
                            : '<div class="review-status" style="font-size:0.8em;color:#4CAF50;margin-top:5px;">✓ Approved</div>'
                        }
                    </div>
                    <div class="admin-actions">
                        ${
                          isPending
                            ? `
                            <button class="edit-btn">Edit</button>
                            <button class="approve-btn" style="display:none">Save & Approve</button>
                            <button class="cancel-edit-btn" style="display:none">Cancel</button>
                            <button class="reject-btn">Delete</button>
                        `
                            : `
                            <button class="edit-btn">Edit</button>
                            <button class="approve-btn" style="display:none">Save</button>
                            <button class="cancel-edit-btn" style="display:none">Cancel</button>
                            <button class="reject-btn">Delete</button>
                        `
                        }
                    </div>
                `;

    // Add event listeners
    const editBtn = element.querySelector(".edit-btn");
    const approveBtn = element.querySelector(".approve-btn");
    const cancelBtn = element.querySelector(".cancel-edit-btn");
    const rejectBtn = element.querySelector(".reject-btn");
    const editStars = element.querySelectorAll(".edit-star");

    editStars.forEach((star) => {
      star.addEventListener("click", () => {
        const rating = parseInt(star.dataset.value);
        editStars.forEach((s, i) => (s.textContent = i < rating ? "★" : "☆"));
      });
    });

    editBtn.addEventListener("click", () => {
      element.querySelector(".review-edit-fields").style.display = "block";
      element.querySelector(".review-display").style.display = "none";
      editBtn.style.display = "none";
      approveBtn.style.display = "inline-block";
      cancelBtn.style.display = "inline-block";
      rejectBtn.style.display = "none";
    });

    cancelBtn.addEventListener("click", () => {
      element.querySelector(".review-edit-fields").style.display = "none";
      element.querySelector(".review-display").style.display = "block";
      editBtn.style.display = "inline-block";
      approveBtn.style.display = "none";
      cancelBtn.style.display = "none";
      rejectBtn.style.display = "inline-block";
    });

    approveBtn.addEventListener("click", () => {
      const updatedReview = {
        ...review,
        text: element.querySelector(".edit-text").value,
        name: element.querySelector(".edit-name").value,
        rating: Array.from(element.querySelectorAll(".edit-star")).filter(
          (star) => star.textContent === "★"
        ).length,
        approved: isPending ? true : review.approved, 
      };
      updateReview(updatedReview);
    });

    rejectBtn.addEventListener("click", () => {
      if (confirm("Are you sure you want to delete this review?")) {
        // Fade out animation
        element.style.transition = "opacity 0.3s";
        element.style.opacity = "0";

        setTimeout(() => {
          deleteReview(review.id);
        }, 300);
      }
    });

    return element;
  }

  // Update review
  function updateReview(updatedReview) {
    const allReviews = JSON.parse(localStorage.getItem("userReviews")) || [];
    const updatedReviews = allReviews.map((r) =>
      r.id === updatedReview.id ? updatedReview : r
    );
    localStorage.setItem("userReviews", JSON.stringify(updatedReviews));
    refreshUI();
    showActionMessage(
      updatedReview.approved ? "✓ Review approved" : "✓ Review updated",
      "#4CAF50"
    );
  }

  // Delete review
  function deleteReview(id) {
    const allReviews = JSON.parse(localStorage.getItem("userReviews")) || [];
    const updatedReviews = allReviews.filter((review) => review.id !== id);
    localStorage.setItem("userReviews", JSON.stringify(updatedReviews));
    refreshUI();
    showActionMessage("✓ Review deleted", "#f44336");
  }

  // Refresh UI
  function refreshUI() {
    if (adminMode) {
      const activeTab = document.querySelector(".admin-tab.active");
      if (activeTab.dataset.tab === "approved") {
        loadApprovedReviews();
      } else {
        loadPendingReviews();
      }
    }
    loadSavedReviews(); 
  }
});
// WhatsApp link
const whatsappNumber = "2349064296917";
const whatsappURL = `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(
  message
)}`;
window.open(whatsappURL, "_blank");

/* =====================================================
   Joseph's Pot Chatbot (Menu + Hugging Face AI Assistant)
   ===================================================== */

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
const openSound = new Audio("https://assets.mixkit.co/sfx/preview/mixkit-game-notification-alert-1075.mp3");
const messageSound = new Audio("https://assets.mixkit.co/sfx/preview/mixkit-positive-interface-echo-3157.mp3");

// ====== SMALL-TALK DICTIONARY ======
const smallTalkDictionary = {
  // Greetings
  "hello": ["Hello there!", "Hi!", "Hey! How can I help you today?"],
  "hi": ["Hello!", "Hi there!", "Hey! What can I do for you?"],
  "hey": ["Hello!", "Hi there!", "Hey! How can I assist you?"],
  "good morning": ["Good morning! How are you today?", "Morning! Ready for some delicious food?"],
  "good afternoon": ["Good afternoon! How's your day going?", "Afternoon! What can I get for you?"],
  "good evening": ["Good evening! How was your day?", "Evening! Ready to order some tasty food?"],
  
  // How are you
  "how are you": ["I'm doing great, thanks for asking! Ready to help you with your order.", 
                 "I'm wonderful! Excited to help you get some delicious food.", 
                 "I'm doing well, thank you! How about you?"],
  
  // Who are you
  "who are you": ["I'm Joseph's Pot assistant! I'm here to help you order delicious Nigerian cuisine.", 
                 "I'm your friendly food assistant at Joseph's Pot. I can help you place orders and answer questions.", 
                 "I'm an AI assistant created to help you enjoy the best food from Joseph's Pot!"],
  
  // What can you do
  "what can you do": ["I can help you order food, answer questions about our menu, tell you about specials, and more!",
                     "I can take your order, suggest menu items, help with checkout, and answer any questions you have about Joseph's Pot.",
                     "I'm here to help you browse our menu, place orders, and make your food experience delightful!"],
  
  // Thank you
  "thank you": ["You're welcome!", "My pleasure!", "Happy to help!", "Anytime!"],
  "thanks": ["You're welcome!", "No problem!", "Happy to assist!"],
  
  // Goodbye
  "bye": ["Goodbye! Hope to see you again soon.", "Bye! Come back whenever you're hungry!", "See you later!"],
  "goodbye": ["Goodbye! Enjoy your day!", "Farewell! Don't hesitate to return if you need anything."],
  
  // Compliments
  "you are helpful": ["Thank you! I'm here to make your ordering experience easy and enjoyable.", 
                     "I appreciate that! Making your food journey smooth is my goal."],
  "you are smart": ["Thank you! I'm always learning to serve you better.", 
                   "That's kind of you to say! I try my best to be helpful."],
  
  // Menu questions
  "what do you serve": ["We serve authentic Nigerian cuisine like Nkwobi, Abacha, Nsala, Asun Rice, Ofe Owerri, and more!",
                       "Our menu features delicious Nigerian dishes including Nkwobi, Abacha, Jollof rice, Palm Wine, and various drinks."],
  "what is on the menu": ["Our menu includes Nigerian favorites like Nkwobi, Abacha, Nsala, Asun Rice, Ofe Owerri, Palm Wine, Drinks, and Jollof rice.",
                         "You can choose from various Nigerian dishes: Nkwobi, Abacha, Nsala, Asun Rice, Ofe Owerri, plus drinks and Palm Wine."],
  
  // Restaurant info
  "where are you located": ["Joseph's Pot is located at [Your Address Here]. We offer delivery and pickup options!",
                           "You can find us at [Your Address Here]. Would you like delivery or pickup?"],
  "what are your hours": ["We're open [Your Hours Here]. Feel free to order anytime through our website!",
                         "Our operating hours are [Your Hours Here]. You can place orders for delivery or pickup during these times."],
  
  // Special requests
  "do you have vegan options": ["We offer some plant-based Nigerian dishes. Let me check what's available for you!",
                               "While traditional Nigerian cuisine often includes meat, we do have some vegan-friendly options. Let me help you find something!"],
  "do you deliver": ["Yes, we deliver! Please provide your address so we can check if you're in our delivery area.",
                    "We certainly deliver! Let me know your location and I'll check our delivery coverage."]
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
  voicesList = window.speechSynthesis && window.speechSynthesis.getVoices ? window.speechSynthesis.getVoices() : [];
  preferredVoice = voicesList.find(v => /ng/i.test(v.lang || "") && /female/i.test((v.name || "").toLowerCase()));
  if (!preferredVoice) {
    preferredVoice = voicesList.find(v => /ng/i.test(v.lang || ""));
    if (!preferredVoice) preferredVoice = voicesList.find(v => /female/i.test((v.name || "").toLowerCase()));
    if (!preferredVoice) preferredVoice = voicesList.find(v => /en/i.test(v.lang || ""));
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
    utter.lang = (preferredVoice && preferredVoice.lang) ? preferredVoice.lang : "en-NG";
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
      const isCartConfirmation = /added[\s\S]*\b(cart|to your cart|to cart)\b/i.test(text)
                               || /\badded to your cart\b/i.test(text)
                               || /\badded to cart\b/i.test(text);
      if (isCartConfirmation) return;
    }
  } catch (e) { console.warn("trySpeak cart-suppression check error:", e); }
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

let savedName = localStorage.getItem("aiChat-name") || null;
let savedAddress = localStorage.getItem("aiChat-address") || null;
let lastOrder = JSON.parse(localStorage.getItem("aiChat-lastOrder") || "[]");

// === Map chat food names -> your real menu item IDs (update IDs to match your menuItems) ===
const chatFoodMap = {
  "Nkwobi": 1,
  "Abacha": 2,
  "Nsala": 3,
  "Asun Rice": 4,
  "Ofe Owerri": 5,
  "Palm Wine": 6,
  "Drinks": 7,
  "Jollof": 8
};

// --- Basic price map used for checkout summary (edit prices as you like) ---
const menuPrices = {
  "nkwobi": 3000,
  "abacha": 1500,
  "nsala": 2500,
  "asun rice": 3500,
  "ofe owerri": 3200,
  "palm wine": 800,
  "drinks": 500,
  "jollof": 2500
};

// --- helper to get local widget cart (used for checkout summary) ---
function getLocalWidgetCart() {
  return JSON.parse(localStorage.getItem("aiChat-localCart") || "[]");
}
function setLocalWidgetCart(arr) {
  localStorage.setItem("aiChat-localCart", JSON.stringify(arr));
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
}

// --- small helper to append bubbles consistently (keeps UI unchanged) ---
function appendBubble(type, msg, isTyping = false) {
  if (!messagesDiv) return;
  const p = document.createElement("p");
  p.className = isTyping ? "bot-bubble typing-indicator" : (type === "bot" ? "bot-bubble" : "user-bubble");
  p.textContent = msg;
  messagesDiv.appendChild(p);
  messagesDiv.scrollTop = messagesDiv.scrollHeight;

  if (type === "bot" && !isTyping) {
    try { messageSound.play(); } catch (e) { /* ignore autoplay errors */ }
    // speak selectively using trySpeak elsewhere
  }
}

// --- show the main "order" suggestions as clickable buttons (cleans previous suggestions first) ---
function showFoodSuggestions() {
  if (!messagesDiv) return;

  // remove existing suggestion blocks so we don't duplicate
  const prev = messagesDiv.querySelectorAll(".chat-suggestions");
  prev.forEach(el => el.remove());

  const suggestionDiv = document.createElement("div");
  suggestionDiv.className = "chat-suggestions";
  suggestionDiv.style.display = "flex";
  suggestionDiv.style.flexWrap = "wrap";
  suggestionDiv.style.gap = "8px";
  suggestionDiv.style.margin = "8px 0";

  Object.keys(chatFoodMap).forEach(food => {
    const btn = document.createElement("button");
    btn.className = "chat-suggestion-btn";
    btn.setAttribute("data-name", food);
    btn.setAttribute("data-id", chatFoodMap[food]);
    btn.type = "button";
    // styling is minimal — won't override site design (you can style .chat-suggestion-btn in your CSS)
    btn.style.padding = "6px 10px";
    btn.style.borderRadius = "6px";
    btn.style.border = "1px solid rgba(0,0,0,0.08)";
    btn.style.background = "white";
    btn.style.cursor = "pointer";
    btn.textContent = food;
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
    appendBubble("bot", "✅ Your order history has been cleared.");
    trySpeak("Your order history has been cleared.");
  };
  suggestionDiv.appendChild(clearBtn);

  messagesDiv.appendChild(suggestionDiv);
  messagesDiv.scrollTop = messagesDiv.scrollHeight;
}

// --- helper: show checkout summary (count + total) using widget local cart ---
function showCartSummary() {
  const localCart = getLocalWidgetCart();
  const count = localCart.length;
  let total = 0;
  localCart.forEach(it => {
    const key = (it.name || "").toLowerCase();
    total += (menuPrices[key] || 0);
  });
  appendBubble("bot", `🛒 You have ${count} item${count === 1 ? "" : "s"} in your cart.\n💰 Total: ₦${total.toLocaleString()}`);
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

    // Confirm to user (consistent messaging)
    appendBubble("bot", `✅ ${foodName} added to your cart.`);
    // speak confirmation (queued if necessary). trySpeak suppresses cart-add messages via regex already.
    trySpeak(`${foodName} added to your cart.`);

    // Update click count and possibly prompt checkout after 3 additions
    cartClickCount += 1;

    if (cartClickCount >= 3) {
      // remove old suggestion blocks (so checkout options are visible)
      const prevBlocks = messagesDiv.querySelectorAll(".chat-suggestions");
      prevBlocks.forEach(el => el.remove());

      appendBubble("bot", `🛒 You've added ${cartClickCount} items. Would you like to checkout now or keep adding more?`);
      trySpeak(`You have added ${cartClickCount} items. Would you like to checkout now or keep adding more?`);

      const choiceDiv = document.createElement("div");
      choiceDiv.className = "chat-suggestions";

      const checkoutBtn = document.createElement("button");
      checkoutBtn.type = "button";
      checkoutBtn.className = "chat-suggestion-btn";
      checkoutBtn.textContent = "Checkout";
      checkoutBtn.style.marginRight = "8px";
      checkoutBtn.onclick = () => {
        // show widget summary AND try to open site cart
        showCartSummary();
        trySpeak("Opening your cart. Please review and proceed to payment.");
        const cartIcon = document.getElementById("cart-icon") || document.querySelector(".cart-toggle") || document.querySelector(".open-cart");
        if (cartIcon) {
          cartIcon.click();
        } else {
          appendBubble("bot", "If your cart didn't open automatically, please open it from the page header.");
        }
      };

      const moreBtn = document.createElement("button");
      moreBtn.type = "button";
      moreBtn.className = "chat-suggestion-btn";
      moreBtn.textContent = "Add More";
      moreBtn.onclick = () => {
        appendBubble("bot", "Sure — here are more options:");
        // reset count so we prompt again after next 3 clicks if needed
        cartClickCount = 0;
        showFoodSuggestions();
      };

      choiceDiv.appendChild(checkoutBtn);
      choiceDiv.appendChild(moreBtn);
      messagesDiv.appendChild(choiceDiv);
      messagesDiv.scrollTop = messagesDiv.scrollHeight;
    } else {
      // After short delay confirm and show suggestions again
      setTimeout(() => {
        appendBubble("bot", "Would you like to add another? Here are some options:");
        showFoodSuggestions();
      }, 700);
    }
  });
}

// --- Ask for order (bot prompt that triggers suggestions) ---
function askForOrder() {
  appendBubble("bot", "Would you like Nkwobi, Abacha, Nsala, Asun Rice, Ofe Owerri, Palm Wine, Drinks, or Jollof today?");
  showFoodSuggestions();
}

// --- Multi free fallback: tries Dictionary -> DuckDuckGo -> Wikipedia -> generic ---
async function getFallbackAnswer(query) {
  // First check if it's small talk
  const smallTalkResponse = matchSmallTalk(query);
  if (smallTalkResponse) {
    return smallTalkResponse;
  }

  // small helper to return gracefully
  const generic = "🤔 I'm not sure about that. Can you rephrase or ask something else?";

  // 1) dictionaryapi.dev for single-word definitions (best-effort)
  try {
    // only attempt dictionary for single tokens or short words
    if (typeof query === "string" && query.trim().split(/\s+/).length <= 2) {
      const dRes = await fetch(`https://api.dictionaryapi.dev/api/v2/entries/en/${encodeURIComponent(query)}`);
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
    const ddgRes = await fetch(`https://api.duckduckgo.com/?q=${encodeURIComponent(query)}&format=json&no_redirect=1&no_html=1`);
    if (ddgRes.ok) {
      const ddgData = await ddgRes.json();
      if (ddgData?.AbstractText) {
        return `🔎 ${ddgData.AbstractText}`;
      }
      // if RelatedTopics contains a snippet, use it
      if (Array.isArray(ddgData?.RelatedTopics) && ddgData.RelatedTopics.length) {
        const topic = ddgData.RelatedTopics[0];
        if (topic?.Text) return `🔎 ${topic.Text}`;
      }
    }
  } catch (e) {
    console.warn("duckduckgo fallback failed", e);
  }

  // 3) Wikipedia summary
  try {
    const wpRes = await fetch(`https://en.wikipedia.org/api/rest_v1/page/summary/${encodeURIComponent(query)}`);
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
      chatContainer.style.display = "flex";                // popup chat
      try { openSound.play(); } catch(e) { /* ignore */ }
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
    try { openSound.play(); } catch(e) {}
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
      appendBubble("bot", `Welcome back ${savedName}! I have your delivery address as "${savedAddress}". What would you like to order today?`);
      // show last order suggestion (if any)
      const stored = JSON.parse(localStorage.getItem("aiChat-lastOrder") || "[]");
      if (stored && stored.length > 0) {
        appendBubble("bot", `Last time you ordered: ${stored.join(", ")}. Would you like the same or modify it?`);
      }
      askForOrder();
      if (!suggestionShown) {
        appendBubble("bot", 'Tip: If you want to update your details, just type "change my details".');
        suggestionShown = true;
      }
    } else if (savedName && !savedAddress) {
      appendBubble("bot", `Welcome back ${savedName}! Please provide your delivery address:`);
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
    const text = (userInput && userInput.value) ? userInput.value.trim() : "";
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
      appendBubble("bot", `Thanks ${text}! Please enter your delivery address:`);
      return;
    }
    if (awaitingAddress) {
      localStorage.setItem("aiChat-address", text);
      awaitingAddress = false;
      appendBubble("bot", "Great! What would you like to order?");
      askForOrder();
      if (!suggestionShown) {
        appendBubble("bot", 'Tip: If you want to update your details, just type "change my details".');
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
      appendBubble("bot", "Alright — let's update your details. What's your name?");
      return;
    }

    // Clear order via typed command (keeps previous behavior)
    if (text.toLowerCase().includes("clear order") || text.toLowerCase().includes("reset")) {
      if (!confirm("Are you sure you want to clear your order history?")) return;
      localStorage.removeItem("aiChat-lastOrder");
      lastOrder = [];
      setLocalWidgetCart([]);
      appendBubble("bot", "✅ Your order history has been cleared.");
      trySpeak("Your order history has been cleared.");
      return;
    }

    // If user typed a food item explicitly, try to map and add
    const typedFood = Object.keys(chatFoodMap).find(f => text.toLowerCase().includes(f.toLowerCase()));
    if (typedFood) {
      const itemId = chatFoodMap[typedFood];
      // use safeAddToCart so we don't display "(addToCart not found)"
      safeAddToCart(itemId, typedFood);
      appendBubble("bot", `✅ ${typedFood} added to your cart.`);
      trySpeak(`${typedFood} added to your cart.`);

      // save last order already handled in safeAddToCart
      cartClickCount++;
      if (cartClickCount >= 3) {
        appendBubble("bot", `🛒 You've added ${cartClickCount} items. Checkout or add more?`);
        // show checkout/add more choices
        const div = document.createElement("div");
        div.className = "chat-suggestions";
        const cBtn = document.createElement("button");
        cBtn.textContent = "Checkout";
        cBtn.onclick = () => {
          // show summary and try open site cart
          showCartSummary();
          const cartIcon = document.getElementById("cart-icon")
            || document.querySelector(".cart-toggle")
            || document.querySelector(".open-cart");
          if (cartIcon) cartIcon.click();
          else appendBubble("bot", "Please open the cart to proceed to checkout.");
        };
        const mBtn = document.createElement("button");
        mBtn.textContent = "Add More";
        mBtn.onclick = () => { cartClickCount = 0; appendBubble("bot", "Here are more options:"); showFoodSuggestions(); };
        div.appendChild(cBtn); div.appendChild(mBtn);
        messagesDiv.appendChild(div);
      } else {
        setTimeout(() => { appendBubble("bot", "Would you like to add another?"); showFoodSuggestions(); }, 600);
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
  const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
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
      const transcript = ev.results && ev.results[0] && ev.results[0][0] && ev.results[0][0].transcript;
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
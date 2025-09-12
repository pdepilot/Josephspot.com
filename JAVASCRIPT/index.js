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
    "../images/image20.jpg",
    "../images/spaghetti.jpg",
    "../images/unnamed.webp",
    "../images/image50.jpg",
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
    title: "Wines During Specific Nights",
    description:
      "Join us for an exclusive evening of fine wines paired with gourmet dishes in our elegant dining room. Our sommelier will guide you through each selection.",
    date: "2025-09-19T2:00:00",
    eventImage: "../images/image52.jpg",
  },
  {
    title: "Cultural Night Experience",
    description:
      "Celebrate Igbo heritage through an immersive culinary journey featuring traditional cuisine, live music, and cultural performances.",
    date: "2025-08-30T19:00:00",
    eventImage: "../images/image51.jpg",
  },
  {
    title: "Palm Wine & Poetry",
    description:
      "An intimate evening of locally sourced palm wine tasting accompanied by live poetry readings under the stars in our garden terrace.",
    date: "2025-09-30T19:30:00",
    eventImage: "../images/image50.jpg",
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
        approved: isPending ? true : review.approved, // Keep approved status if already approved
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
    loadSavedReviews(); // Refresh the main display
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

// --- Append chat bubbles to the chat window ---
function appendBubble(sender, text) {
  const chatBox = document.getElementById("chat-box");
  const bubble = document.createElement("div");
  bubble.className = sender === "bot" ? "bot-bubble" : "user-bubble";
  bubble.innerText = text;
  chatBox.appendChild(bubble);
  chatBox.scrollTop = chatBox.scrollHeight;
}

// --- Send user message ---
async function sendMessage() {
  const input = document.getElementById("chat-input");
  const message = input.value.trim();
  if (!message) return;

  // Show user's message
  appendBubble("user", message);
  input.value = "";

  // Handle reply
  await handleFoodQuestion(message);
}

// --- Rule-based replies for common restaurant questions ---
function generateFoodResponse(text) {
  text = text.toLowerCase();

  if (text.includes("menu") || text.includes("food")) {
    return "🍽️ Our menu includes: Jollof Rice, Egusi Soup, Suya, Pepper Soup, Fried Plantain, and more!";
  }
  if (text.includes("open") || text.includes("time") || text.includes("hours")) {
    return "⏰ We are open daily from 9:00 AM to 10:00 PM.";
  }
  if (text.includes("location") || text.includes("where")) {
    return "📍 Joseph's Pot is located in Lagos, Nigeria.";
  }
  if (text.includes("order") || text.includes("delivery")) {
    return "🚚 You can place an order directly on our website or via WhatsApp for quick delivery!";
  }
  if (text.includes("thanks") || text.includes("thank you")) {
    return "😊 You're welcome! We look forward to serving you at Joseph's Pot.";
  }

  return null; // fallback to AI if no rule found
}

// --- Hugging Face API call (Falcon-7B Instruct) ---
async function callHF(message) {
  const response = await fetch("https://api-inference.huggingface.co/models/tiiuae/falcon-7b-instruct", {
    method: "POST",
    headers: {
      "Authorization": "Bearer YOUR_HF_API_KEY", // ⚠️ Replace with your Hugging Face API key
      "Content-Type": "application/json"
    },
    body: JSON.stringify({
      inputs: `You are Chef Joseph, a friendly Nigerian restaurant assistant. Be polite and helpful.\nUser: ${message}\nAssistant:`
    })
  });

  const data = await response.json();
  if (data.error) {
    console.error("HF error:", data.error);
    return "😔 Sorry, I’m having trouble connecting to the assistant right now.";
  }

  return data[0].generated_text;
}

// --- Handle customer questions (rules + AI fallback) ---
async function handleFoodQuestion(text) {
  // Try rule-based response first
  const ruleReply = generateFoodResponse(text);
  if (ruleReply) {
    appendBubble("bot", ruleReply);
    return;
  }

  // Otherwise, fallback to Hugging Face AI
  appendBubble("bot", "🤔 Let me think...");
  try {
    const reply = await callHF(text);
    // Replace "thinking..." bubble with AI reply
    document.querySelector("#chat-box .bot-bubble:last-child").innerText = reply;
  } catch (err) {
    console.error("AI error:", err);
    document.querySelector("#chat-box .bot-bubble:last-child").innerText =
      "😔 Sorry, I’m having trouble right now. Please try again.";
  }
}

// --- Enter key support for sending messages ---
document.getElementById("chat-input").addEventListener("keypress", function (e) {
  if (e.key === "Enter") {
    sendMessage();
  }
});

// --- Optional: Button click handler ---
document.getElementById("send-btn").addEventListener("click", sendMessage);

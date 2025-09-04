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
    progress += Math.random() * 20 + 10; // jump faster (10–30% per tick)
    if (progress >= 100) {
      progress = 100;
      clearInterval(interval);
      setTimeout(() => {
        preloader.style.opacity = "0";
        setTimeout(() => {
          preloader.style.display = "none";
          content.style.display = "block";
        }, 2000); // faster fade
      }, 2000);
    }
    progressBar.style.width = progress + "%";
    percentage.textContent = Math.round(progress) + "%";
  }, 80); // ultra fast tick speed
  

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

  const addReviewBtn = document.getElementById("addReviewBtn");
  const reviewForm = document.getElementById("reviewForm");
  const submitReviewForm = document.getElementById("submitReviewForm");
  const cancelReviewBtn = document.getElementById("cancelReviewBtn");
  const stars = document.querySelectorAll(".star");
  const reviewRatingInput = document.getElementById("reviewRating");
  // Admin elements
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
    reviewForm.style.display = "block";
    addReviewBtn.style.display = "none";
    stopSlideShow();
  });

  cancelReviewBtn.addEventListener("click", () => {
    reviewForm.style.display = "none";
    addReviewBtn.style.display = "block";
    submitReviewForm.reset();
    reviewRatingInput.value = "5";
    updateStars(5);
    startSlideShow();
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
      approved: false,
      date: new Date().toISOString(),
    };

    // Save to localStorage
    saveReviewToStorage(newReview);

    // Reset form and hide it
    submitReviewForm.reset();
    reviewRatingInput.value = "5";
    updateStars(5);
    reviewForm.style.display = "none";
    addReviewBtn.style.display = "block";

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
    savedReviews.unshift(review);
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
  if (
    window.location.search.includes("admin") ||
    window.location.hash.includes("admin")
  ) {
    adminToggle.style.display = "block";
  }
  adminToggle.addEventListener("click", () => {
    const password = prompt("Enter admin password:");
    if (password === "admin1982") {
      // Change this to a more secure password in production
      adminMode = !adminMode;
      adminPanel.style.display = adminMode ? "block" : "none";
      adminToggle.textContent = adminMode ? "ADMIN MODE ON" : "Admin Mode";
      if (adminMode) {
        loadPendingReviews();
        // Load approved reviews if that tab is active
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
// === Chef Joseph AI Chatbot - Enhanced with Food Knowledge ===
// DOM Elements
const chatContainer = document.getElementById("aiChat-container");
const messagesDiv = document.getElementById("aiChat-messages");
const openBtn = document.getElementById("aiChat-open-btn");
const closeBtn = document.getElementById("aiChat-close");
const sendBtn = document.getElementById("aiChat-send-btn");
const userInput = document.getElementById("aiChat-user-input");
const voiceBtn = document.getElementById("aiChat-voice-btn");
const welcomeBox = document.getElementById("aiChat-welcome-options");
const liveBtn = document.getElementById("ai-start-live");
const waBtn = document.getElementById("ai-start-whatsapp");
const inputArea = document.getElementById("aiChat-input-area");

// Sounds
const openSound = new Audio(
  "https://assets.mixkit.co/sfx/preview/mixkit-game-notification-alert-1075.mp3"
);
const messageSound = new Audio(
  "https://assets.mixkit.co/sfx/preview/mixkit-positive-interface-echo-3157.mp3"
);
const successSound = new Audio(
  "https://assets.mixkit.co/sfx/preview/mixkit-achievement-bell-600.mp3"
);

// Chat state
let awaitingName = false;
let awaitingAddress = false;
let awaitingMealChoice = false;
let suggestionCount = 0;
let isAskingAboutPreviousOrder = false;
let isSuggestingItem = false;
let currentSuggestion = null;
let voiceRecognitionActive = false;

let customerName = localStorage.getItem("jp_customerName") || "";
let customerAddress = localStorage.getItem("jp_customerAddress") || "";
let cart = JSON.parse(localStorage.getItem("jp_cart") || "[]");
let currentMenuMessageId = null;
let lastOrderedItem = localStorage.getItem("jp_lastItem") || "";
let previousOrder = JSON.parse(
  localStorage.getItem("jp_previousOrder") || "[]"
);

/* Enhanced Menus */
const lunchMenu = [
  {
    id: 1,
    name: "Ofe Owerri",
    price: 25000,
    category: "soup",
    description:
      "A rich Igbo soup made with assorted meats, fish, and vegetables",
  },
  {
    id: 2,
    name: "Nsala Soup",
    price: 20000,
    category: "soup",
    description: "White soup made with catfish and yam",
  },
  {
    id: 3,
    name: "Onugbu Soup",
    price: 18000,
    category: "soup",
    description: "Bitterleaf soup with assorted meats",
  },
  {
    id: 4,
    name: "Jollof Rice",
    price: 15000,
    category: "rice",
    description: "Popular West African rice dish cooked in tomato sauce",
  },
  {
    id: 5,
    name: "Pepper Soup",
    price: 10000,
    category: "soup",
    description: "Spicy broth with meat or fish",
  },
  {
    id: 6,
    name: "Drinks",
    price: 5000,
    category: "drink",
    description: "Assorted soft drinks and water",
  },
  {
    id: 7,
    name: "Nkwobi",
    price: 20000,
    category: "appetizer",
    description: "Spicy cow foot delicacy",
  },
  {
    id: 8,
    name: "Native Rice",
    price: 12000,
    category: "rice",
    description: "Local rice with palm oil and vegetables",
  },
  {
    id: 9,
    name: "Asun Rice",
    price: 18000,
    category: "rice",
    description: "Spicy grilled goat with rice",
  },
  {
    id: 10,
    name: "Coconut Rice",
    price: 15000,
    category: "rice",
    description: "Rice cooked with coconut milk",
  },
  {
    id: 11,
    name: "White Rice & Stew",
    price: 10000,
    category: "rice",
    description: "Plain rice with Nigerian tomato stew",
  },
  {
    id: 12,
    name: "Fried Rice",
    price: 15000,
    category: "rice",
    description: "Chinese-style fried rice with vegetables",
  },
  {
    id: 13,
    name: "Matching Ground and Piom-piom",
    price: 12000,
    category: "special",
    description: "Local delicacy with pounded yam and special sauce",
  },
  {
    id: 14,
    name: "Spaghetti",
    price: 8000,
    category: "pasta",
    description: "Italian pasta with Nigerian-style sauce",
  },
  {
    id: 15,
    name: "Indomie",
    price: 7000,
    category: "noodles",
    description: "Popular instant noodles with eggs",
  },
  {
    id: 16,
    name: "Egusi Soup",
    price: 18000,
    category: "soup",
    description: "Melon seed soup with vegetables",
  },
  {
    id: 17,
    name: "Oha Soup",
    price: 20000,
    category: "soup",
    description: "Ora soup with cocoyam paste",
  },
  {
    id: 18,
    name: "Bitter Soup",
    price: 18000,
    category: "soup",
    description: "Traditional medicinal soup",
  },
  {
    id: 19,
    name: "Abacha",
    price: 12000,
    category: "salad",
    description: "African salad made with dried cassava",
  },
  {
    id: 20,
    name: "Jitazzi",
    price: 15000,
    category: "special",
    description: "Special house recipe",
  },
  {
    id: 21,
    name: "Peppered Beef",
    price: 15000,
    category: "protein",
    description: "Spicy stir-fried beef",
  },
  {
    id: 22,
    name: "Peppered Goat Meat",
    price: 18000,
    category: "protein",
    description: "Spicy stir-fried goat meat",
  },
];

const breakfastMenu = [
  {
    id: "b1",
    name: "Akara & Pap",
    price: 5000,
    category: "breakfast",
    description: "Bean cakes with corn pudding",
  },
  {
    id: "b2",
    name: "Bread & Tea",
    price: 3500,
    category: "breakfast",
    description: "Fresh bread with tea or coffee",
  },
  {
    id: "b3",
    name: "Noodles & Egg",
    price: 4500,
    category: "breakfast",
    description: "Instant noodles with fried egg",
  },
  {
    id: "b4",
    name: "Nri Ututu",
    price: 6000,
    category: "breakfast",
    description: "Traditional morning meal",
  },
];

// Food knowledge database
const foodKnowledge = {
  ingredients: {
    egusi:
      "Egusi is melon seeds that are ground and used as a thickener in soups. It's rich in protein and healthy fats.",
    bitterleaf:
      "Bitterleaf is a leafy vegetable used in Nigerian soups. It has a distinct bitter taste and is known for its medicinal properties.",
    ogbono:
      "Ogbono is African mango seeds used to make a slimy, delicious soup. It's rich in fat and protein.",
    okra: "Okra is a vegetable that produces a slimy texture when cooked, often used in soups.",
    garri:
      "Garri is processed cassava granules that can be eaten with water, sugar and milk or used to make eba.",
  },
  dishes: {
    "jollof rice":
      "Jollof rice is a popular West African dish made with rice, tomatoes, and spices. There's a friendly rivalry between Nigerian and Ghanaian Jollof!",
    "pepper soup":
      "Pepper soup is a spicy broth often made with fish, meat, or poultry. It's known for its medicinal properties and is often served at celebrations.",
    "nsala soup":
      "Nsala soup is a white soup from Eastern Nigeria, traditionally made with catfish and yam. It's often served to new mothers.",
    "ofe owerri":
      "Ofe Owerri is a rich soup from Owerri, Imo State. It contains at least three types of meat and vegetables.",
  },
  nutrition: {
    protein:
      "Our protein-rich dishes like Peppered Goat Meat and Nkwobi help build and repair tissues. They're great after workouts!",
    vitamins:
      "Our soups are packed with vegetables that provide essential vitamins and minerals for overall health.",
    carbs:
      "Our rice dishes and swallows provide carbohydrates for energy. Perfect for fueling your day!",
    health:
      "Many Nigerian soups like Bitter Soup and Onugbu have medicinal properties and are used in traditional medicine.",
  },
  cooking: {
    "how to cook":
      "Our dishes are prepared with traditional methods and secret family recipes passed down through generations.",
    "cooking time":
      "Most of our soups take 1-2 hours to prepare properly to develop the authentic flavors.",
    spices:
      "We use a blend of traditional spices like uziza, ehuru, and ogiri to create authentic Nigerian flavors.",
  },
};

// Get all menu items for voice recognition
const allMenuItems = [...breakfastMenu, ...lunchMenu];

// Greeting by time
function getGreeting() {
  const hr = new Date().getHours();
  if (hr >= 5 && hr < 12) return "Good morning";
  if (hr >= 12 && hr < 17) return "Good afternoon";
  return "Good evening";
}

// Get time-based suggestion (different from what's already in cart)
function getTimeBasedSuggestion() {
  const hr = new Date().getHours();
  const menu = getTimeBasedMenu();

  // Filter out items already in cart
  const availableItems = menu.filter(
    (item) => !cart.some((cartItem) => cartItem.id === item.id)
  );

  if (availableItems.length === 0) {
    return null; // No items available to suggest
  }

  // Time-based suggestions
  if (hr >= 5 && hr < 11) {
    const breakfastItem = availableItems.find(
      (item) => item.category === "breakfast"
    );
    return breakfastItem ? breakfastItem.name : availableItems[0].name;
  }

  if (hr >= 11 && hr < 16) {
    // Suggest something different for lunch
    const nonRiceItem = availableItems.find((item) => item.category !== "rice");
    return nonRiceItem ? nonRiceItem.name : availableItems[0].name;
  }

  // Evening suggestion
  const eveningItem = availableItems.find(
    (item) => item.category === "appetizer" || item.category === "protein"
  );
  return eveningItem ? eveningItem.name : availableItems[0].name;
}

// Get a suggestion that's different from the previous order
function getDifferentSuggestion(previousItemName) {
  const menu = getTimeBasedMenu();

  // Filter out the previous item and items already in cart
  const availableItems = menu.filter(
    (item) =>
      item.name !== previousItemName &&
      !cart.some((cartItem) => cartItem.id === item.id)
  );

  if (availableItems.length === 0) {
    return null; // No items available to suggest
  }

  // Return a random item from available options
  const randomIndex = Math.floor(Math.random() * availableItems.length);
  return availableItems[randomIndex].name;
}

// Get time-based menu
function getTimeBasedMenu() {
  const hr = new Date().getHours();
  return hr >= 5 && hr < 11 ? breakfastMenu : lunchMenu;
}

// Enhanced AI response system for food questions
function generateFoodResponse(userMessage) {
  const lowerMessage = userMessage.toLowerCase();

  // Check for menu item inquiries
  for (const item of allMenuItems) {
    if (lowerMessage.includes(item.name.toLowerCase())) {
      return `Our ${item.name} is ${
        item.description
      }. It's priced at ₦${item.price.toLocaleString()}. Would you like to add it to your order?`;
    }
  }

  // Check for ingredient questions
  for (const [ingredient, info] of Object.entries(foodKnowledge.ingredients)) {
    if (lowerMessage.includes(ingredient)) {
      return info;
    }
  }

  // Check for dish questions
  for (const [dish, info] of Object.entries(foodKnowledge.dishes)) {
    if (lowerMessage.includes(dish)) {
      return info;
    }
  }

  // Check for nutrition questions
  for (const [nutrient, info] of Object.entries(foodKnowledge.nutrition)) {
    if (lowerMessage.includes(nutrient)) {
      return info;
    }
  }

  // Check for cooking questions
  for (const [term, info] of Object.entries(foodKnowledge.cooking)) {
    if (lowerMessage.includes(term)) {
      return info;
    }
  }

  // Check for specific question patterns
  if (lowerMessage.includes("what") || lowerMessage.includes("tell me about")) {
    if (
      lowerMessage.includes("recommend") ||
      lowerMessage.includes("suggest")
    ) {
      const suggestion = getTimeBasedSuggestion();
      if (suggestion) {
        const item = allMenuItems.find((i) => i.name === suggestion);
        return `Based on the time of day, I'd recommend our ${suggestion}. ${item.description}. Would you like to try it?`;
      }
      return "I'd be happy to recommend something! Could you tell me if you prefer soups, rice dishes, or something else?";
    }

    if (
      lowerMessage.includes("popular") ||
      lowerMessage.includes("best seller")
    ) {
      return "Our most popular dishes are Jollof Rice, Ofe Owerri, and Pepper Soup. Many customers also love our Nkwobi for special occasions!";
    }

    if (lowerMessage.includes("spicy") || lowerMessage.includes("hot")) {
      return "Our Pepper Soup and Peppered Goat Meat are quite spicy! If you prefer milder options, I'd recommend our Native Rice or White Rice and Stew.";
    }

    if (lowerMessage.includes("vegetarian") || lowerMessage.includes("vegan")) {
      return "We have several vegetarian options including our Fried Rice (without meat), and we can prepare most soups without meat. Please let us know your preferences when ordering.";
    }
  }

  if (
    lowerMessage.includes("how long") ||
    lowerMessage.includes("delivery time")
  ) {
    return "Delivery typically takes 30-45 minutes depending on your location. During busy periods, it might take up to 60 minutes.";
  }

  if (lowerMessage.includes("price") || lowerMessage.includes("how much")) {
    return "Our prices range from ₦3,500 for breakfast items to ₦25,000 for premium soups. Would you like to see our full menu with prices?";
  }

  // Default response for unrecognized food questions
  return "I'm not sure about that specific food question. Would you like to know about our menu items instead? I can tell you about ingredients, cooking methods, or make a recommendation!";
}

// On load, update popup greeting
window.onload = () => {
  setTimeout(() => {
    chatContainer.style.display = "flex";
    openSound.play();
    const g = getGreeting();
    const wText = document.getElementById("ai-welcome-text");
    if (wText) wText.innerText = `${g}! I'm Chef Joseph.`;
  }, 2000);
};

openBtn.onclick = () => {
  chatContainer.style.display = "flex";
  openSound.play();
};
closeBtn.onclick = () => {
  chatContainer.style.display = "none";
};

liveBtn.onclick = () => {
  welcomeBox.style.display = "none";
  inputArea.style.display = "flex";
  const g = getGreeting();

  if (customerName) {
    if (lastOrderedItem) {
      appendBubble("bot", `${g}, ${customerName}! Welcome back.`);
      setTimeout(() => {
        appendBubble(
          "bot",
          `Would you like to order ${lastOrderedItem} again?`,
          true
        );
        setTimeout(() => {
          appendQuickReplies(["Yes", "No"]);
          isAskingAboutPreviousOrder = true;
        }, 1500);
      }, 1000);
    } else {
      appendBubble(
        "bot",
        `${g}, ${customerName}! Welcome back. Would you like to place an order?`
      );
      awaitingMealChoice = true;
    }
  } else {
    appendBubble("bot", `${g}! May I have your name please?`);
    awaitingName = true;
  }
};

// Send, Voice and Input
sendBtn.onclick = sendMessage;
userInput.addEventListener("keypress", (e) => {
  if (e.key === "Enter") sendMessage();
});

// Enhanced Voice to text with order recognition
if (voiceBtn) {
  voiceBtn.onclick = () => {
    if (!("webkitSpeechRecognition" in window)) {
      alert("Voice input not supported in this browser.");
      return;
    }

    if (voiceRecognitionActive) {
      return;
    }

    voiceRecognitionActive = true;
    voiceBtn.style.backgroundColor = "#e44521";

    const recognition = new webkitSpeechRecognition();
    recognition.lang = "en-US";
    recognition.continuous = false;
    recognition.interimResults = false;

    recognition.onstart = () => {
      appendBubble("bot", "I'm listening... Speak now.", true);
    };

    recognition.onresult = (event) => {
      const transcript = event.results[0][0].transcript;
      userInput.value = transcript;

      // Process voice input for menu items
      processVoiceInput(transcript);
    };

    recognition.onerror = (event) => {
      console.error("Speech recognition error", event.error);
      voiceRecognitionActive = false;
      voiceBtn.style.backgroundColor = "";

      if (event.error === "no-speech") {
        appendBubble("bot", "I didn't hear anything. Please try again.");
      } else {
        appendBubble(
          "bot",
          "Error with voice recognition. Please try typing instead."
        );
      }
    };

    recognition.onend = () => {
      voiceRecognitionActive = false;
      voiceBtn.style.backgroundColor = "";
    };

    recognition.start();
  };
}

// Process voice input to detect menu items
function processVoiceInput(transcript) {
  const lowerTranscript = transcript.toLowerCase();

  // Check if the transcript contains any menu item
  const matchedItems = allMenuItems.filter((item) =>
    lowerTranscript.includes(item.name.toLowerCase())
  );

  if (matchedItems.length > 0) {
    // If menu items detected, add them to cart
    matchedItems.forEach((item) => {
      addToCart(item);
      appendBubble("bot", `Added ${item.name} to your order!`);
    });

    // Show updated menu
    setTimeout(() => {
      showMenu();
    }, 1000);
  } else {
    // If no menu items detected, process as regular message
    sendMessage();
  }
}

function sendMessage() {
  const text = userInput.value.trim();
  if (!text) return;
  appendBubble("user", text);
  userInput.value = "";

  if (isAskingAboutPreviousOrder) {
    handlePreviousOrderResponse(text);
    return;
  }

  if (isSuggestingItem) {
    handleSuggestionResponse(text);
    return;
  }

  if (awaitingName) {
    customerName = text;
    localStorage.setItem("jp_customerName", customerName);
    awaitingName = false;
    awaitingAddress = true;
    appendBubble("bot", `Thanks ${text}! Please enter your delivery address:`);
    return;
  }

  if (awaitingAddress) {
    customerAddress = text;
    localStorage.setItem("jp_customerAddress", customerAddress);
    awaitingAddress = false;
    const hr = new Date().getHours();
    let q =
      hr >= 5 && hr < 11
        ? "Great! What would you like to eat for breakfast?"
        : hr >= 11 && hr < 16
        ? "Great! What would you like to have for lunch?"
        : "Great! What would you like this evening?";
    appendBubble("bot", q);
    awaitingMealChoice = true;
    return;
  }

  if (awaitingMealChoice) {
    awaitingMealChoice = false;
    appendBubble("bot", "Sure! Here's our menu:");
    showMenu();
    return;
  }

  // Handle food-related questions with AI
  handleFoodQuestion(text);
}

// Handle food-related questions with AI
function handleFoodQuestion(text) {
  const lowerText = text.toLowerCase();

  // Check if it's a greeting
  const greetings = [
    "hello",
    "hi",
    "hey",
    "good morning",
    "good afternoon",
    "good evening",
  ];
  const isGreeting = greetings.some((greet) => lowerText.includes(greet));

  if (isGreeting) {
    const g = getGreeting();
    appendBubble("bot", `${g}! How can I help you today?`);
    return;
  }

  // Check if it's a thank you
  const thanks = ["thank", "thanks", "appreciate"];
  const isThanks = thanks.some((thank) => lowerText.includes(thank));

  if (isThanks) {
    appendBubble(
      "bot",
      "You're welcome! Is there anything else I can help you with?"
    );
    return;
  }

  // Check if it's a menu request
  const menuRequests = [
    "menu",
    "what do you have",
    "what can i order",
    "options",
    "dishes",
  ];
  const isMenuRequest = menuRequests.some((request) =>
    lowerText.includes(request)
  );

  if (isMenuRequest) {
    appendBubble("bot", "Sure! Here's our menu:");
    showMenu();
    return;
  }

  // Check if it's about ingredients, cooking, or food information
  const foodKeywords = [
    "ingredient",
    "cook",
    "make",
    "prepare",
    "what is",
    "what are",
    "how to",
    "nutrition",
    "healthy",
    "calorie",
    "protein",
    "vitamin",
    "carbs",
    "spicy",
    "hot",
    "vegetarian",
    "vegan",
  ];
  const isFoodQuestion = foodKeywords.some((keyword) =>
    lowerText.includes(keyword)
  );

  if (isFoodQuestion) {
    const response = generateFoodResponse(text);
    appendBubble("bot", response);
    return;
  }

  // If none of the above, respond with a helpful message
  appendBubble(
    "bot",
    "I can help you with food information, place an order, or answer questions about our menu. What would you like to know?"
  );
}

// Text-to-speech function
function speakText(text) {
  if ("speechSynthesis" in window) {
    const speech = new SpeechSynthesisUtterance();
    speech.text = text;
    speech.volume = 1;
    speech.rate = 1;
    speech.pitch = 1;
    speech.lang = "en-US";

    window.speechSynthesis.speak(speech);
  }
}

function handlePreviousOrderResponse(text) {
  isAskingAboutPreviousOrder = false;
  text = text.toLowerCase();

  if (text.includes("yes") || text.includes("yeah") || text.includes("sure")) {
    // Add previous order to cart
    if (previousOrder.length > 0) {
      previousOrder.forEach((item) => {
        addToCart(item);
      });
      appendBubble(
        "bot",
        "Thank you! I've added your previous order to the cart."
      );

      // Show menu with cart
      setTimeout(() => {
        showMenu();
        // Suggest something different from what was just added
        setTimeout(() => {
          const previousItemName = previousOrder[0].name;
          const suggestion = getDifferentSuggestion(previousItemName);

          if (suggestion) {
            suggestItem(suggestion);
          } else {
            appendBubble(
              "bot",
              "Your cart looks great! Would you like to proceed to checkout?"
            );
          }
        }, 1000);
      }, 1000);
    } else if (lastOrderedItem) {
      const menu = getTimeBasedMenu();
      const item = menu.find((i) => i.name === lastOrderedItem);
      if (item) {
        addToCart(item);
        appendBubble(
          "bot",
          "Thank you! I've added " + lastOrderedItem + " to your order."
        );

        // Show menu with cart
        setTimeout(() => {
          showMenu();
          // Suggest something different from what was just added
          setTimeout(() => {
            const suggestion = getDifferentSuggestion(lastOrderedItem);

            if (suggestion) {
              suggestItem(suggestion);
            } else {
              appendBubble(
                "bot",
                "Your cart looks great! Would you like to proceed to checkout?"
              );
            }
          }, 1000);
        }, 1000);
      }
    }
  } else {
    // User doesn't want previous order
    appendBubble("bot", "No problem! Let me show you our menu.");

    // Show menu
    setTimeout(() => {
      showMenu();
      // Suggest based on time of day
      setTimeout(() => {
        const suggestion = getTimeBasedSuggestion();
        if (suggestion) {
          suggestItem(suggestion);
        }
      }, 1000);
    }, 1000);
  }
}

function suggestItem(itemName) {
  const menu = getTimeBasedMenu();
  const item = menu.find((i) => i.name === itemName);

  if (item) {
    appendBubble(
      "bot",
      `Would you like to try our ${itemName}? ${item.description}`,
      true
    );
    setTimeout(() => {
      appendQuickReplies(["Yes, please add it", "No, suggest something else"]);
      isSuggestingItem = true;
      currentSuggestion = item;
    }, 1500);
  }
}

function handleSuggestionResponse(text) {
  isSuggestingItem = false;
  text = text.toLowerCase();

  if (text.includes("yes") || text.includes("add it")) {
    addToCart(currentSuggestion);
    appendBubble(
      "bot",
      "Great! I've added " + currentSuggestion.name + " to your order."
    );
    suggestionCount = 0;

    // Show updated menu
    setTimeout(() => {
      showMenu();
      // Suggest another item different from what was just added
      setTimeout(() => {
        const suggestion = getDifferentSuggestion(currentSuggestion.name);
        if (suggestion) {
          suggestItem(suggestion);
        }
      }, 1000);
    }, 1000);
  } else {
    // User declined the suggestion
    suggestionCount++;

    if (suggestionCount >= 3) {
      appendBubble(
        "bot",
        "I see you're not sure what you want. Let me redirect you to our full menu online."
      );
      setTimeout(() => {
        window.location.href = "order online.html"; // Replace with your order online page
      }, 2000);
    } else {
      // Suggest another item different from the previous suggestion
      const suggestion = getDifferentSuggestion(currentSuggestion.name);
      if (suggestion) {
        suggestItem(suggestion);
      } else {
        appendBubble(
          "bot",
          "I don't have any more suggestions. Your cart looks great! Would you like to proceed to checkout?"
        );
      }
    }
  }
}

function appendQuickReplies(options) {
  const container = document.createElement("div");
  container.className = "quick-reply-buttons";

  options.forEach((option) => {
    const button = document.createElement("button");
    button.className = "quick-reply-btn";
    button.textContent = option;
    button.onclick = () => {
      userInput.value = option;
      sendMessage();
    };
    container.appendChild(button);
  });

  messagesDiv.appendChild(container);
  messagesDiv.scrollTop = messagesDiv.scrollHeight;
}

// SHOW MENU
function showMenu() {
  const hr = new Date().getHours();
  const items = hr >= 5 && hr < 11 ? breakfastMenu : lunchMenu;

  if (currentMenuMessageId) {
    const prev = document.getElementById(currentMenuMessageId);
    if (prev) prev.remove();
  }

  const id = "menu-" + Date.now();
  currentMenuMessageId = id;

  const container = document.createElement("div");
  container.className = "bot-bubble menu-container";
  container.id = id;

  const listDiv = document.createElement("div");
  listDiv.className = "menu-items";

  items.forEach((it) => {
    const div = document.createElement("div");
    div.className = "menu-item";

    const nameSpan = document.createElement("span");
    nameSpan.textContent = `${it.name} - ₦${it.price.toLocaleString()}`;
    nameSpan.title = it.description; // Show description on hover

    const controls = document.createElement("div");
    controls.className = "item-controls";

    const minusBtn = document.createElement("button");
    minusBtn.textContent = "-";
    minusBtn.className = "quantity-btn orange-btn";
    minusBtn.onclick = () => updateCart(it, -1);

    const qty = document.createElement("span");
    qty.className = "item-quantity";
    // Check if item is already in cart
    const cartItem = cart.find((c) => c.id === it.id);
    qty.textContent = cartItem ? cartItem.quantity : 0;

    const plusBtn = document.createElement("button");
    plusBtn.textContent = "+";
    plusBtn.className = "quantity-btn orange-btn";
    plusBtn.onclick = () => updateCart(it, 1);

    controls.appendChild(minusBtn);
    controls.appendChild(qty);
    controls.appendChild(plusBtn);

    div.appendChild(nameSpan);
    div.appendChild(controls);
    listDiv.appendChild(div);
  });

  container.appendChild(listDiv);
  container.appendChild(createCartSummary());

  const action = document.createElement("div");
  action.className = "menu-actions";

  if (cart.length > 0) {
    const clearButton = document.createElement("button");
    clearButton.textContent = "Clear Order";
    clearButton.className = "clear-btn orange-btn";
    clearButton.onclick = clearCart;

    const waBtn2 = document.createElement("button");
    waBtn2.textContent = "Send via WhatsApp";
    waBtn2.className = "whatsapp-btn green-btn";
    waBtn2.onclick = sendViaWhatsApp;

    action.appendChild(clearButton);
    action.appendChild(waBtn2);
  }

  container.appendChild(action);
  messagesDiv.appendChild(container);
  messagesDiv.scrollTop = messagesDiv.scrollHeight;
}

// Cart functions
function addToCart(item) {
  const found = cart.find((c) => c.id === item.id);
  if (found) {
    found.quantity += 1;
  } else {
    cart.push({ id: item.id, name: item.name, price: item.price, quantity: 1 });
  }

  // Save to localStorage
  localStorage.setItem("jp_cart", JSON.stringify(cart));
  localStorage.setItem("jp_lastItem", item.name);

  // Update previous order
  previousOrder = [
    { id: item.id, name: item.name, price: item.price, quantity: 1 },
  ];
  localStorage.setItem("jp_previousOrder", JSON.stringify(previousOrder));
}

function updateCart(item, change) {
  const found = cart.find((c) => c.id === item.id);
  if (found) {
    found.quantity += change;
    if (found.quantity <= 0) {
      cart = cart.filter((c) => c.id !== item.id);
    }
  } else if (change > 0) {
    cart.push({ id: item.id, name: item.name, price: item.price, quantity: 1 });
  }

  // Save to localStorage
  localStorage.setItem("jp_cart", JSON.stringify(cart));
  localStorage.setItem("jp_lastItem", item.name);

  // Update previous order
  if (change > 0) {
    previousOrder = [
      { id: item.id, name: item.name, price: item.price, quantity: 1 },
    ];
    localStorage.setItem("jp_previousOrder", JSON.stringify(previousOrder));
  }

  showMenu();
}

function clearCart() {
  cart = [];
  localStorage.setItem("jp_cart", JSON.stringify(cart));
  showMenu();
  appendBubble("bot", "Cart cleared.");
}

function createCartSummary() {
  const wrap = document.createElement("div");
  wrap.className = "cart-summary";
  const title = document.createElement("h4");
  title.textContent = "Your Order:";
  wrap.appendChild(title);
  if (cart.length === 0) {
    wrap.appendChild(document.createTextNode("Your cart is empty"));
    return wrap;
  }
  const ul = document.createElement("ul");
  cart.forEach((c) => {
    const li = document.createElement("li");
    li.textContent = `${c.name} x ${c.quantity}`;
    ul.appendChild(li);
  });
  wrap.appendChild(ul);
  const total = cart.reduce((sum, c) => sum + c.price * c.quantity, 0);
  const totDiv = document.createElement("div");
  totDiv.className = "cart-total";
  totDiv.textContent = `Total: ₦${total.toLocaleString()}`;
  wrap.appendChild(totDiv);
  return wrap;
}

function sendViaWhatsApp() {
  const msg = encodeURIComponent(generateOrderText());
  // Save order as previous order
  localStorage.setItem("jp_previousOrder", JSON.stringify(cart));
  // Clear cart but keep for display until sent
  const tempCart = [...cart];
  cart = [];
  localStorage.setItem("jp_cart", JSON.stringify(cart));

  appendBubble("bot", "Order sent! We'll contact you shortly.");
  successSound.play();

  // Show empty cart
  setTimeout(() => {
    showMenu();
  }, 500);

  // Open WhatsApp
  setTimeout(() => {
    window.open(`https://wa.me/2349064296917?text=${msg}`, "_blank");
  }, 100);
}

function generateOrderText() {
  let text = `New Order from ${customerName}\nAddress: ${customerAddress}\n\nOrder Details:\n`;
  let total = 0;
  cart.forEach((c) => {
    let line = `${c.name} x ${c.quantity}`;
    let amount = c.price * c.quantity;
    total += amount;
    text += `${line} - ₦${amount.toLocaleString()}\n`;
  });
  text += `\nTotal: ₦${total.toLocaleString()}`;
  return text;
}
// HTML Bubble
function appendBubble(type, msg, typing = false) {
  const p = document.createElement("p");
  p.className = typing
    ? "bot-bubble typing-indicator"
    : type === "bot"
    ? "bot-bubble"
    : "user-bubble";

  if (typing) {
    p.textContent = msg;
  } else {
    p.textContent = msg;
  }

  messagesDiv.appendChild(p);
  messagesDiv.scrollTop = messagesDiv.scrollHeight;
  if (type === "bot" && !typing) messageSound.play();
}

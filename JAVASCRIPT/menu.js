// Navbar scroll effect
window.addEventListener("scroll", function () {
  const navbar = document.getElementById("navbar");
  if (window.scrollY > 50) {
    navbar.classList.add("scrolled");
  } else {
    navbar.classList.remove("scrolled");
  }
});

// Mobile menu toggle
function toggleMenu() {
  const navLinks = document.querySelector(".nav-links");
  const menuToggle = document.querySelector(".menu-toggle i");

  navLinks.classList.toggle("active");

  if (navLinks.classList.contains("active")) {
    menuToggle.classList.remove("fa-utensils");
    menuToggle.classList.add("fa-times");
  } else {
    menuToggle.classList.remove("fa-times");
    menuToggle.classList.add("fa-utensils");
  }
}

// Scroll to top button
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

// Menu filtering
document.addEventListener("DOMContentLoaded", function () {
  const filterButtons = document.querySelectorAll(".filter-btn");
  const menuSections = document.querySelectorAll(".menu-section");

  filterButtons.forEach((button) => {
    button.addEventListener("click", function () {
      // Remove active class from all buttons
      filterButtons.forEach((btn) => btn.classList.remove("active"));

      // Add active class to clicked button
      this.classList.add("active");

      const filterValue = this.getAttribute("data-filter");

      // Show/hide menu sections based on filter
      menuSections.forEach((section) => {
        if (
          filterValue === "all" ||
          section.getAttribute("data-category") === filterValue
        ) {
          section.classList.remove("hidden");
          section.style.opacity = "0";
          section.style.transform = "translateY(20px)";

          setTimeout(() => {
            section.style.opacity = "1";
            section.style.transform = "translateY(0)";
          }, 50);
        } else {
          section.classList.add("hidden");
        }
      });
    });
  });

  // Add animation to menu items on scroll
  const observerOptions = {
    threshold: 0.1,
    rootMargin: "0px 0px -50px 0px",
  };

  const observer = new IntersectionObserver(function (entries) {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = "1";
        entry.target.style.transform = "translateY(0)";
      }
    });
  }, observerOptions);

  // Observe all menu sections
  menuSections.forEach((section) => {
    observer.observe(section);
  });
});

// WhatsApp integration
const whatsappNumber = "2348104344994";
const message = "Hello! I'm interested in ordering from Joseph's Pot menu.";
const whatsappURL = `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(
  message
)}`;

document.querySelector(".whatsapp-chat").href = whatsappURL;

// Enhanced JavaScript with cosmic effects and scroll reveal
document.addEventListener("DOMContentLoaded", function () {
  // Enhanced scroll to top button
  const scrollBtn = document.getElementById("scrollTopBtn");

  window.onscroll = function () {
    if (
      document.body.scrollTop > 300 ||
      document.documentElement.scrollTop > 300
    ) {
      scrollBtn.style.display = "flex";
      scrollBtn.style.alignItems = "center";
      scrollBtn.style.justifyContent = "center";
    } else {
      scrollBtn.style.display = "none";
    }

    // Scroll reveal functionality
    revealOnScroll();
  };

  scrollBtn.onclick = function () {
    window.scrollTo({
      top: 0,
      behavior: "smooth",
    });
  };

  // Modal functionality
  const modal = document.getElementById("ceoModal");
  const modalImg = document.getElementById("modalImage");
  const ceoImg = document.getElementById("ceoImage");
  const closeBtn = document.getElementsByClassName("close")[0];

  ceoImg.onclick = function () {
    modal.style.display = "block";
    modalImg.src = this.src;
    modalImg.alt = this.alt;
  };

  closeBtn.onclick = function () {
    modal.style.display = "none";
  };

  window.onclick = function (event) {
    if (event.target == modal) {
      modal.style.display = "none";
    }
  };

  // Enhanced Navbar Scroll Effect
  window.addEventListener("scroll", function () {
    const navbar = document.querySelector(".navbar");
    const scrollPosition = window.scrollY;

    if (scrollPosition > 100) {
      navbar.classList.add("scrolled");

      // Add cosmic pulse effect when scrolled
      navbar.style.animation = "cosmicPulse 3s infinite alternate";
    } else {
      navbar.classList.remove("scrolled");
      navbar.style.animation = "none";
    }
  });

  // Scroll reveal function
  function revealOnScroll() {
    const reveals = document.querySelectorAll(
      ".reveal, .reveal-left, .reveal-right, .reveal-scale"
    );

    for (let i = 0; i < reveals.length; i++) {
      const windowHeight = window.innerHeight;
      const elementTop = reveals[i].getBoundingClientRect().top;
      const elementVisible = 150;

      if (elementTop < windowHeight - elementVisible) {
        reveals[i].classList.add("active");
      } else {
        reveals[i].classList.remove("active");
      }
    }
  }

  // Initial call to check elements on page load
  revealOnScroll();
});

function toggleMenu() {
  const navLinks = document.querySelector(".nav-links");
  const menuToggle = document.querySelector(".menu-toggle");

  // Toggle menu visibility
  navLinks.classList.toggle("active");
  menuToggle.classList.toggle("active");

  // Toggle hamburger/X icons
  const bars = document.querySelector(".fa-bars");
  const xmark = document.querySelector(".fa-xmark");

  if (navLinks.classList.contains("active")) {
    bars.style.display = "none";
    xmark.style.display = "inline-block";
  } else {
    bars.style.display = "inline-block";
    xmark.style.display = "none";
  }
}

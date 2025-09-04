document.addEventListener("DOMContentLoaded", () => {
    // Typing effect
    const phrases = [
      "What customers say about us",
      "Why clients love us",
      "Our reputation speaks"
    ];
  
    const typingHeading = document.getElementById("typing-heading");
    let phraseIndex = 0;
    let charIndex = 0;
    let isDeleting = false;
  
    function typeEffect() {
      const currentPhrase = phrases[phraseIndex];
  
      if (isDeleting) {
        typingHeading.textContent = currentPhrase.substring(0, charIndex--);
      } else {
        typingHeading.textContent = currentPhrase.substring(0, charIndex++);
      }
  
      if (!isDeleting && charIndex === currentPhrase.length + 1) {
        isDeleting = true;
        setTimeout(typeEffect, 1500);
        return;
      } else if (isDeleting && charIndex === 0) {
        isDeleting = false;
        phraseIndex = (phraseIndex + 1) % phrases.length;
      }
  
      setTimeout(typeEffect, isDeleting ? 45 : 85);
    }
  
    typeEffect();
  
    // Review slider
    const slides = document.querySelectorAll('.review-slide');
    const dotsContainer = document.getElementById('sliderDots');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
  
    let currentIndex = 0;
  
    slides.forEach((_, index) => {
      const dot = document.createElement('span');
      dot.classList.add('dot');
      dot.dataset.index = index;
      dot.addEventListener('click', () => showSlide(index));
      dotsContainer.appendChild(dot);
    });
  
    function showSlide(index) {
      slides.forEach((slide, i) => {
        slide.classList.remove('active', 'from-left', 'from-right', 'from-top', 'from-bottom');
        if (i === index) {
          const anims = ['from-left', 'from-right', 'from-top', 'from-bottom'];
          const random = anims[Math.floor(Math.random() * anims.length)];
          slide.classList.add('active', random);
        }
      });
  
      document.querySelectorAll('.dot').forEach(dot => dot.classList.remove('active'));
      document.querySelector(`.dot[data-index="${index}"]`).classList.add('active');
  
      currentIndex = index;
    }
  
    prevBtn.addEventListener('click', () => {
      const newIndex = (currentIndex - 1 + slides.length) % slides.length;
      showSlide(newIndex);
    });
  
    nextBtn.addEventListener('click', () => {
      const newIndex = (currentIndex + 1) % slides.length;
      showSlide(newIndex);
    });
  
    // Swipe support
    let startX = 0;
    document.querySelector('.review-slider').addEventListener('touchstart', e => {
      startX = e.changedTouches[0].clientX;
    });
    document.querySelector('.review-slider').addEventListener('touchend', e => {
      const endX = e.changedTouches[0].clientX;
      if (endX < startX - 50) nextBtn.click();
      else if (endX > startX + 50) prevBtn.click();
    });
  
    // Auto-rotate
    setInterval(() => {
      const newIndex = (currentIndex + 1) % slides.length;
      showSlide(newIndex);
    }, 7000);
  
    showSlide(currentIndex);
  });
  
const observer = new IntersectionObserver((entries) => entries.forEach((entry) => {
  if (entry.isIntersecting) entry.target.classList.add('in-view');
}), { threshold: 0.14 });
document.querySelectorAll('.manifesto, .experience-card, .quote, .visit').forEach((el) => observer.observe(el));

const carousel = document.querySelector('#explore-carousel');
const previousButton = document.querySelector('.carousel-prev');
const nextButton = document.querySelector('.carousel-next');

if (carousel && previousButton && nextButton) {
  const cardWidth = () => {
    const card = carousel.querySelector('.explore-card');
    const gap = Number.parseFloat(getComputedStyle(carousel).gap) || 0;
    return card ? card.getBoundingClientRect().width + gap : carousel.clientWidth * 0.8;
  };

  const moveCarousel = (direction) => {
    carousel.scrollBy({ left: cardWidth() * direction, behavior: 'smooth' });
  };

  previousButton.addEventListener('click', () => moveCarousel(-1));
  nextButton.addEventListener('click', () => moveCarousel(1));
  carousel.addEventListener('keydown', (event) => {
    if (event.key === 'ArrowLeft') {
      event.preventDefault();
      moveCarousel(-1);
    }
    if (event.key === 'ArrowRight') {
      event.preventDefault();
      moveCarousel(1);
    }
  });
}

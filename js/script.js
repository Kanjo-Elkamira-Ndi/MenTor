// Select the mobile menu toggle button, mobile nav, and overlay
const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
const mobileNav = document.querySelector('.mobile-nav');
const mobileNavOverlay = document.querySelector('.mobile-nav-overlay');

// Add click event listener to the toggle button
mobileMenuToggle.addEventListener('click', () => {
  // Toggle the active class on all relevant elements
  mobileMenuToggle.classList.toggle('active');
  mobileNav.classList.toggle('active');
  mobileNavOverlay.classList.toggle('active');
});

// Add click event listener to the overlay to close the menu
mobileNavOverlay.addEventListener('click', () => {
  // Remove the active class to close the menu
  mobileMenuToggle.classList.remove('active');
  mobileNav.classList.remove('active');
  mobileNavOverlay.classList.remove('active');
});

// Add click event listeners to mobile nav links to close the menu
const mobileNavLinks = document.querySelectorAll('.mobile-nav-links a');
mobileNavLinks.forEach(link => {
  link.addEventListener('click', () => {
    // Remove the active class to close the menu
    mobileMenuToggle.classList.remove('active');
    mobileNav.classList.remove('active');
    mobileNavOverlay.classList.remove('active');
  });
});
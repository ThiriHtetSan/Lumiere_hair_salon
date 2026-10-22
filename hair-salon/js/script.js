// Mobile navigation
const menuToggle = document.querySelector(".menu-toggle");
const navLinks = document.querySelector(".nav-links");

if (menuToggle) {
  menuToggle.addEventListener("click", () => {
    navLinks.classList.toggle("open");
    menuToggle.setAttribute(
      "aria-expanded",
      navLinks.classList.contains("open"),
    );
  });
}

document.querySelectorAll(".nav-links a").forEach((link) => {
  link.addEventListener("click", () => navLinks?.classList.remove("open"));
});

// Simple scroll reveal animation
const observer = new IntersectionObserver(
  (entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add("visible");
        observer.unobserve(entry.target);
      }
    });
  },
  { threshold: 0.12 },
);

document.querySelectorAll(".reveal").forEach((el) => observer.observe(el));

// Appointment form validation and API connection
const form = document.querySelector("#appointmentForm");

if (form) {
  form.addEventListener("submit", async (event) => {
    event.preventDefault();

    const message = document.querySelector("#formMessage");
    const email = document.querySelector("#email");
    const requiredFields = form.querySelectorAll("[required]");
    const submitButton = form.querySelector('button[type="submit"]');

    let valid = true;

    // Validate required fields
    requiredFields.forEach((field) => {
      field.style.borderColor = "";

      if (!field.value.trim()) {
        valid = false;
        field.style.borderColor = "#b85c5c";
      }
    });

    // Validate email
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (email.value && !emailPattern.test(email.value)) {
      valid = false;
      email.style.borderColor = "#b85c5c";
    }

    if (!valid) {
      message.textContent =
        "Please complete all required fields and enter a valid email address.";

      message.classList.add("show");
      message.style.background = "#fff0f0";
      message.style.color = "#8a3f3f";
      return;
    }

    // Send booking data to PHP API
    submitButton.disabled = true;
    submitButton.textContent = "Booking...";

    try {
      const formData = new FormData(form);

      const response = await fetch("api/create_booking.php", {
        method: "POST",
        body: formData,
      });

      const result = await response.json();

      if (!response.ok || !result.success) {
        throw new Error(result.message || "Booking failed.");
      }

      // Success message
      message.textContent =
        "Your appointment request has been submitted successfully!";

      message.classList.add("show");
      message.style.background = "#edf6ed";
      message.style.color = "#2f6332";

      form.reset();

      requiredFields.forEach((field) => {
        field.style.borderColor = "";
      });
    } catch (error) {
      // Error message
      message.textContent = error.message;
      message.classList.add("show");
      message.style.background = "#fff0f0";
      message.style.color = "#8a3f3f";
    } finally {
      submitButton.disabled = false;
      submitButton.textContent = "Book Appointment →";
    }
  });
}

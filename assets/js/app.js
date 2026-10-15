(() => {
  const toggle = document.querySelector("[data-nav-toggle]");
  const nav = document.querySelector("[data-nav]");
  if (toggle && nav) {
    toggle.addEventListener("click", () => {
      const open = nav.classList.toggle("is-open");
      toggle.setAttribute("aria-expanded", open ? "true" : "false");
      document.body.classList.toggle("nav-open", open);
    });
  }

  document.querySelectorAll("form[data-confirm]").forEach((form) => {
    form.addEventListener("submit", (e) => {
      const msg = form.getAttribute("data-confirm") || "মুছে ফেলতে চান?";
      if (!confirm(msg)) e.preventDefault();
    });
  });
  document.querySelectorAll("button[data-confirm], a[data-confirm]").forEach((el) => {
    el.addEventListener("click", (e) => {
      const msg = el.getAttribute("data-confirm") || "মুছে ফেলতে চান?";
      if (!confirm(msg)) e.preventDefault();
    });
  });

  const heroLottie = document.querySelector("[data-hero-lottie]");
  if (heroLottie) {
    const src = heroLottie.getAttribute("data-hero-lottie");
    const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    const start = () => {
      if (!window.lottie || !src) return;
      window.lottie.loadAnimation({
        container: heroLottie,
        renderer: "svg",
        loop: !reduceMotion,
        autoplay: true,
        path: src,
      });
    };
    if (window.lottie) {
      start();
    } else {
      const script = document.createElement("script");
      script.src = "https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js";
      script.onload = start;
      document.head.appendChild(script);
    }
  }

  document.querySelectorAll("form[data-validate]").forEach((form) => {
    form.addEventListener("submit", (e) => {
      let ok = true;
      form.querySelectorAll("[required]").forEach((field) => {
        field.classList.remove("is-invalid");
        if (!field.value.trim()) {
          ok = false;
          field.classList.add("is-invalid");
        }
      });
      const email = form.querySelector('input[type="email"]');
      if (email && email.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
        ok = false;
        email.classList.add("is-invalid");
      }
      const pw = form.querySelector("[data-password]");
      const pw2 = form.querySelector("[data-password-confirm]");
      if (pw && pw2 && pw.value && pw.value !== pw2.value) {
        ok = false;
        pw2.classList.add("is-invalid");
      }
      if (!ok) e.preventDefault();
    });
  });
})();

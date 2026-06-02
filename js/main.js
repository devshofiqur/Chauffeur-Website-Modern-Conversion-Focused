/* ============================================================
   ELITE DRIVE — Luxury Chauffeur Service
   main.js — Master JavaScript
   ============================================================ */

$(function () {
  "use strict";

  /* ── Preloader ── */
  $(window).on("load", function () {
    setTimeout(function () {
      $("#preloader").addClass("fade-out");
      setTimeout(function () {
        $("#preloader").remove();
      }, 700);
    }, 2200);
  });

  /* ── Hero BG Ken Burns ── */
  setTimeout(function () {
    $(".hero-bg").addClass("loaded");
  }, 300);

  /* ── Navbar Scroll ── */
  $(window).on("scroll", function () {
    if ($(this).scrollTop() > 60) {
      $("#navbar").addClass("scrolled");
    } else {
      $("#navbar").removeClass("scrolled");
    }

    // Back to top
    if ($(this).scrollTop() > 400) {
      $("#back-to-top").addClass("visible");
    } else {
      $("#back-to-top").removeClass("visible");
    }

    // Active nav link
    updateActiveNav();
  });

  function updateActiveNav() {
    var scrollTop = $(window).scrollTop() + 100;
    $("section[id]").each(function () {
      var sectionTop = $(this).offset().top;
      var sectionH = $(this).outerHeight();
      var id = $(this).attr("id");
      if (scrollTop >= sectionTop && scrollTop < sectionTop + sectionH) {
        $(".nav-link").removeClass("active");
        $(".nav-link[href='#" + id + "']").addClass("active");
      }
    });
  }

  /* ── Mobile Menu ── */
  $(".nav-hamburger").on("click", function () {
    $(this).toggleClass("open");
    $(".mobile-menu").toggleClass("open");
    $("body").toggleClass("no-scroll");
  });

  $(".mobile-menu .nav-link, .mobile-menu .btn").on("click", function () {
    $(".nav-hamburger").removeClass("open");
    $(".mobile-menu").removeClass("open");
    $("body").removeClass("no-scroll");
  });

  /* ── Smooth Scroll ── */
  $(document).on("click", 'a[href^="#"]', function (e) {
    var target = $(this.getAttribute("href"));
    if (target.length) {
      e.preventDefault();
      $("html, body").animate({ scrollTop: target.offset().top - 80 }, 700, "swing");
    }
  });

  /* ── Back to Top ── */
  $("#back-to-top").on("click", function () {
    $("html, body").animate({ scrollTop: 0 }, 700);
  });

  /* ── Scroll Reveal ── */
  var revealObserver = new IntersectionObserver(
    function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add("visible");
        }
      });
    },
    { threshold: 0.12, rootMargin: "0px 0px -40px 0px" }
  );

  document
    .querySelectorAll(".reveal, .reveal-left, .reveal-right")
    .forEach(function (el) {
      revealObserver.observe(el);
    });

  /* ── Counter Animation ── */
  function animateCounter(el) {
    var target = parseInt($(el).data("target"), 10);
    var duration = 2000;
    var step = target / (duration / 16);
    var current = 0;
    var timer = setInterval(function () {
      current += step;
      if (current >= target) {
        current = target;
        clearInterval(timer);
      }
      $(el).text(Math.floor(current) + ($(el).data("suffix") || ""));
    }, 16);
  }

  var counterObserver = new IntersectionObserver(
    function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting && !entry.target.classList.contains("counted")) {
          entry.target.classList.add("counted");
          animateCounter(entry.target);
        }
      });
    },
    { threshold: 0.5 }
  );

  document.querySelectorAll(".counter").forEach(function (el) {
    counterObserver.observe(el);
  });

  /* ── Multi-Step Hero Form ── */
  var currentStep = 1;
  var totalSteps = 3;

  function goToStep(step) {
    $(".form-step").removeClass("active");
    $(".form-step[data-step='" + step + "']").addClass("active");

    $(".step-dot").each(function () {
      var dotStep = parseInt($(this).data("step"), 10);
      $(this).removeClass("active done");
      if (dotStep === step) $(this).addClass("active");
      else if (dotStep < step) $(this).addClass("done");
    });

    $(".step-line").each(function (i) {
      if (i < step - 1) $(this).addClass("done");
      else $(this).removeClass("done");
    });

    currentStep = step;
  }

  $(document).on("click", ".btn-next", function () {
    if (validateStep(currentStep) && currentStep < totalSteps) {
      goToStep(currentStep + 1);
    }
  });

  $(document).on("click", ".btn-prev", function () {
    if (currentStep > 1) {
      goToStep(currentStep - 1);
    }
  });

  function validateStep(step) {
    var valid = true;
    var $step = $(".form-step[data-step='" + step + "']");
    $step.find("[required]").each(function () {
      if (!$(this).val().trim()) {
        $(this).css("border-color", "#ef4444");
        valid = false;
        setTimeout(function () {
          $step.find("[required]").css("border-color", "");
        }, 2000);
      }
    });
    return valid;
  }

  /* ── Hero Form Submit ── */
  $("#hero-quote-form").on("submit", function (e) {
    e.preventDefault();
    var $btn = $(this).find(".btn-submit-hero");
    $btn.text("Sending...").prop("disabled", true);

    var formData = $(this).serialize();
    $.ajax({
      url: "php/send-email.php",
      method: "POST",
      data: formData,
      success: function (res) {
        var r = JSON.parse(res);
        if (r.status === "success") {
          showHeroAlert("success", r.message);
          $("#hero-quote-form")[0].reset();
          goToStep(1);
        } else {
          showHeroAlert("error", r.message);
        }
      },
      error: function () {
        showHeroAlert("error", "Connection error. Please try again.");
      },
      complete: function () {
        $btn.text("Request My Quote").prop("disabled", false);
      },
    });
  });

  function showHeroAlert(type, msg) {
    var $alert = type === "success" ? $("#hero-success") : $("#hero-error");
    $alert.find(".alert-msg").text(msg);
    $(".alert").removeClass("show");
    $alert.addClass("show");
    setTimeout(function () {
      $alert.removeClass("show");
    }, 6000);
  }

  /* ── Contact Form Submit ── */
  $("#contact-form").on("submit", function (e) {
    e.preventDefault();
    var $btn = $(this).find(".btn-contact-submit");
    $btn.text("Sending...").prop("disabled", true);

    $.ajax({
      url: "php/send-email.php",
      method: "POST",
      data: $(this).serialize(),
      success: function (res) {
        var r = JSON.parse(res);
        if (r.status === "success") {
          showContactAlert("success", r.message);
          $("#contact-form")[0].reset();
        } else {
          showContactAlert("error", r.message);
        }
      },
      error: function () {
        showContactAlert("error", "Connection error. Please try again.");
      },
      complete: function () {
        $btn.text("Send Message").prop("disabled", false);
      },
    });
  });

  function showContactAlert(type, msg) {
    var $alert = type === "success" ? $("#contact-success") : $("#contact-error");
    $alert.find(".alert-msg").text(msg);
    $(".contact-form .alert").removeClass("show");
    $alert.addClass("show");
    setTimeout(function () {
      $alert.removeClass("show");
    }, 6000);
  }

  /* ── Fleet Slider ── */
  var fleetIndex = 0;
  var fleetItems = $(".fleet-card").length;
  var visibleCards = getVisibleCards();

  function getVisibleCards() {
    if ($(window).width() <= 768) return 1;
    if ($(window).width() <= 1100) return 2;
    return 3;
  }

  function maxFleetIndex() {
    return Math.max(0, fleetItems - visibleCards);
  }

  function updateFleet() {
    visibleCards = getVisibleCards();
    if (fleetIndex > maxFleetIndex()) fleetIndex = maxFleetIndex();
    var cardW = $(".fleet-card").first().outerWidth(true);
    $(".fleet-track").css("transform", "translateX(-" + fleetIndex * cardW + "px)");

    var dotCount = maxFleetIndex() + 1;
    $(".fleet-dots").html("");
    for (var i = 0; i <= maxFleetIndex(); i++) {
      var dot = $('<span class="fleet-dot' + (i === fleetIndex ? " active" : "") + '"></span>');
      dot.data("idx", i);
      $(".fleet-dots").append(dot);
    }
  }

  $(".fleet-next").on("click", function () {
    if (fleetIndex < maxFleetIndex()) {
      fleetIndex++;
      updateFleet();
    }
  });

  $(".fleet-prev").on("click", function () {
    if (fleetIndex > 0) {
      fleetIndex--;
      updateFleet();
    }
  });

  $(document).on("click", ".fleet-dot", function () {
    fleetIndex = $(this).data("idx");
    updateFleet();
  });

  $(window).on("resize", function () {
    updateFleet();
  });

  updateFleet();

  /* ── Stagger Delay for Cards ── */
  $(".service-card").each(function (i) {
    $(this).css("transition-delay", i * 0.08 + "s");
  });
  $(".testimonial-card").each(function (i) {
    $(this).css("transition-delay", i * 0.1 + "s");
  });

  /* ── Quote Page Form (if exists) ── */
  $("#quote-page-form").on("submit", function (e) {
    e.preventDefault();
    var $btn = $(this).find(".btn-quote-submit");
    $btn.text("Sending...").prop("disabled", true);

    $.ajax({
      url: "php/send-email.php",
      method: "POST",
      data: $(this).serialize(),
      success: function (res) {
        var r = JSON.parse(res);
        if (r.status === "success") {
          showQuoteAlert("success", r.message);
          $("#quote-page-form")[0].reset();
        } else {
          showQuoteAlert("error", r.message);
        }
      },
      error: function () {
        showQuoteAlert("error", "Connection error. Please try again.");
      },
      complete: function () {
        $btn.text("Request My Quote").prop("disabled", false);
      },
    });
  });

  function showQuoteAlert(type, msg) {
    var $alert = type === "success" ? $("#quote-success") : $("#quote-error");
    $alert.find(".alert-msg").text(msg);
    $(".quote-form .alert").removeClass("show");
    $alert.addClass("show");
    setTimeout(function () {
      $alert.removeClass("show");
    }, 6000);
  }

  /* ── Newsletter ── */
  $(".footer-newsletter").on("submit", function (e) {
    e.preventDefault();
  });

  /* ── Input date min ── */
  var today = new Date().toISOString().split("T")[0];
  $('input[type="date"]').attr("min", today);

  /* ── Navbar scroll offset init ── */
  updateActiveNav();
});
(function ($) {
    "use strict";

    // Page loading
    $(window).on("load", function () {
        $(".preloader").fadeOut("slow");
    });

    // Scroll progress
    var scrollProgress = function () {
        var docHeight = $(document).height();
        var windowHeight = $(window).height();

        $(window).on("scroll", function () {
            var scrollPercent = ($(window).scrollTop() / (docHeight - windowHeight)) * 100;
            $(".scroll-progress").width(scrollPercent + "%");
        });
    };

    // Off canvas sidebar
    var OffCanvas = function () {
        $("#off-canvas-toggle").on("click", function () {
            $("body").toggleClass("canvas-opened");
        });

        $(".dark-mark").on("click", function () {
            $("body").removeClass("canvas-opened open-search-form");
        });

        $(".off-canvas-close").on("click", function () {
            $("body").removeClass("canvas-opened");
        });
    };

    // Search form
    var openSearchForm = function () {
        $("button.search-icon").on("click", function () {
            $("body").toggleClass("open-search-form");
            $(".mega-menu-item").removeClass("open");
            $("html, body").animate({ scrollTop: 0 }, "slow");
        });

        $(".search-close").on("click", function () {
            $("body").removeClass("open-search-form");
        });
    };

    // Mobile menu
    var mobileMenu = function () {
        var menu = $("ul#mobile-menu");

        if (menu.length && $.fn.slicknav) {
            menu.slicknav({
                prependTo: ".mobile_menu",
                closedSymbol: "+",
                openedSymbol: "-"
            });
        }
    };

    // Widget submenu
    var WidgetSubMenu = function () {
        $(".menu li.menu-item-has-children").on("click", function () {
            var element = $(this);

            if (element.hasClass("open")) {
                element.removeClass("open");
                element.find("li").removeClass("open");
                element.find("ul").slideUp(200);
            } else {
                element.addClass("open");
                element.children("ul").slideDown(200);
                element.siblings("li").children("ul").slideUp(200);
                element.siblings("li").removeClass("open");
                element.siblings("li").find("li").removeClass("open");
                element.siblings("li").find("ul").slideUp(200);
            }
        });
    };

    /* =============================================
        SWIPER SLIDER
    ============================================= */
    if (typeof Swiper !== "undefined") {
        if (document.querySelector(".post-slider-4")) {
            new Swiper(".post-slider-4", {
                slidesPerView: 1,
                spaceBetween: 30,
                loop: true,
                breakpoints: {
                    1200: {
                        slidesPerView: 4
                    },
                    992: {
                        slidesPerView: 4
                    },
                    768: {
                        slidesPerView: 2
                    },
                    576: {
                        slidesPerView: 2
                    },
                    0: {
                        slidesPerView: 2
                    }
                }
            });
        }

        if (document.querySelector(".post-slider-3")) {
            new Swiper(".post-slider-3", {
                slidesPerView: 3,
                spaceBetween: 0,
                loop: true,
                breakpoints: {
                    1200: {
                        slidesPerView: 3
                    },
                    992: {
                        slidesPerView: 3
                    },
                    768: {
                        slidesPerView: 2
                    },
                    576: {
                        slidesPerView: 2
                    },
                    0: {
                        slidesPerView: 2
                    }
                }
            });
        }

        if (document.querySelector(".slider-center-3")) {
            new Swiper(".slider-center-3", {
                slidesPerView: 3,
                spaceBetween: 0,
                loop: true,
                breakpoints: {
                    1200: {
                        slidesPerView: 3
                    },
                    992: {
                        slidesPerView: 3
                    },
                    768: {
                        slidesPerView: 2
                    },
                    576: {
                        slidesPerView: 2
                    },
                    0: {
                        slidesPerView: 2
                    }
                }
            });
        }
    }

    // Header sticky
    var headerSticky = function () {
        $(window).on("scroll", function () {
            var scroll = $(window).scrollTop();

            if (scroll < 245) {
                $(".header-sticky").removeClass("sticky-bar");
            } else {
                $(".header-sticky").addClass("sticky-bar");
            }
        });
    };

    // Scroll up to top
    var scrollToTop = function () {
        if ($.scrollUp) {
            $.scrollUp({
                scrollName: "scrollUp",
                topDistance: "300",
                topSpeed: 300,
                animation: "fade",
                animationInSpeed: 200,
                animationOutSpeed: 200,
                scrollText: "<span>Go to top</span>",
                activeOverlay: false
            });
        }
    };

    // Custom scrollbar
    var customScrollbar = function () {
        if (typeof PerfectScrollbar !== "undefined" && document.querySelector(".custom-scrollbar")) {
            new PerfectScrollbar(".custom-scrollbar");
        }
    };

    // Masonry grid
    var masonryGrid = function () {
        if ($(".grid").length && $.fn.masonry) {
            $(".grid").masonry({
                itemSelector: ".grid-item",
                percentPosition: true,
                columnWidth: ".grid-sizer",
                gutter: 0
            });
        }
    };

    // More articles
    var moreArticles = function () {
        $.fn.vwScroller = function (options) {
            var defaultOptions = {
                delay: 500,
                position: 0.7,
                visibleClass: "",
                invisibleClass: ""
            };

            var isVisible = false;
            var $document = $(document);
            var $window = $(window);

            options = $.extend(defaultOptions, options);

            var observer = $.proxy(function () {
                var isInViewPort =
                    $document.scrollTop() > ($document.height() - $window.height()) * options.position;

                if (!isVisible && isInViewPort) {
                    onVisible();
                } else if (isVisible && !isInViewPort) {
                    onInvisible();
                }
            }, this);

            var onVisible = $.proxy(function () {
                isVisible = true;

                if (options.visibleClass) {
                    this.addClass(options.visibleClass);
                }

                if (options.invisibleClass) {
                    this.removeClass(options.invisibleClass);
                }
            }, this);

            var onInvisible = $.proxy(function () {
                isVisible = false;

                if (options.visibleClass) {
                    this.removeClass(options.visibleClass);
                }

                if (options.invisibleClass) {
                    this.addClass(options.invisibleClass);
                }
            }, this);

            setInterval(observer, options.delay);

            return this;
        };

        var $moreArticles = $(".single-more-articles");

        if ($moreArticles.length) {
            $moreArticles.vwScroller({
                visibleClass: "single-more-articles--visible",
                position: 0.55
            });

            $moreArticles.find(".single-more-articles-close-button").on("click", function () {
                $moreArticles.hide();
            });
        }

        $("button.single-more-articles-close").on("click", function () {
            $(".single-more-articles").removeClass("single-more-articles--visible");
        });
    };

    // Fixed footer
    var fixedFooter = function () {
        var $footer = $(".fixed-footer");
        var $content = $(".main-content");

        if ($footer.length && $content.length) {
            $content.css({
                "margin-bottom": $footer.innerHeight()
            });
        }
    };

    // Dark / Light mode
    var darkLightMode = function () {
        var savedMode = localStorage.getItem("mode");

        if (savedMode === "dark") {
            toggleDarkMode();
        } else {
            toggleLightMode();
        }

        $(".dark-light").on("click", function () {
            if ($("body").hasClass("dark-mode")) {
                toggleLightMode();
            } else {
                toggleDarkMode();
            }
        });

        function toggleDarkMode() {
            $("body").removeClass("light-mode").addClass("dark-mode");
            localStorage.setItem("mode", "dark");
        }

        function toggleLightMode() {
            $("body").removeClass("dark-mode").addClass("light-mode");
            localStorage.setItem("mode", "light");
        }
    };

    // Language select
    var languageSelect = function () {
        $(".select-language").each(function () {
            var selectedOption = $(this).find(".language-selected");
            var optionsList = $(this).find(".language-list");

            if (selectedOption.length && optionsList.length) {
                selectedOption.on("click", function () {
                    optionsList.toggle();
                });

                optionsList.on("click", "li", function () {
                    var dataContent = $(this).attr("data-content");
                    selectedOption.text(dataContent);
                    optionsList.hide();
                });
            }
        });
    };

    // User header menu
    var userHeaderMenu = function () {
        var userMenu = document.querySelector(".header-user-menu");
        var toggle = document.querySelector("[data-user-menu-toggle]");

        if (!userMenu || !toggle) {
            return;
        }

        toggle.addEventListener("click", function (event) {
            event.preventDefault();
            event.stopPropagation();
            userMenu.classList.toggle("open");
        });

        document.addEventListener("click", function (event) {
            if (!userMenu.contains(event.target)) {
                userMenu.classList.remove("open");
            }
        });

        document.addEventListener("keydown", function (event) {
            if (event.key === "Escape") {
                userMenu.classList.remove("open");
            }
        });
    };

    // Init
    $(function () {
    openSearchForm();
    OffCanvas();
    customScrollbar();
    scrollToTop();
    headerSticky();
    mobileMenu();
    WidgetSubMenu();
    scrollProgress();
    masonryGrid();
    moreArticles();
    fixedFooter();
    darkLightMode();
    languageSelect();
    userHeaderMenu();
});
})(jQuery);

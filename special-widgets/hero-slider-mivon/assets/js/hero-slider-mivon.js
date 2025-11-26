document.addEventListener("DOMContentLoaded", () => {

    // Replace data-background → background-image
    document.querySelectorAll("[data-background]").forEach(el => {
        el.style.backgroundImage = `url("${el.getAttribute("data-background")}")`;
    });

    new Swiper(".mivon-hero-slider", {
        loop: true,
        speed: 1800,
        parallax: true,

        // ONLY pagination
        pagination: {
            el: ".mivon-hero-dots",
            clickable: true,
        },

        // No mousewheel
        mousewheel: false,

        // No navigation arrows
        navigation: false,

        on: {
            progress(swiper) {
                swiper.slides.forEach(slide => {
                    const inner = slide.querySelector(".slide-bg");
                    if (!inner) return;
                    const offset = swiper.width * 0.8;
                    inner.style.transform = `translate3d(${slide.progress * offset}px,0,0)`;
                });
            },
            setTransition(swiper, speed) {
                swiper.slides.forEach(slide => {
                    const inner = slide.querySelector(".slide-bg");
                    if (!inner) return;
                    inner.style.transition = `${speed}ms`;
                });
            }
        }
    });

});
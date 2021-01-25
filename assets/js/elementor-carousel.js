/*js for custom carousels Minh Khang */
jQuery(window).on('elementor/frontend/init', () => {
    const addHandler = ($element) => {
        elementorFrontend.elementsHandler.addHandler(Adminz_carousel, {
            $element,
        });
    };
    // register action for mka testimonial in admin editor viewer    
    elementorFrontend.hooks.addAction('frontend/element_ready/adminz-carousel.default', addHandler);
});
class Adminz_carousel extends elementorModules.frontend.handlers.Base {
    getDefaultSettings() {
        var el_class = '.adminz-carousel.swiper-container';
        
        return {
            selectors: {
                swiperclass: el_class,
            }
        };
    }

    getDefaultElements() {
        const selectors = this.getSettings('selectors');
        return {
            $swiper: this.$element.find(selectors.swiperclass),
        };
    }

    bindEvents() {
        // get data settings 
        var data_settings = JSON.parse(this.elements.$swiper.attr("data-settings"));
        var swiperdata = {
            loop: (data_settings.infinite == 'yes') ? true : false,
            speed: data_settings.speed,
            slidesPerGroup: data_settings.slides_to_scroll,
            slidesPerView: data_settings.slides_to_show,
            breakpoints: {
                "0": {
                    "slidesPerView": data_settings.slides_to_show_mobile ? parseInt(data_settings.slides_to_show_mobile) : 1,
                    "slidesPerGroup": data_settings.slides_to_scroll_mobile ? parseInt(data_settings.slides_to_scroll_mobile) : 1
                },
                "768": {
                    "slidesPerView": data_settings.slides_to_show_tablet ? parseInt(data_settings.slides_to_show_tablet) : 2,
                    "slidesPerGroup": data_settings.slides_to_scroll_tablet ? parseInt(data_settings.slides_to_scroll_tablet) : 1
                },
                "1025": {
                    "slidesPerView": data_settings.slides_to_show ? parseInt(data_settings.slides_to_show) : 3,
                    "slidesPerGroup": data_settings.slides_to_scroll ? parseInt(data_settings.slides_to_scroll) : 1
                }
            }

        };
        var show_dots = (jQuery.inArray(data_settings.navigation, ["dots", "both"]) > -1);
        var show_arrows = (jQuery.inArray(data_settings.navigation, ["arrows", "both"]) > -1);
        if (show_arrows) {
            swiperdata.navigation = {
                "prevEl": ".elementor-swiper-button-prev",
                "nextEl": ".elementor-swiper-button-next"
            };
        }
        if (show_dots) {
            swiperdata.pagination = {
                "el": ".swiper-pagination",
                "type": "bullets",
                "clickable": true
            }
        }
        if (data_settings.autoplay == "yes") {
            swiperdata.autoplay = {
                "delay": data_settings.autoplay_speed,
                "disableOnInteraction": (data_settings.pause_on_interaction == "yes") ? true : false
            }
        }

        var mySwiper = new Swiper(this.elements.$swiper, swiperdata);
        //this.elements.$swiper.on( 'click', this.onButtonClick.bind( this ) );
        this.elements.$swiper.hover(function(){mySwiper.autoplay.stop()}, function(){mySwiper.autoplay.start()});

    }
}
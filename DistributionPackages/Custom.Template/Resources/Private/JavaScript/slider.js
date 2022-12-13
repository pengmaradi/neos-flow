'use strict';

import Swiper, { Navigation, Pagination, Autoplay, EffectFade, Scrollbar } from 'swiper';

Swiper.use([Navigation, Pagination, Autoplay, EffectFade, Scrollbar]);


export default class Slider {
    constructor($el) {
        const slider = document.querySelector($el);
        if ($el.length > 0) {
            new Swiper($el, {
                loop: true,
                speed: 400,
                spaceBetween: 100,

                pagination: {
                    el: '.swiper-pagination',
                },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                scrollbar: {
                    el: '.swiper-scrollbar',
                },
            })
        }

    }
}


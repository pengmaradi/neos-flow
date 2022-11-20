
const content = require("./Build/Carbon.Pipeline/purge");

/** @type {import('tailwindcss').Config} */

module.exports = {
    content,
    theme: {
        screens: {
            'xs': '320px',
            'ty': '480px',
            'sm': '768px',
            'md': '992px',
            'lg': '1200px',
            'xl': '1400px',
        },
        container: {
            center: true,
            padding: '1rem',
            screens: {
                'ty': '480px',
                'sm': '768px',
                'md': '860px',
                'lg': '1140px',
                'xl': '1300px',
            },
        },
        aspectRatio: {
            'none': 0,
            'square': [1, 1],
            '16/9': [16, 9],
            '4/3': [4, 3],
            '21/9': [21, 9],
        },
        colors: {
            transparent: 'transparent',
            black: '#000',
            white: '#fff',

            grey: {
                100: '#F7F7F7',
                200: '#EBEBEB',
                300: '#DDDDDD',
                400: '#C4C4C0',
                500: '#9A9A9A',
            },

            primary: {
                100: '#519EF9',
                DEFAULT: '#4A90E2',
                300: '#2C76CC',
            },

            body: '#000',

            fuchsia: {
                DEFAULT: '#A43BCB',
            },

            green: {
                100: '#F5FFF4',
                200: '#01811F',
            },

            red: {
                100: '#ff4141',
                200: '#D0021B',
            },
        },

        extend: {},
    },
    plugins: [
        require('tailwindcss-aspect-ratio'),
        require('tw-elements/dist/plugin'),
    ],
};

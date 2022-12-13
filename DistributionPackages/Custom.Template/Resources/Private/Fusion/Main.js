'use strict';

//import "tw-elements";

// console.log('try');
// debugger;
// console.log('next try');

import Team from '../JavaScript/team';
import Slider from '../JavaScript/slider';

document.addEventListener('DOMContentLoaded', () => {
    new Team();

    new Slider('.image-slider');
});

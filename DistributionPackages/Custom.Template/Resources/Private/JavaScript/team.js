'use strict';

export default class Team {

    constructor() {
        const selectElement = document.querySelector('.personform__select');
        if (selectElement !== null) {
            selectElement.addEventListener('change', (event) => {
                let catId = event.target.value;
                const persionElement = document.querySelectorAll('.custom_team_content_persioncart');

                persionElement.forEach((element, index) => {

                    if (!element.querySelector('.persioncart').classList.contains(catId) && catId.length > 0) {
                        element.classList.add('hidden');
                    } else {
                        element.classList.remove('hidden');
                    }
                });
            });
        }
    }
}

// src/main.js
import { createApp } from 'vue';
import App from './App.vue'; // Import your root Vue component
import './assets/global.css';

//import router from './router'; // not directly possible if the app is hosted separately 

// FontAwesome imports
import { library } from '@fortawesome/fontawesome-svg-core';
import { faStar as fasFaStar, faStarHalfAlt } from '@fortawesome/free-solid-svg-icons';
import { faStar as farFaStar } from '@fortawesome/free-regular-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

// Add icons to the library
library.add(fasFaStar, faStarHalfAlt, farFaStar);

const app = createApp(App);

// Register FontAwesomeIcon globally
app.component('font-awesome-icon', FontAwesomeIcon);



document.addEventListener('DOMContentLoaded', () => {
  app.mount('#app');
});


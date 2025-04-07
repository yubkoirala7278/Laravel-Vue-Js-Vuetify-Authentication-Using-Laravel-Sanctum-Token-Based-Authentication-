// src/main.js
// Import Vue's createApp function
import { createApp } from 'vue'
// Import the main App component
import App from './App.vue'
// Import global Axios configuration
import './axios'
// Import Pinia (State Management)
import { createPinia } from 'pinia'
// Import Vue Router (Routing)
import router from './router'
// Import Vuetify configuration
import vuetify from './plugins/vuetify'
import '@mdi/font/css/materialdesignicons.css';
// Create an instance of Pinia
const pinia = createPinia();
// Create a Vue application instance
const app = createApp(App);
// Use Pinia for state management
app.use(pinia);
// Use Vue Router for handling routes
app.use(router);
// Use Vuetify for UI components
app.use(vuetify);
// Mount the app to the DOM
app.mount('#app');
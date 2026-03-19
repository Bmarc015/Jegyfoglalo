import './assets/main.css' // Itt importálod a globális stílust

import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'
//Bootstrap: css, js
import "bootstrap/dist/css/bootstrap.min.css"
import "bootstrap"
//Icons: css
import "bootstrap-icons/font/bootstrap-icons.min.css"

import axios from 'axios';
axios.defaults.baseURL = 'http://localhost:8000';

const app = createApp(App)

app.use(createPinia())
app.use(router)

app.mount('#app')

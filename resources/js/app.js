import { createApp } from 'vue'
import App from './App.vue'
import vuetify from './plugins/vuetify'

// styles icônes
import '@mdi/font/css/materialdesignicons.css'


const el = document.getElementById('app')

if (el) {
  const app = createApp(App)
  app.use(vuetify)
  app.mount(el)
}

// import './bootstrap'
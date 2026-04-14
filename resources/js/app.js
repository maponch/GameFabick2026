import { createApp } from 'vue'
import UsersList from './components/UsersList.vue'

const app = createApp({})

app.component('users-list', UsersList)

app.mount('#app')
// import './bootstrap'
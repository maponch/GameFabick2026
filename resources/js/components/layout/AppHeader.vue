<template>
  <v-app-bar color="primary" dark>
    <v-toolbar-title>GameFabrick 🎲</v-toolbar-title>

    <v-spacer />

    <!-- SI NON CONNECTÉ -->
    <template v-if="!user">
      <v-btn v-if="mdAndUp" variant="text" href="/login">
        Login
      </v-btn>

      <v-btn v-if="mdAndUp" variant="outlined" href="/register">
        Register
      </v-btn>
    </template>

    <!-- SI CONNECTÉ -->
    <template v-else>
      <v-btn variant="text" href="/dashboard">
        Dashboard
      </v-btn>
      <v-btn v-if="user?.role === 'admin'" href="/admin">
        Admin
      </v-btn>

      <v-btn variant="text" @click="logout">
        Logout
      </v-btn>
    </template>

    <!-- MOBILE -->
    <v-btn v-if="!mdAndUp" icon>
      <v-icon>mdi-menu</v-icon>
    </v-btn>

  </v-app-bar>
</template>

<script setup>
import { useDisplay } from 'vuetify'

const { mdAndUp } = useDisplay()

const user = window.user
console.log('User:', user)

const logout = () => {
  const form = document.createElement('form')
  form.method = 'POST'
  form.action = '/logout'

  const csrf = document.createElement('input')
  csrf.type = 'hidden'
  csrf.name = '_token'
  csrf.value = document.querySelector('meta[name="csrf-token"]').content

  form.appendChild(csrf)
  document.body.appendChild(form)
  form.submit()
}
</script>
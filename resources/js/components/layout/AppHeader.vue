<template>
  <!-- HEADER -->
  <v-app-bar color="primary" dark>
    <v-toolbar-title>GameFabrick 🎲</v-toolbar-title>

    <v-spacer />

    <!-- bouton différent selon écran -->
    <v-btn v-if="mdAndUp" variant="text" href="/login">Login</v-btn>
    <v-btn v-if="mdAndUp" variant="outlined" href="/register">Register</v-btn>

    <v-btn v-else icon>
      <v-icon>mdi-menu</v-icon>
    </v-btn>
  </v-app-bar>
</template>
<script setup>
import { useDisplay } from 'vuetify'

const { mdAndUp } = useDisplay()
</script>
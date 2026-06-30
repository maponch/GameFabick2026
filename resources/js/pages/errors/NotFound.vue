<template>
  <v-container class="text-center py-12">
    <v-icon size="120" color="grey-lighten-1" class="mb-4">mdi-compass-off-outline</v-icon>
    <h1 class="text-h3 font-weight-bold mb-2">404</h1>
    <p class="text-h6 text-medium-emphasis mb-6">
      Cette page n'existe pas (ou plus).
    </p>
    <div class="d-flex justify-center ga-3 flex-wrap">
      <v-btn color="primary" size="large" to="/" prepend-icon="mdi-home">
        Retour à l'accueil
      </v-btn>
      <v-btn variant="text" size="large" @click="goBack" prepend-icon="mdi-arrow-left">
        Page précédente
      </v-btn>
    </div>
  </v-container>
</template>

<script setup>
function goBack() {
  if (window.history.length > 1) {
    window.history.back()
  } else {
    window.location.href = '/'
  }
}
</script>
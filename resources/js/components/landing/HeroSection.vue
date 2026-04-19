<template>
  <v-container fluid class="pa-0">

    <v-img :src="isDesktop ? bannerDesktop : bannerMobile" :height="isDesktop ? '80vh' : '60vh'" cover class="hero-img d-flex align-center" content-class="hero-image">

      <!-- OVERLAY -->
      <div class="hero-overlay d-flex align-center justify-center" :class="{ active: showOverlay }">

        <v-container class="text-center text-white hero-content">

          <h1 class="text-h3 text-md-h2 font-weight-bold mb-4">
            Crée ton jeu de société 🎲
          </h1>

          <p class="text-body-1 text-md-h6 mb-6 mx-auto hero-text">
            Imagine, construis et partage tes propres jeux.
          </p>

          <v-btn color="primary" size="large">
            Commencer
          </v-btn>

        </v-container>

      </div>

    </v-img>

  </v-container>
  <!-- FEATURES -->
  <v-container class="mt-15">

    <v-row>

      <v-col cols="12" md="4">
        <v-card class="pa-5 text-center">
          <v-icon size="40" color="primary">mdi-pencil</v-icon>
          <h3 class="mt-3">Créer</h3>
          <p>Conçois tes propres règles et cartes.</p>
        </v-card>
      </v-col>

      <v-col cols="12" md="4">
        <v-card class="pa-5 text-center">
          <v-icon size="40" color="primary">mdi-database</v-icon>
          <h3 class="mt-3">Explorer</h3>
          <p>Découvre des jeux existants.</p>
        </v-card>
      </v-col>

      <v-col cols="12" md="4">
        <v-card class="pa-5 text-center">
          <v-icon size="40" color="primary">mdi-share-variant</v-icon>
          <h3 class="mt-3">Partager</h3>
          <p>Publie tes créations facilement.</p>
        </v-card>
      </v-col>

    </v-row>

  </v-container>

  <!-- CTA -->
  <v-container class="text-center mt-15">
    <h2 class="mb-4">Prêt à créer ton jeu ?</h2>

    <v-btn color="primary" size="large">
      Rejoindre maintenant
    </v-btn>
  </v-container>

</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useResponsive } from '@/composables/useResponsive'

import bannerMobile from '@/assets/images/banner-mobile.jpg'
import bannerDesktop from '@/assets/images/banner-desktop.jpg'

const { isDesktop } = useResponsive()

const showOverlay = ref(false)

const handleScroll = () => {
  showOverlay.value = window.scrollY > 10
}

onMounted(() => {
  window.addEventListener('scroll', handleScroll)
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})
</script>

<style scoped>
.hero-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to bottom,
      rgba(0, 0, 0, 0),
      rgba(0, 0, 0, 0.75));

  opacity: 0;
  transition: opacity 0.25s ease;
}

.hero-overlay.active {
  opacity: 1;
}
.hero-image img {
  object-position: bottom;
}
.hero-content {
  position: absolute;
  bottom: 60px;
  /* 👈 clé ici */
  left: 50%;
  transform: translateX(-50%);

  text-align: center;
  color: white;
  max-width: 600px;
  padding: 0 20px;
}

/* mobile adjustment */
@media (max-width: 768px) {
  .hero-content {
    bottom: 40px;
  }
}
</style>
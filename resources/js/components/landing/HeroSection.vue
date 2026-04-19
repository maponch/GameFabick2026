<template>
  <v-card>
    <v-img :src="isDesktop ? bannerDesktop : bannerMobile" :height="isDesktop ? 600 : 300" cover
      class="d-flex align-center" 
      content-class="hero-image" />
  </v-card>

  <!-- HERO -->
  <v-container class="text-center mt-10">

    <v-row align="center" justify="center">

      <!-- TEXTE -->
      <v-col cols="12" md="6">
        <h1 class="text-h4 text-md-h2 font-weight-bold mb-4">
          Crée ton jeu de société 🎲
        </h1>

        <p class="text-body-1 text-md-h6 mb-6">
          Une plateforme pour imaginer, concevoir et partager tes jeux.
        </p>

        <v-btn color="primary" size="large">
          Commencer
        </v-btn>
      </v-col>

      <!-- IMAGE -->
      <v-col cols="12" md="6">
        <v-img :src="isDesktop
          ? 'https://picsum.photos/800/400'
          : 'https://picsum.photos/400/250'" :height="isDesktop ? 350 : 200" cover class="rounded-lg" />
      </v-col>

    </v-row>
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
import { useResponsive } from '@/composables/useResponsive'

//import des images
import bannerMobile from '@/assets/images/banner-mobile.jpg'
import bannerDesktop from '@/assets/images/banner-desktop.jpg'

const { isMobile, isDesktop } = useResponsive()
</script>


<style scoped>  

.hero-image img {
  object-position: bottom;
}
</style>
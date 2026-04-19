<template>
  <v-app>
    <AppHeader />

    <v-main>
      <HeroSection />
    </v-main>

    <AppFooter />
    <!-- <v-main>
      <TestDevs />
    </v-main> -->
  </v-app>
    
  
</template>
<script setup>
import AppHeader from './components/layout/AppHeader.vue'
import AppFooter from './components/layout/AppFooter.vue'
import HeroSection from './components/landing/HeroSection.vue'
import TestDevs from './components/TestDevs.vue'
</script>
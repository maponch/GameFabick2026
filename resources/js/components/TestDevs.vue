<template>
  <v-app>

    

    <!-- MAIN -->
    <v-main>


    </v-main>

    <!-- FOOTER -->
    <v-footer class="text-center mt-10">
      <v-container>
        <span>© 2026 GameFabrick - TFE</span>
      </v-container>
    </v-footer>

  </v-app>
</template>


<script setup>
  import {useResponsive} from '@/composables/useResponsive'

  const {isMobile, isDesktop} = useResponsive()
</script>

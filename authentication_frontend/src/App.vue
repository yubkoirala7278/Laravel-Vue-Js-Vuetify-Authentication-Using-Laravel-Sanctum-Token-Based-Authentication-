<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const isLoading = ref(false);
const router = useRouter();

// Show loader when navigation starts
router.beforeEach((to, from, next) => {
  isLoading.value = true;
  next();
});

// Hide loader when navigation is done
router.afterEach(() => {
  setTimeout(() => {
    isLoading.value = false;
  }, 500); // Small delay for smoother transition
});
</script>

<template>

  <main>
    <!-- Page Loader -->
    <v-overlay v-model="isLoading" class="loader-overlay" :scrim="false">
      <v-progress-circular indeterminate size="64" color="primary" />
    </v-overlay>

    <!-- The child routes will be injected here -->
    <router-view />
  </main>
</template>

<style scoped>
/* Page Loader Styles */
.loader-overlay {
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  background: rgba(255, 255, 255, 0.8);
}
</style>
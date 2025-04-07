<script setup>
import { useAuthStore } from "@/stores/auth"

// Access the authentication store
const authStore = useAuthStore();

// Methods
const logout = () => {
    authStore.logout();
};
</script>
<template>
    <v-btn class="mx-1" @click="logout" :loading="authStore.loading">
        Logout
    </v-btn>
</template>

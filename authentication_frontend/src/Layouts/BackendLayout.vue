<script setup>
import Dashboard from './Dashboard.vue'
</script>
<template>
    <v-app>
        <!-- Dashboard -->
        <Dashboard />
        <!-- Main Content Area -->
        <v-main>
            <main>
                <!-- Child routes will be injected here -->
                <router-view />
            </main>
        </v-main>
    </v-app>
</template>
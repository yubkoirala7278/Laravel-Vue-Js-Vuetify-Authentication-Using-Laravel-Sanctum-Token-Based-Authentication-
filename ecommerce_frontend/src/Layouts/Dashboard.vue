<script setup>
import { ref } from "vue";
import { useRoute } from "vue-router";
import { useAuthStore } from "@/stores/auth"

// Access the authentication store
const authStore = useAuthStore();

// Drawer state (open by default on larger screens)
const drawer = ref(true);

// Navigation items
const navItems = [
    { title: "Dashboard", icon: "mdi-home", route: "/admin/home" },
    { title: "Category", icon: "mdi-cart", route: "/admin/category" },
    { title: "SubCategory", icon: "mdi-tag-multiple", route: "/admin/sub_category" },
    { title: "Brands", icon: "mdi-store", route: "/admin/brands" },
    { title: "Product", icon: "mdi-storefront", route: "/admin/product" },
];

// Get current route
const route = useRoute();

// Check if the item is active
const isActive = (itemRoute) => {
    return route.path === itemRoute;
};

// Methods
const logout = () => {
    authStore.logout();
};
</script>
<template>
    <!-- App Bar with Background Color -->
    <v-app-bar app elevation="2" color="teal darken-3" class="px-4">
        <v-app-bar-nav-icon @click="drawer = !drawer">
            <v-icon color="white">mdi-menu</v-icon>
        </v-app-bar-nav-icon>
        <v-toolbar-title class="white--text font-weight-bold">
            E-Commerce Hub
        </v-toolbar-title>
        <v-spacer></v-spacer>
        <v-btn icon class="mx-1">
            <v-icon color="white">mdi-bell</v-icon>
        </v-btn>
        <v-btn icon class="mx-1" @click="logout">
            <v-icon color="white">mdi-logout</v-icon>
        </v-btn>
    </v-app-bar>

    <!-- Navigation Drawer -->
    <v-navigation-drawer v-model="drawer" app permanent color="teal darken-3">
        <v-list dense class="pt-4">
            <v-list-item v-for="item in navItems" :key="item.title" :to="item.route" :active="isActive(item.route)"
                class="d-flex align-center pa-2" active-class="active-item">
                <v-icon color="white" class="mr-3">{{ item.icon }}</v-icon>
                <span class="white--text text-subtitle-1">{{ item.title }}</span>
            </v-list-item>
        </v-list>
    </v-navigation-drawer>

</template>

<style scoped>
/* Active Item Styling */
.active-item {
    background-color: rgba(255, 255, 255, 0.2) !important;
    color: white !important;
}

.active-item .v-icon {
    color: white !important;
}

/* App Bar Enhancements */
.v-app-bar {
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
}

.v-toolbar-title {
    font-size: 1.8rem;
}

/* Navigation Drawer Enhancements */
.v-navigation-drawer {
    width: 240px;
}

.v-navigation-drawer .v-list-item {
    padding: 16px;
    font-weight: 500;
    transition: background-color 0.3s ease;
}

.v-navigation-drawer .v-list-item:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

/* Make the drawer content more consistent */
.v-list-item {
    border-radius: 8px;
}

/* Responsive Behavior: Make Drawer Toggleable on Smaller Screens */
@media (max-width: 768px) {
    .v-navigation-drawer {
        width: 200px;
    }

    .v-app-bar {
        font-size: 1rem;
    }
}
</style>
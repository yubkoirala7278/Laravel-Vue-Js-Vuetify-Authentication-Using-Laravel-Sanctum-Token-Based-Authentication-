<!-- src/views/auth/Login.vue -->
<script setup>
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()

const form = ref({
    email: '',
    password: '',
})
const loading = ref(false)
const errors = ref({
    email: null,
    password: null,
    general: null,
})

const handleLogin = async () => {
    errors.value = { email: null, password: null, general: null }
    loading.value = true

    try {
        await auth.login(form.value)
    } catch (err) {
        const e = err.errors || {}
        errors.value.email = e.email?.[0] || null
        errors.value.password = e.password?.[0] || null
        errors.value.general = err.message || 'Login failed'
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <v-container class="d-flex align-center justify-center" style="height: 100vh">
        <v-card class="pa-6" min-width="400">
            <v-card-title class="text-h5">Login</v-card-title>
            <v-form @submit.prevent="handleLogin" ref="formRef">
                <v-text-field v-model="form.email" label="Email" :error-messages="errors.email" type="email" required />
                <v-text-field v-model="form.password" label="Password" :error-messages="errors.password" type="password"
                    required />
                <v-btn type="submit" color="primary" :loading="loading" block>
                    Login
                </v-btn>
                <v-alert v-if="errors.general"
                    :type="errors.general === 'Please verify your email to login.' ? 'info' : 'error'" class="mt-4"
                    dense>
                    {{ errors.general }}
                </v-alert>
            </v-form>
            <v-row class="mt-4" justify="space-between">
                <v-col cols="auto">
                    <router-link 
                        to="/register" 
                        class="text-decoration-none text-caption primary--text font-weight-medium"
                    >
                        Register new account
                    </router-link>
                </v-col>
                <v-col cols="auto">
                    <router-link 
                        to="/forgot-password" 
                        class="text-decoration-none text-caption primary--text font-weight-medium"
                    >
                        Forgot password?
                    </router-link>
                </v-col>
            </v-row>
        </v-card>
    </v-container>
</template>
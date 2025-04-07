<!-- src/views/auth/Register.vue -->
<script setup>
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()

// Form data for registration
const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
})
const loading = ref(false)
const errors = ref({
    name: null,
    email: null,
    password: null,
    password_confirmation: null,
    general: null,
})

// Handle registration submission
const handleRegister = async () => {
    errors.value = { name: null, email: null, password: null, password_confirmation: null, general: null }
    loading.value = true

    try {
        await auth.register(form.value)
        // Redirect to EmailVerification.vue happens in auth.js
    } catch (err) {
        const e = err.errors || {}
        errors.value.name = e.name?.[0] || null
        errors.value.email = e.email?.[0] || null
        errors.value.password = e.password?.[0] || null
        errors.value.password_confirmation = e.password_confirmation?.[0] || null
        errors.value.general = err.message || 'Registration failed'
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <v-container class="d-flex align-center justify-center" style="height: 100vh">
        <v-card class="pa-6" min-width="400">
            <v-card-title class="text-h5">Register</v-card-title>
            <v-form @submit.prevent="handleRegister" ref="formRef">
                <v-text-field v-model="form.name" label="Name" :error-messages="errors.name" required />
                <v-text-field v-model="form.email" label="Email" :error-messages="errors.email" type="email" required />
                <v-text-field v-model="form.password" label="Password" :error-messages="errors.password" type="password"
                    required />
                <v-text-field v-model="form.password_confirmation" label="Confirm Password"
                    :error-messages="errors.password_confirmation" type="password" required />
                <v-btn type="submit" color="primary" :loading="loading" block>
                    Register
                </v-btn>
                <v-alert v-if="errors.general" type="error" class="mt-4" dense>
                    {{ errors.general }}
                </v-alert>
            </v-form>
        </v-card>
    </v-container>
</template>
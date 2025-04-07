<!-- src/views/auth/EmailVerification.vue -->
<script setup>
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRoute } from 'vue-router'

const auth = useAuthStore()
const route = useRoute()

const email = ref(route.query.email || '')
const loading = ref(false)
const message = ref('An email verification link has been sent to you.') // Adjusted message
const error = ref(null)

const resendVerification = async () => {
    if (!email.value) {
        error.value = 'Please provide your email address.'
        return
    }

    loading.value = true
    error.value = null

    try {
        const response = await auth.resendVerification(email.value)
        message.value = response.message
    } catch (err) {
        error.value = err.message || 'Failed to resend verification email.'
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <v-container class="d-flex align-center justify-center" style="height: 100vh">
        <v-card class="pa-6" min-width="400">
            <v-card-title class="text-h5">Email Verification Required</v-card-title>
            <v-card-text>
                <p>{{ message }}</p>
                <v-text-field v-model="email" label="Email" type="email" :error-messages="error" required />
            </v-card-text>
            <v-card-actions>
                <v-btn color="primary" :loading="loading" block @click="resendVerification">
                    Resend Verification Link
                </v-btn>
            </v-card-actions>
            <v-alert v-if="error" type="error" class="mt-4" dense>
                {{ error }}
            </v-alert>
        </v-card>
    </v-container>
</template>
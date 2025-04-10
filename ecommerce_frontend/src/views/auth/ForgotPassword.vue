<!-- src/views/auth/ForgotPassword.vue -->
<script setup>
import { ref, onUnmounted } from 'vue'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()

const email = ref('')
const loading = ref(false)
const message = ref('')
const error = ref(null)
const isButtonDisabled = ref(false) // Button starts enabled
const countdown = ref(0) // Countdown starts at 0 (no initial countdown)
let timer = null // Timer reference

// Start countdown timer after button click
const startCountdown = () => {
    isButtonDisabled.value = true
    countdown.value = 30

    timer = setInterval(() => {
        if (countdown.value > 0) {
            countdown.value--
        } else {
            clearInterval(timer)
            isButtonDisabled.value = false
        }
    }, 1000)
}

// Send reset link and start countdown
const sendResetLink = async () => {
    if (!email.value) {
        error.value = 'Email is required'
        return
    }

    loading.value = true
    message.value = ''
    error.value = null

    try {
        const response = await auth.sendPasswordResetLink(email.value)
        message.value = response.message // "Password reset link sent to your email"
        startCountdown() // Start countdown after successful send
    } catch (err) {
        error.value = err.message || 'Failed to send reset link'
    } finally {
        loading.value = false
    }
}

// Clean up timer on component unmount
onUnmounted(() => {
    if (timer) {
        clearInterval(timer)
    }
})
</script>

<template>
    <v-container fluid class="d-flex align-center justify-center"
        style="height: 100vh; background: linear-gradient(135deg, #e0eafc, #cfdef3);">
        <v-card class="pa-8 elevation-10" min-width="400" max-width="450" rounded="lg" color="white">
            <v-card-title class="text-h4 font-weight-bold primary--text text-center">
                Forgot Password
            </v-card-title>
            <v-card-subtitle class="text-center mt-2 grey--text">
                Enter your email to reset your password
            </v-card-subtitle>
            <v-form @submit.prevent="sendResetLink" class="mt-6">
                <v-text-field v-model="email" label="Email" type="email" prepend-inner-icon="mdi-email" outlined dense
                    color="primary" :error-messages="error" required />
                <v-btn type="submit" color="primary" :loading="loading" :disabled="isButtonDisabled" block large rounded
                    elevation="2" class="mt-6">
                    {{ isButtonDisabled ? `Send Reset Link (${countdown}s)` : 'Send Reset Link' }}
                </v-btn>
                <v-alert v-if="message" type="success" class="mt-6" dense outlined text>
                    {{ message }}
                </v-alert>
                <v-alert v-if="error" type="error" class="mt-6" dense outlined text>
                    {{ error }}
                </v-alert>
            </v-form>
            <router-link to="/login"
                class="text-decoration-none text-caption primary--text font-weight-medium mt-4 d-block text-center">
                Back to Login
            </router-link>
        </v-card>
    </v-container>
</template>

<style scoped>
a:hover {
    text-decoration: underline;
}
</style>
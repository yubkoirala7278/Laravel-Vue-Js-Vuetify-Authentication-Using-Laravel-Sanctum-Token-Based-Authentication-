<!-- src/views/auth/ResetPassword.vue -->
<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

const form = ref({
    password: '',
    password_confirmation: '',
})
const email = ref('')
const token = ref('')
const loading = ref(false)
const message = ref('')
const error = ref(null)

onMounted(() => {
    email.value = route.query.email || ''
    token.value = route.query.token || ''
    if (!token.value || !email.value) {
        error.value = 'Invalid reset link'
    }
})

const resetPassword = async () => {
    if (!token.value || !email.value) return

    loading.value = true
    message.value = ''
    error.value = null

    try {
        const response = await auth.resetPassword(token.value, form.value)
        message.value = response.message // "Password reset successfully"

        // Check email verification status
        await auth.fetchUser()
        if (auth.user?.email_verified_at) {
            setTimeout(() => router.push('/admin/home'), 2000) // Redirect after 2s
        } else {
            setTimeout(() => router.push({ path: '/auth/email-verification', query: { email: email.value } }), 2000)
        }
    } catch (err) {
        error.value = err.message || 'Failed to reset password'
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <v-container fluid class="d-flex align-center justify-center"
        style="height: 100vh; background: linear-gradient(135deg, #e0eafc, #cfdef3);">
        <v-card class="pa-8 elevation-10" min-width="400" max-width="450" rounded="lg" color="white">
            <v-card-title class="text-h4 font-weight-bold primary--text text-center">
                Reset Password
            </v-card-title>
            <v-card-subtitle class="text-center mt-2 grey--text">
                Enter your new password
            </v-card-subtitle>
            <v-form @submit.prevent="resetPassword" class="mt-6">
                <v-text-field v-model="form.password" label="New Password" type="password" prepend-inner-icon="mdi-lock"
                    outlined dense color="primary"
                    :error-messages="error && !form.password ? 'Password is required' : null" required />
                <v-text-field v-model="form.password_confirmation" label="Confirm Password" type="password"
                    prepend-inner-icon="mdi-lock-check" outlined dense color="primary"
                    :error-messages="error && !form.password_confirmation ? 'Confirmation is required' : null"
                    required />
                <v-btn type="submit" color="primary" :loading="loading" block large rounded elevation="2" class="mt-6"
                    :disabled="!token || !email">
                    Reset Password
                </v-btn>
                <v-alert v-if="message" type="success" class="mt-6" dense outlined text>
                    {{ message }}
                </v-alert>
                <v-alert v-if="error" type="error" class="mt-6" dense outlined text>
                    {{ error }}
                </v-alert>
            </v-form>
            <router-link to="/auth/login"
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
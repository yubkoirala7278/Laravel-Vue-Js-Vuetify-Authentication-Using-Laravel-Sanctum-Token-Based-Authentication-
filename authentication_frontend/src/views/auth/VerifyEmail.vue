<!-- src/views/auth/VerifyEmail.vue -->
<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from '@/axios'
import { useAuthStore } from '@/stores/auth'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

const loading = ref(true)
const message = ref('')
const error = ref(null)

const verifyEmail = async () => {
    try {
        const response = await axios.get(`/verify-email/${route.params.token}`)
        message.value = response.data.message

        if (response.data.status === 'success') {
            auth.token = response.data.access_token
            localStorage.setItem('token', auth.token)
            await auth.fetchUser()
            setTimeout(() => router.push('/admin/home'), 2000)
        }
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to verify email.'
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    verifyEmail()
})
</script>

<template>
    <v-container class="d-flex align-center justify-center" style="height: 100vh">
        <v-card class="pa-6" min-width="400">
            <v-card-title class="text-h5">Email Verification</v-card-title>
            <v-card-text>
                <v-progress-circular v-if="loading" indeterminate color="primary" class="mb-4" />
                <p v-if="message && !loading">{{ message }}</p>
                <v-alert v-if="error" type="error" dense>
                    {{ error }}
                </v-alert>
            </v-card-text>
        </v-card>
    </v-container>
</template>
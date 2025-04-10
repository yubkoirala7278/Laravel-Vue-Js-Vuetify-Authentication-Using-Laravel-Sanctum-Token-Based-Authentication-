// src/stores/auth.js
import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import axios from '@/axios'
import router from '@/router'

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null)
    const token = ref(localStorage.getItem('token') || null)
    const isAuthenticated = computed(() => !!token.value)

    // Login user and redirect
    const login = async (credentials) => {
        try {
            const response = await axios.post('/login', credentials);
            token.value = response.data.access_token
            localStorage.setItem('token', token.value)
            await fetchUser()
            router.push('/admin/home')
        } catch (error) {
            // Handle unverified email
            if (error.response.data.message === 'email_not_verified') {
                router.push({ path: '/email-verification', query: { email: credentials.email } })
                return;
            }
            // Only throw errors for validation or server issues, not email verification
            if (error.response?.status !== 403) {
                throw error.response?.data || { message: 'Login failed. Please try again.' }
            }
        }
    }

    // Register new user and redirect to email verification
    const register = async (data) => {
        try {
            const response = await axios.post('/register', data)
            // Store token from registration
            token.value = response.data.access_token
            localStorage.setItem('token', token.value)
            // Redirect to email verification page with email query
            router.push({ path: '/email-verification', query: { email: data.email } })
        } catch (error) {
            // Pass validation or server errors back to the component
            throw error.response?.data || { message: 'Registration failed. Please try again.' }
        }
    }

    // Fetch user data
    const fetchUser = async () => {
        try {
            const response = await axios.get('/user')
            user.value = response.data
        } catch (error) {
            logout()
        }
    }

    // Logout user and clear state
    const logout = async () => {
        try {
            await axios.post('/logout')
        } catch (error) {
            console.warn('Logout request failed, but continuing.')
        } finally {
            token.value = null
            user.value = null
            localStorage.removeItem('token')
            router.push('/login')
        }
    }

    // Resend verification email
    const resendVerification = async (email) => {
        try {
            const response = await axios.post('/resend-verification-email', { email })
            return response.data
        } catch (error) {
            throw error.response?.data || { message: 'Failed to resend verification email.' }
        }
    }

    // Send password reset link
    const sendPasswordResetLink = async (email) => {
        try {
            const response = await axios.post('/send-reset-password-email', { email })
            return response.data
        } catch (error) {
            throw error.response?.data || { message: 'Failed to send reset link' }
        }
    }

    // Reset password
    const resetPassword = async (token, data) => {
        try {
            const response = await axios.post(`/reset-password/${token}`, data)
            return response.data
        } catch (error) {
            throw error.response?.data || { message: 'Failed to reset password' }
        }
    }

    return {
        user,
        token,
        isAuthenticated,
        login,
        register,
        logout,
        fetchUser,
        resendVerification,
        sendPasswordResetLink,
        resetPassword
    }
})
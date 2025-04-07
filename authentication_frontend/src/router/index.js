// src/router/index.js
import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
    {
        path: '/',
        name: 'Frontend',
        component: () => import('@/layouts/FrontendLayout.vue'),
        children: [
            {
                path: '',
                name: 'Home',
                component: () => import('@/views/frontend/Home.vue'),
            },
        ],
    },
    {
        path: '/auth',
        name: 'Auth',
        component: () => import('@/layouts/AuthLayout.vue'),
        children: [
            {
                path: '/login',
                name: 'Login',
                component: () => import('@/views/auth/Login.vue'),
            },
            {
                path: '/email-verification',
                name: 'EmailVerification',
                component: () => import('@/views/auth/EmailVerification.vue'),
            },
            {
                path: '/verify-email/:token',
                name: 'VerifyEmail',
                component: () => import('@/views/auth/VerifyEmail.vue'),
            },
            {
                path: '/register',
                name: 'Register',
                component: () => import('@/views/auth/Register.vue'),
            },
            {
                path: '/forgot-password',
                name: 'ForgotPassword',
                component: () => import('@/views/auth/ForgotPassword.vue'),
            },
            {
                path: '/reset-password',
                name: 'ResetPassword',
                component: () => import('@/views/auth/ResetPassword.vue'),
            },
        ],
    },
    {
        path: '/admin',
        name: 'Admin',
        component: () => import('@/layouts/BackendLayout.vue'),
        meta: { requiresAuth: true },
        children: [
            {
                path: 'home',
                name: 'Dashboard',
                component: () => import('@/views/backend/Home.vue'),
            },
            {
                path: "category",
                name: "Category",
                component: () => import("@/views/backend/category/Category.vue"),
            },
        ],
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

router.beforeEach(async (to, from, next) => {
    const auth = useAuthStore()

    // List of auth-related route names to restrict for authenticated users
    const authRoutes = ['Login', 'EmailVerification', 'VerifyEmail', 'Register', 'ForgotPassword', 'ResetPassword']

    // Redirect authenticated users away from auth routes
    if (auth.isAuthenticated && authRoutes.includes(to.name)) {
        return next('/admin/home')
    }

    // Protect admin routes for unauthenticated users
    if (to.meta.requiresAuth && !auth.isAuthenticated) {
        return next('/login')
    }

    return next() // Proceed to the requested route
})

export default router
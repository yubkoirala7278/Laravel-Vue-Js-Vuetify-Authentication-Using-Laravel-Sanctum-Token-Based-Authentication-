import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

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
                path: 'category',
                name: 'Category',
                component: () => import('@/views/backend/category/Category.vue'),
            },
            {
                path: 'sub_category',
                name: 'SubCategory',
                component: () => import('@/views/backend/sub_category/SubCategory.vue'),
            },
            {
                path: 'brands',
                name: 'Brand',
                component: () => import('@/views/backend/brands/Brand.vue'),
            },
            {
                path: 'colors',
                name: 'Color',
                component: () => import('@/views/backend/colors/Color.vue'),
            },
            {
                path: 'product',
                name: 'Product',
                component: () => import('@/views/backend/products/Product.vue'),
            },
            {
                path: 'product/add',
                name: 'AddProduct',
                component: () => import('@/views/backend/products/AddProduct.vue'),
            },
            {
                path: 'product/edit/:slug',
                name: 'EditProduct',
                component: () => import('@/views/backend/products/EditProduct.vue'),
            },
        ],
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});


// Navigation guard
router.beforeEach(async (to, from, next) => {
    const auth = useAuthStore();

    // List of auth-related route names to restrict for authenticated users
    const authRoutes = [
        'Login',
        'EmailVerification',
        'VerifyEmail',
        'Register',
        'ForgotPassword',
        'ResetPassword',
    ];

    // If authenticated and trying to access an auth route, redirect to dashboard
    if (auth.isAuthenticated && authRoutes.includes(to.name)) {
        // Use next() with a path instead of returning to avoid overwriting history
        next('/admin/home');
    }
    // If unauthenticated and trying to access a protected route, redirect to login
    else if (to.meta.requiresAuth && !auth.isAuthenticated) {
        // Store the intended destination for post-login redirect (optional)
        sessionStorage.setItem('intendedRoute', to.fullPath);
        next('/login');
    }
    // Allow navigation to proceed normally
    else {
        next();
    }
});

export default router
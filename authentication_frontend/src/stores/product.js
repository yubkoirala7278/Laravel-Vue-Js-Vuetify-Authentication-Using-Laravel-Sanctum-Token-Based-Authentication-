import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '@/axios';
import { showToast } from '@/utils/toast'; // Import toast utility

export const useProductStore = defineStore('product', () => {
    // ========= State =========
    const products = ref([]); // List of products
    const currentProduct = ref(null); // Single product for view/edit
    const productErrors = ref({}); // Field-specific errors
    const loading = ref(false); // Loading state
    const pagination = ref({}); // Pagination metadata


    // ======= Fetch all products (GET /api/products) =======
    const fetchProducts = async (page = 1, perPage = 10, search = '', sortBy = 'created_at', sortDirection = 'desc', status = '') => {
        try {
            loading.value = true;

            const params = {
                page,
                per_page: perPage,
                sort_by: sortBy,
                sort_direction: sortDirection,
            };

            if (search) params.search = encodeURIComponent(search);
            if (status) params.status = encodeURIComponent(status);

            const response = await api.get('/products', { params });
            console.log('Fetch Products Response:', response.data);

            products.value = response.data.data || [];
            pagination.value = {
                total: response.data.meta?.total || 0,
                current_page: response.data.meta?.current_page || 1,
                last_page: response.data.meta?.last_page || 1,
                per_page: response.data.meta?.per_page || perPage,
            };

            return response.data;
        } catch (error) {
            console.error('Fetch Products Error:', error.response?.data || error.message);
            products.value = [];
            pagination.value = { total: 0, current_page: 1, last_page: 1, per_page: perPage };
            throw error;
        } finally {
            loading.value = false;
        }
    };

    // ======= Fetch a single product (GET /api/products/{slug}) =======
    const fetchProduct = async (slug) => {
        try {
            loading.value = true;
            const response = await api.get(`/products/${slug}`);
            currentProduct.value = response.data.data;
            return response;
        } catch (error) {
            console.error('Fetch Product Error:', error.response?.data || error.message);
            productErrors.value = error.response?.data?.errors || { general: ['Product not found'] };
            currentProduct.value = null;
            throw error;
        } finally {
            loading.value = false;
        }
    };

    // ======= Store a new product (POST /api/products) =======
    const storeProduct = async (formData) => {
        loading.value = true;
        productErrors.value = {};

        try {
            const response = await api.post('/products', formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });

            showToast.success('Product added successfully!');
        } catch (error) {
            const errorMsg = error.response?.data?.message || error.message;
            console.error('Store Product Error:', errorMsg);
            productErrors.value = error.response?.data?.errors || { general: [errorMsg] };
            throw error;
        } finally {
            loading.value = false;
        }
    };


    // ======= Update an existing product (PUT /api/products/{id}) =======
    const updateProduct = async (slug, formData) => {
        try {
            loading.value = true;
            productErrors.value = {};

            formData.append('_method', 'PUT'); // Now works because formData is a FormData instance

            const response = await api.post(`/products/${slug}`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });

            showToast.success('Product updated successfully!');
        } catch (error) {
            console.error('Update Product Error:', error.response?.data || error.message);
            productErrors.value = error.response?.data?.errors || { general: ['Failed to update product'] };
            throw error;
        } finally {
            loading.value = false;
        }
    };


    // ======= Delete a product (DELETE /api/products/{id}) =======
    const deleteProduct = async (slug) => {
        try {
            loading.value = true;
            await api.delete(`/products/${slug}`);
            products.value = products.value.filter(prod => prod.slug !== slug);
            if (currentProduct.value?.slug === slug) currentProduct.value = null;
            showToast.success('Product deleted successfully!'); // Use vue3-toastify
            return;
        } catch (error) {
            console.error('Delete Product Error:', error.response?.data || error.message);
            productErrors.value = error.response?.data?.errors || { general: ['Failed to delete product'] };
            throw error;
        } finally {
            loading.value = false;
        }
    };

    // ======= Reset errors =======
    const resetErrors = () => {
        productErrors.value = {};
    };

    // ======= Return state and actions =======
    return {
        products,
        currentProduct,
        productErrors,
        loading,
        pagination,
        fetchProducts,
        fetchProduct,
        storeProduct,
        updateProduct,
        deleteProduct,
        resetErrors,
    };
});
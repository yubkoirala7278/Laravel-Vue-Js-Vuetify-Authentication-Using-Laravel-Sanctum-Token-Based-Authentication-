import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '@/axios';
import { showToast } from '@/utils/toast'; // Import toast utility

export const useCategoryStore = defineStore('category', () => {
    // ========= State =========
    const categories = ref([]); // List of categories
    const currentCategory = ref(null); // Single category for view/edit
    const categoryErrors = ref({}); // Field-specific errors
    const loading = ref(false); // Loading state
    const pagination = ref({}); // Pagination metadata
    const activeCategories = ref([]); // New state for active categories

    // ======= Fetch all categories (GET /api/category) =======
    const fetchCategories = async (page = 1, perPage = 10, search = '', sortBy = 'updated_at', sortDirection = 'desc', status = '') => {
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

            const response = await api.get('/category', { params });
            console.log('Fetch Categories Response:', response.data);

            categories.value = response.data.data || [];
            pagination.value = {
                total: response.data.meta?.total || 0,
                current_page: response.data.meta?.current_page || 1,
                last_page: response.data.meta?.last_page || 1,
                per_page: response.data.meta?.per_page || perPage,
            };

            return response.data;
        } catch (error) {
            console.error('Fetch Categories Error:', error.response?.data || error.message);
            categories.value = [];
            pagination.value = { total: 0, current_page: 1, last_page: 1, per_page: perPage };
            throw error;
        } finally {
            loading.value = false;
        }
    };

    // ======= Fetch a single category (GET /api/category/{slug}) =======
    const fetchCategory = async (slug) => {
        try {
            loading.value = true;
            const response = await api.get(`/category/${slug}`);
            currentCategory.value = response.data.data;
            return response;
        } catch (error) {
            console.error('Fetch Category Error:', error.response?.data || error.message);
            categoryErrors.value = error.response?.data?.errors || { general: ['Category not found'] };
            currentCategory.value = null;
            throw error;
        } finally {
            loading.value = false;
        }
    };

    // ======= Store a new category (POST /api/category) =======
    const storeCategory = async (formData) => {
        try {
            loading.value = true;
            categoryErrors.value = {};

            const response = await api.post('/category', formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });

            const newCategory = response.data.data;
            categories.value.unshift(newCategory);
            showToast.success('Category added successfully!');
            return newCategory;
        } catch (error) {
            console.error('Store Category Error:', error.response?.data || error.message);
            categoryErrors.value = error.response?.data?.errors || { general: ['Failed to create category'] };
            throw error;
        } finally {
            loading.value = false;
        }
    };


    // ======= Update an existing category (PUT /api/category/{slug}) =======
    const updateCategory = async (slug, formData) => {
        try {
            loading.value = true;
            categoryErrors.value = {};

            formData.append('_method', 'PUT');

            const response = await api.post(`/category/${slug}`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });

            const updatedCategory = response.data.data;
            const index = categories.value.findIndex(cat => cat.slug === slug);
            if (index !== -1) categories.value[index] = updatedCategory;
            currentCategory.value = updatedCategory;
            return updatedCategory;
        } catch (error) {
            console.error('Update Category Error:', error.response?.data || error.message);
            categoryErrors.value = error.response?.data?.errors || { general: ['Failed to update category'] };
            throw error;
        } finally {
            loading.value = false;
        }
    };


    // ======= Delete a category (DELETE /api/category/{slug}) =======
    const deleteCategory = async (slug) => {
        try {
            loading.value = true;
            await api.delete(`/category/${slug}`);
            categories.value = categories.value.filter(cat => cat.slug !== slug);
            if (currentCategory.value?.slug === slug) currentCategory.value = null;
            return slug;
        } catch (error) {
            console.error('Delete Category Error:', error.response?.data || error.message);
            categoryErrors.value = error.response?.data?.errors || { general: ['Failed to delete category'] };
            throw error;
        } finally {
            loading.value = false;
        }
    };

    // ======= Delete multiple categories (DELETE /api/category/multiple) =======
    const deleteMultipleCategories = async (slugs) => {
        try {
            loading.value = true;
            await api.delete('/category/multiple', { data: { slugs } });
            categories.value = categories.value.filter(cat => !slugs.includes(cat.slug));
            if (currentCategory.value && slugs.includes(currentCategory.value.slug)) {
                currentCategory.value = null;
            }
            return slugs;
        } catch (error) {
            console.error('Delete Multiple Categories Error:', error.response?.data || error.message);
            categoryErrors.value = error.response?.data?.errors || { general: ['Failed to delete categories'] };
            throw error;
        } finally {
            loading.value = false;
        }
    };

    // ======= Reset errors =======
    const resetErrors = () => {
        categoryErrors.value = {};
    };

    // Fetch active categories (GET /api/categories/active)
    const fetchActiveCategories = async () => {
        try {
            loading.value = true;
            const response = await api.get('/category/active');
            activeCategories.value = response.data.data || [];
        } catch (error) {
            console.error('Fetch Active Categories Error:', error.response?.data || error.message);
            activeCategories.value = [];
            throw error;
        } finally {
            loading.value = false;
        }
    };

    // ======= Return state and actions =======
    return {
        categories,
        currentCategory,
        categoryErrors,
        loading,
        pagination,
        fetchCategories,
        fetchCategory,
        storeCategory,
        updateCategory,
        deleteCategory,
        deleteMultipleCategories,
        resetErrors,
        fetchActiveCategories,
        activeCategories
    };
});
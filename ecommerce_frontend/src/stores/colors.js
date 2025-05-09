import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '@/axios';
import { showToast } from '@/utils/toast';

export const useColorStore = defineStore('color', () => {
    // ========= State =========
    const colors = ref([]); // List of colors
    const currentColor = ref(null); // Single color for view/edit
    const colorErrors = ref({}); // Field-specific errors
    const loading = ref(false); // Loading state
    const pagination = ref({}); // Pagination metadata
    const activeColors = ref([]); // Active colors

    // ======= Fetch all colors (GET /api/colors) =======
    const fetchColors = async (
        page = 1,
        perPage = 10,
        search = '',
        sortBy = 'created_at',
        sortDirection = 'desc',
        status = ''
    ) => {
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

            const response = await api.get('/colors', { params });
            console.log('Fetch Colors Response:', response.data);

            colors.value = response.data.data || [];
            pagination.value = {
                total: response.data.meta?.total || 0,
                current_page: response.data.meta?.current_page || 1,
                last_page: response.data.meta?.last_page || 1,
                per_page: response.data.meta?.per_page || perPage,
            };

            return response.data;
        } catch (error) {
            console.error('Fetch Colors Error:', error.response?.data || error.message);
            colors.value = [];
            pagination.value = { total: 0, current_page: 1, last_page: 1, per_page: perPage };
            throw error;
        } finally {
            loading.value = false;
        }
    };

    // ======= Fetch a single color (GET /api/colors/{slug}) =======
    const fetchColor = async (slug) => {
        try {
            loading.value = true;
            const response = await api.get(`/colors/${slug}`);
            currentColor.value = response.data.data;
            return response;
        } catch (error) {
            console.error('Fetch Color Error:', error.response?.data || error.message);
            colorErrors.value = error.response?.data?.errors || {
                general: ['Color not found'],
            };
            currentColor.value = null;
            throw error;
        } finally {
            loading.value = false;
        }
    };

    // ======= Store a new color (POST /api/colors) =======
    const storeColor = async (colorData) => {
        try {
            loading.value = true;
            colorErrors.value = {};

            const response = await api.post('/colors', colorData);

            const newColor = response.data.data;
            colors.value.unshift(newColor);
            showToast.success('Color added successfully!');
            return newColor;
        } catch (error) {
            console.error('Store Color Error:', error.response?.data || error.message);
            colorErrors.value = error.response?.data?.errors || {
                general: ['Failed to create color'],
            };
            throw error;
        } finally {
            loading.value = false;
        }
    };

    // ======= Update an existing color (PUT /api/colors/{slug}) =======
    const updateColor = async (slug, colorData) => {
        try {
            loading.value = true;
            colorErrors.value = {};

            const response = await api.put(`/colors/${slug}`, colorData);

            const updatedColor = response.data.data;
            const index = colors.value.findIndex((color) => color.slug === slug);
            if (index !== -1) colors.value[index] = updatedColor;
            currentColor.value = updatedColor;
            showToast.success('Color updated successfully!');
            return updatedColor;
        } catch (error) {
            console.error('Update Color Error:', error.response?.data || error.message);
            colorErrors.value = error.response?.data?.errors || {
                general: ['Failed to update color'],
            };
            throw error;
        } finally {
            loading.value = false;
        }
    };

    // ======= Delete a color (DELETE /api/colors/{slug}) =======
    const deleteColor = async (slug) => {
        try {
            loading.value = true;
            await api.delete(`/colors/${slug}`);
            colors.value = colors.value.filter((color) => color.slug !== slug);
            if (currentColor.value?.slug === slug) currentColor.value = null;
            showToast.success('Color deleted successfully!');
            return slug;
        } catch (error) {
            console.error('Delete Color Error:', error.response?.data || error.message);
            colorErrors.value = error.response?.data?.errors || {
                general: ['Failed to delete color'],
            };
            throw error;
        } finally {
            loading.value = false;
        }
    };

    // ======= Fetch active colors (GET /api/colors/active) =======
    const fetchActiveColors = async () => {
        try {
            loading.value = true;
            const response = await api.get('/colors/active');
            activeColors.value = response.data.data || [];
        } catch (error) {
            console.error('Fetch Active Colors Error:', error.response?.data || error.message);
            activeColors.value = [];
            throw error;
        } finally {
            loading.value = false;
        }
    };

    // ======= Reset errors =======
    const resetErrors = () => {
        colorErrors.value = {};
    };

    // ======= Return state and actions =======
    return {
        colors,
        currentColor,
        colorErrors,
        loading,
        pagination,
        activeColors,
        fetchColors,
        fetchColor,
        storeColor,
        updateColor,
        deleteColor,
        fetchActiveColors,
        resetErrors,
    };
});
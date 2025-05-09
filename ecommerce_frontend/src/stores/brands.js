import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '@/axios';
import { showToast } from '@/utils/toast';

export const useBrandStore = defineStore('brand', () => {
    // ========= State =========
    const brands = ref([]); // List of brands
    const currentBrand = ref(null); // Single brand for view/edit
    const brandErrors = ref({}); // Field-specific errors
    const loading = ref(false); // Loading state
    const pagination = ref({}); // Pagination metadata
    const activeBrands = ref([]); // Active brands

    // ======= Fetch all brands (GET /api/brands) =======
    const fetchBrands = async (
        page = 1,
        perPage = 10,
        search = '',
        sortBy = 'updated_at',
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

            const response = await api.get('/brands', { params });
            console.log('Fetch Brands Response:', response.data);

            brands.value = response.data.data || [];
            pagination.value = {
                total: response.data.meta?.total || 0,
                current_page: response.data.meta?.current_page || 1,
                last_page: response.data.meta?.last_page || 1,
                per_page: response.data.meta?.per_page || perPage,
            };

            return response.data;
        } catch (error) {
            console.error('Fetch Brands Error:', error.response?.data || error.message);
            brands.value = [];
            pagination.value = { total: 0, current_page: 1, last_page: 1, per_page: perPage };
            throw error;
        } finally {
            loading.value = false;
        }
    };

    // ======= Fetch a single brand (GET /api/brands/{slug}) =======
    const fetchBrand = async (slug) => {
        try {
            loading.value = true;
            const response = await api.get(`/brands/${slug}`);
            currentBrand.value = response.data.data;
            return response;
        } catch (error) {
            console.error('Fetch Brand Error:', error.response?.data || error.message);
            brandErrors.value = error.response?.data?.errors || {
                general: ['Brand not found'],
            };
            currentBrand.value = null;
            throw error;
        } finally {
            loading.value = false;
        }
    };

    // ======= Store a new brand (POST /api/brands) =======
    const storeBrand = async (brandData) => {
        try {
            loading.value = true;
            brandErrors.value = {};

            const response = await api.post('/brands', brandData);

            const newBrand = response.data.data;
            brands.value.unshift(newBrand);
            showToast.success('Brand added successfully!');
            return newBrand;
        } catch (error) {
            console.error('Store Brand Error:', error.response?.data || error.message);
            brandErrors.value = error.response?.data?.errors || {
                general: ['Failed to create brand'],
            };
            throw error;
        } finally {
            loading.value = false;
        }
    };

    // ======= Update an existing brand (PUT /api/brands/{slug}) =======
    const updateBrand = async (slug, brandData) => {
        try {
            loading.value = true;
            brandErrors.value = {};

            const response = await api.put(`/brands/${slug}`, brandData);

            const updatedBrand = response.data.data;
            const index = brands.value.findIndex((brand) => brand.slug === slug);
            if (index !== -1) brands.value[index] = updatedBrand;
            currentBrand.value = updatedBrand;
            showToast.success('Brand updated successfully!');
            return updatedBrand;
        } catch (error) {
            console.error('Update Brand Error:', error.response?.data || error.message);
            brandErrors.value = error.response?.data?.errors || {
                general: ['Failed to update brand'],
            };
            throw error;
        } finally {
            loading.value = false;
        }
    };

    // ======= Delete a brand (DELETE /api/brands/{slug}) =======
    const deleteBrand = async (slug) => {
        try {
            loading.value = true;
            await api.delete(`/brands/${slug}`);
            brands.value = brands.value.filter((brand) => brand.slug !== slug);
            if (currentBrand.value?.slug === slug) currentBrand.value = null;
            showToast.success('Brand deleted successfully!');
            return slug;
        } catch (error) {
            console.error('Delete Brand Error:', error.response?.data || error.message);
            brandErrors.value = error.response?.data?.errors || {
                general: ['Failed to delete brand'],
            };
            throw error;
        } finally {
            loading.value = false;
        }
    };

    // ======= Fetch active brands (GET /api/brands/active) =======
    const fetchActiveBrands = async () => {
        try {
            loading.value = true;
            const response = await api.get('/brands/active');
            activeBrands.value = response.data.data || [];
        } catch (error) {
            console.error('Fetch Active Brands Error:', error.response?.data || error.message);
            activeBrands.value = [];
            throw error;
        } finally {
            loading.value = false;
        }
    };


     // ======= Delete multiple brands (DELETE /api/brands/multiple) =======
     const deleteMultipleBrands = async (slugs) => {
        try {
            loading.value = true;
            await api.delete('/brands/multiple', { data: { slugs } });
            brands.value = brands.value.filter(brand => !slugs.includes(brand.slug));
            if (currentBrand.value && slugs.includes(currentBrand.value.slug)) {
                currentBrand.value = null;
            }
            return slugs;
        } catch (error) {
            console.error('Delete Multiple Brands Error:', error.response?.data || error.message);
            brandErrors.value = error.response?.data?.errors || { general: ['Failed to delete brands'] };
            throw error;
        } finally {
            loading.value = false;
        }
    };

    // ======= Reset errors =======
    const resetErrors = () => {
        brandErrors.value = {};
    };

    // ======= Return state and actions =======
    return {
        brands,
        currentBrand,
        brandErrors,
        loading,
        pagination,
        activeBrands,
        fetchBrands,
        fetchBrand,
        storeBrand,
        updateBrand,
        deleteBrand,
        fetchActiveBrands,
        deleteMultipleBrands,
        resetErrors,
    };
});
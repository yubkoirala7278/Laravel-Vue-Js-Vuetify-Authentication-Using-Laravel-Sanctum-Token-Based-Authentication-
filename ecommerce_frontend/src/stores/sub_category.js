import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '@/axios';
import { showToast } from '@/utils/toast';

export const useSubCategoryStore = defineStore('subCategory', () => {
    // ========= State =========
    const subCategories = ref([]); // List of subcategories
    const currentSubCategory = ref(null); // Single subcategory for view/edit
    const subCategoryErrors = ref({}); // Field-specific errors
    const loading = ref(false); // Loading state
    const pagination = ref({}); // Pagination metadata
    const activeSubCategories = ref([]); // Active subcategories

    // ======= Fetch all subcategories (GET /api/sub_category) =======
    const fetchSubCategories = async (
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

            const response = await api.get('/sub_category', { params });
            console.log('Fetch SubCategories Response:', response.data);

            subCategories.value = response.data.data || [];
            pagination.value = {
                total: response.data.meta?.total || 0,
                current_page: response.data.meta?.current_page || 1,
                last_page: response.data.meta?.last_page || 1,
                per_page: response.data.meta?.per_page || perPage,
            };

            return response.data;
        } catch (error) {
            console.error('Fetch SubCategories Error:', error.response?.data || error.message);
            subCategories.value = [];
            pagination.value = { total: 0, current_page: 1, last_page: 1, per_page: perPage };
            throw error;
        } finally {
            loading.value = false;
        }
    };

    // ======= Fetch a single subcategory (GET /api/sub_category/{slug}) =======
    const fetchSubCategory = async (slug) => {
        try {
            loading.value = true;
            const response = await api.get(`/sub_category/${slug}`);
            currentSubCategory.value = response.data.data;
            return response;
        } catch (error) {
            console.error('Fetch SubCategory Error:', error.response?.data || error.message);
            subCategoryErrors.value = error.response?.data?.errors || {
                general: ['Subcategory not found'],
            };
            currentSubCategory.value = null;
            throw error;
        } finally {
            loading.value = false;
        }
    };

    // ======= Store a new subcategory (POST /api/sub_category) =======
    const storeSubCategory = async (subCategoryData) => {
        try {
            loading.value = true;
            subCategoryErrors.value = {};

            const response = await api.post('/sub_category', subCategoryData);

            const newSubCategory = response.data.data;
            subCategories.value.unshift(newSubCategory);
            showToast.success('Subcategory added successfully!');
            return newSubCategory;
        } catch (error) {
            console.error('Store SubCategory Error:', error.response?.data || error.message);
            subCategoryErrors.value = error.response?.data?.errors || {
                general: ['Failed to create subcategory'],
            };
            throw error;
        } finally {
            loading.value = false;
        }
    };

    // ======= Update an existing subcategory (PUT /api/sub_category/{slug}) =======
    const updateSubCategory = async (slug, subCategoryData) => {
        try {
            loading.value = true;
            subCategoryErrors.value = {};

            const response = await api.put(`/sub_category/${slug}`, subCategoryData);

            const updatedSubCategory = response.data.data;
            const index = subCategories.value.findIndex((subCat) => subCat.slug === slug);
            if (index !== -1) subCategories.value[index] = updatedSubCategory;
            currentSubCategory.value = updatedSubCategory;
            showToast.success('Subcategory updated successfully!');
            return updatedSubCategory;
        } catch (error) {
            console.error('Update SubCategory Error:', error.response?.data || error.message);
            subCategoryErrors.value = error.response?.data?.errors || {
                general: ['Failed to update subcategory'],
            };
            throw error;
        } finally {
            loading.value = false;
        }
    };

    // ======= Delete a subcategory (DELETE /api/sub_category/{slug}) =======
    const deleteSubCategory = async (slug) => {
        try {
            loading.value = true;
            await api.delete(`/sub_category/${slug}`);
            subCategories.value = subCategories.value.filter((subCat) => subCat.slug !== slug);
            if (currentSubCategory.value?.slug === slug) currentSubCategory.value = null;
            showToast.success('Subcategory deleted successfully!');
            return slug;
        } catch (error) {
            console.error('Delete SubCategory Error:', error.response?.data || error.message);
            subCategoryErrors.value = error.response?.data?.errors || {
                general: ['Failed to delete subcategory'],
            };
            throw error;
        } finally {
            loading.value = false;
        }
    };

    // ======= Fetch active subcategories (GET /api/sub_category/active) =======
    const fetchActiveSubCategories = async (categoryId = null) => {
        try {
            loading.value = true;
            const endpoint = categoryId
                ? `/sub_category/active?category_id=${encodeURIComponent(categoryId)}`
                : `/sub_category/active`;

            const response = await api.get(endpoint);
            activeSubCategories.value = response.data.data || [];
        } catch (error) {
            console.error('Fetch Active SubCategories Error:', error.response?.data || error.message);
            activeSubCategories.value = [];
            throw error;
        } finally {
            loading.value = false;
        }
    };

    const clearSubCategories = async () => {
        activeSubCategories.value = [];
    };

    // ======= Reset errors =======
    const resetErrors = () => {
        subCategoryErrors.value = {};
    };

    // ======= Return state and actions =======
    return {
        subCategories,
        currentSubCategory,
        subCategoryErrors,
        loading,
        pagination,
        activeSubCategories,
        fetchSubCategories,
        fetchSubCategory,
        storeSubCategory,
        updateSubCategory,
        deleteSubCategory,
        fetchActiveSubCategories,
        clearSubCategories,
        resetErrors,
    };
});
<script setup>
import { ref, watch, onMounted } from 'vue';
import { useProductStore } from '@/stores/product';
import { useCategoryStore } from '@/stores/category';
import { useRouter } from 'vue-router';
import { useSubCategoryStore } from '@/stores/sub_category'; // New store
import { useBrandStore } from '@/stores/brands'; // New store
import { useColorStore } from '@/stores/colors'; // New store

// Pinia stores and router
const productStore = useProductStore();
const categoryStore = useCategoryStore();
const subCategoryStore = useSubCategoryStore();
const brandStore = useBrandStore();
const colorStore = useColorStore();
const router = useRouter();

// Form data
const form = ref({
    name: '',
    description: '',
    price: '',
    compare_price: '',
    is_featured: 'No',
    status: 'active',
    category_id: '',
    sub_category_id: '',
    brand_id: '',
    color_id: '',
    image: null,
});

// Validation rules
const rules = {
    name: [
        v => !!v || 'Product name is required',
        v => (v && v.length <= 255) || 'Name must be less than 255 characters',
    ],
    description: [
        v => !!v || 'Description is required',
        v => (v && v.length >= 10) || 'Description must be at least 10 characters',
    ],
    price: [
        v => !!v || 'Price is required',
        v => (!isNaN(v) && v >= 0) || 'Price must be a positive number',
    ],
    compare_price: [
        v => (!v || (!isNaN(v) && Number(v) > Number(form.value.price)) || 'Compare price must be greater than price'),],
    is_featured: [v => !!v || 'Featured status is required'],
    status: [v => !!v || 'Status is required'],
    category_id: [v => !!v || 'Category is required'],
    sub_category_id: [v => !v || subCategoryStore.activeSubCategories.length > 0 || 'No subcategories available'],
    brand_id: [v => !v || brandStore.activeBrands.length > 0 || 'No brands available'],
    color_id: [v => !v || colorStore.activeColors.length > 0 || 'No colors available'],
    image: [v => !!v || 'Image is required'],
};

// Form reference for validation
const formRef = ref(null);

// Error messages from backend
const backendErrors = ref({});

// Fetch active categories on mount
onMounted(async () => {
    try {
        await categoryStore.fetchActiveCategories();
        await brandStore.fetchActiveBrands();
        await colorStore.fetchActiveColors();
    } catch (error) {
        console.error('Failed to fetch active categories:', error);
    }
});

// Watch category_id to fetch related subcategories, brands, and colors
watch(
    () => form.value.category_id,
    async (newCategoryId) => {
        if (!newCategoryId) {
            subCategoryStore.clearSubCategories();
            form.value.sub_category_id = '';
            return;
        }
        try {
            await Promise.all([
                subCategoryStore.fetchActiveSubCategories(newCategoryId),
            ]);
        } catch (error) {
            console.error('Failed to fetch related data:', error);
        }
    }
);

// Handle file input
const onFileChange = (event) => {
    form.value.image = event.target.files[0];
};

// Submit form
const submitForm = async () => {
    const { valid } = await formRef.value.validate();
    if (!valid) return;

    try {
        backendErrors.value = {}; // Clear previous errors
        const formData = new FormData();
        Object.entries(form.value).forEach(([key, value]) => {
            if (value !== null && value !== '') {
                formData.append(key, value);
            }
        });
        await productStore.storeProduct(formData);
        router.push({ name: 'Product' });
    } catch (error) {
        backendErrors.value = error.response?.data?.errors || { general: ['Failed to add product'] };
    }
};

// Reset form
const resetForm = () => {
    form.value = {
        name: '',
        description: '',
        price: '',
        compare_price: '',
        is_featured: 'No',
        status: 'active',
        category_id: '',
        sub_category_id: '',
        brand_id: '',
        color_id: '',
        image: null,
    };
    backendErrors.value = {};
    formRef.value.resetValidation();
};
</script>

<template>
    <v-container fluid class="mt-3">
        <v-row justify="center">
            <v-col cols="12">
                <v-card class="pa-6 rounded-xl elevation-4">
                    <v-card-title class="text-h5 font-weight-bold teal-darken-2">
                        <v-icon left size="large" class="mr-2">mdi-package-variant-closed</v-icon>
                        Add New Product
                    </v-card-title>
                    <v-card-text>
                        <!-- General error from backend -->
                        <v-alert v-if="backendErrors.general" type="error" variant="tonal" class="mb-4 rounded-lg">
                            {{ backendErrors.general[0] }}
                        </v-alert>

                        <v-form ref="formRef" @submit.prevent="submitForm">
                            <!-- Product Name -->
                            <v-text-field v-model="form.name" label="Product Name" prepend-inner-icon="mdi-label"
                                variant="outlined" :rules="rules.name" :error-messages="backendErrors.name"
                                class="rounded-lg mb-4" bg-color="white" />

                            <!-- Product Description -->
                            <v-textarea v-model="form.description" label="Description" prepend-inner-icon="mdi-text"
                                variant="outlined" :rules="rules.description"
                                :error-messages="backendErrors.description" class="rounded-lg mb-4" bg-color="white" />

                            <v-row>
                                <v-col cols="12" sm="6">
                                    <!-- Product Price -->
                                    <v-text-field v-model="form.price" label="Price"
                                        prepend-inner-icon="mdi-currency-usd" variant="outlined" type="number"
                                        :rules="rules.price" :error-messages="backendErrors.price"
                                        class="rounded-lg mb-4" bg-color="white" />
                                </v-col>

                                <v-col cols="12" sm="6">
                                    <!-- Product Compared Price -->
                                    <v-text-field v-model="form.compare_price" label="Compare Price"
                                        prepend-inner-icon="mdi-currency-usd" variant="outlined" type="number"
                                        :rules="rules.compare_price" :error-messages="backendErrors.compare_price"
                                        class="rounded-lg mb-4" bg-color="white" />
                                </v-col>
                            </v-row>

                            <!-- Product Is Featured Yes/No -->
                            <v-select v-model="form.is_featured" :items="['Yes', 'No']" label="Is Featured"
                                prepend-inner-icon="mdi-star" variant="outlined" :rules="rules.is_featured"
                                :error-messages="backendErrors.is_featured" class="rounded-lg mb-4" bg-color="white" />

                            <!-- Product Status -->
                            <v-select v-model="form.status" :items="['active', 'inactive']" label="Status"
                                prepend-inner-icon="mdi-toggle-switch" variant="outlined" :rules="rules.status"
                                :error-messages="backendErrors.status" class="rounded-lg mb-4" bg-color="white" />

                            <!-- Product Select Category -->
                            <v-select label="Category" v-model="form.category_id"
                                :items="categoryStore.activeCategories" prepend-inner-icon="mdi-shape"
                                variant="outlined" :rules="rules.category_id"
                                :error-messages="backendErrors.category_id" class="rounded-lg mb-4"
                                :loading="categoryStore.loading" />

                            <!-- Product Select Subcategory -->
                            <v-select label="Subcategory" v-model="form.sub_category_id"
                                :items="subCategoryStore.activeSubCategories" item-value="id" item-title="name"
                                prepend-inner-icon="mdi-shape-outline" variant="outlined" :rules="rules.sub_category_id"
                                :error-messages="backendErrors.sub_category_id" class="rounded-lg mb-4"
                                :loading="subCategoryStore.loading"
                                :disabled="!form.category_id || subCategoryStore.activeSubCategories.length === 0" />

                            <!-- Product Select Brand -->
                            <v-select label="Brand" v-model="form.brand_id" :items="brandStore.activeBrands"
                                item-value="id" item-title="name" prepend-inner-icon="mdi-tag" variant="outlined"
                                :rules="rules.brand_id" :error-messages="backendErrors.brand_id" class="rounded-lg mb-4"
                                :loading="brandStore.loading" :disabled="brandStore.activeBrands.length === 0" />

                            <!-- Product Select Color -->
                            <v-select label="Color" v-model="form.color_id" :items="colorStore.activeColors"
                                item-value="id" item-title="name" prepend-inner-icon="mdi-palette" variant="outlined"
                                :rules="rules.color_id" :error-messages="backendErrors.color_id" class="rounded-lg mb-4"
                                :loading="colorStore.loading" :disabled="colorStore.activeColors.length === 0" />

                            <!-- Product Image -->
                            <v-file-input v-model="form.image" label="Product Image" prepend-inner-icon="mdi-camera"
                                variant="outlined" accept="image/jpeg,image/png,image/jpg,image/webp"
                                :rules="rules.image" :error-messages="backendErrors.image" class="rounded-lg mb-4"
                                bg-color="white" @change="onFileChange" />

                            <v-row justify="end">
                                <!-- Reset Form -->
                                <v-btn color="grey-darken-1" variant="outlined" class="rounded-lg mr-4"
                                    @click="resetForm">
                                    <v-icon left class="mr-2">mdi-undo</v-icon>
                                    Reset
                                </v-btn>
                                <!-- Submit Form -->
                                <v-btn color="teal-darken-2" variant="flat" class="rounded-lg" type="submit"
                                    :loading="productStore.loading">
                                    <v-icon left class="mr-2">mdi-content-save</v-icon>
                                    Save Product
                                </v-btn>
                            </v-row>
                        </v-form>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>
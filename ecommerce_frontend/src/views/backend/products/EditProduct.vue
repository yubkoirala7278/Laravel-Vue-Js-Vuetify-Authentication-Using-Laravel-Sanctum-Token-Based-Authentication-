<script setup>
import { ref, onMounted } from 'vue';
import { useProductStore } from '@/stores/product'; // Adjust path to your store
import { useCategoryStore } from '@/stores/category'; // Adjust path
import { useRouter, useRoute } from 'vue-router';

// Pinia stores and router
const productStore = useProductStore();
const categoryStore = useCategoryStore();
const router = useRouter();
const route = useRoute();

// Form data
const form = ref({
    name: '',
    description: '',
    price: '',
    compare_price: '',
    is_featured: 'No',
    status: 'active',
    category_id: '',
    image: null,
});

// Validation rules (same as AddProduct.vue)
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
        v => (!v || (!isNaN(v) && Number(v) > Number(form.value.price)) || 'Compare price must be greater than price')],
    is_featured: [v => !!v || 'Featured status is required'],
    status: [v => !!v || 'Status is required'],
    category_id: [v => !!v || 'Category is required'],
    image: [v => !form.value.image || !!v || 'Image is required'], // Optional for updates
};

// Form reference for validation
const formRef = ref(null);

// Error messages from backend
const backendErrors = ref({});

// Fetch product and categories on mount
onMounted(async () => {
    try {
        // Fetch active categories
        await categoryStore.fetchActiveCategories();

        // Fetch product details using slug from route
        const slug = route.params.slug;
        await productStore.fetchProduct(slug);

        // Populate form with existing product data
        const product = productStore.currentProduct;
        if (product) {
            form.value = {
                name: product.name,
                description: product.description,
                price: product.price,
                compare_price: product.compare_price || '',
                is_featured: product.is_featured,
                status: product.status,
                category_id: product.category_id,
                image: null, // Image is not pre-filled; user can upload a new one
            };
        }
    } catch (error) {
        console.error('Failed to load data:', error);
    }
});

// Handle file input
const onFileChange = (event) => {
    form.value.image = event.target.files[0];
};

// Submit form to update product
const submitForm = async () => {
    const { valid } = await formRef.value.validate();
    if (!valid) return;

    try {
        backendErrors.value = {}; // Clear previous errors
        const slug = route.params.slug;

        // Create FormData instance
        const formData = new FormData();
        formData.append('name', form.value.name);
        formData.append('description', form.value.description);
        formData.append('price', form.value.price);
        if (form.value.compare_price) formData.append('compare_price', form.value.compare_price);
        formData.append('is_featured', form.value.is_featured);
        formData.append('status', form.value.status);
        formData.append('category_id', form.value.category_id);
        if (form.value.image) formData.append('image', form.value.image); // Append image only if provided

        await productStore.updateProduct(slug, formData);
        router.push({ name: 'Product' }); // Redirect to product list page
    } catch (error) {
        backendErrors.value = error.response?.data?.errors || { general: ['Failed to update product'] };
    }
};

// Reset form to original product data
const resetForm = () => {
    const product = productStore.currentProduct;
    if (product) {
        form.value = {
            name: product.name,
            description: product.description,
            price: product.price,
            compare_price: product.compare_price || '',
            is_featured: product.is_featured,
            status: product.status,
            category_id: product.category_id,
            image: null,
        };
    }
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
                        Edit Product
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
                                    Update Product
                                </v-btn>
                            </v-row>
                        </v-form>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
</template>
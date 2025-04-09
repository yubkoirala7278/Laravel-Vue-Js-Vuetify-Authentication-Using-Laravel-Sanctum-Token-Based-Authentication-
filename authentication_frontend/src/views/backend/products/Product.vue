<script setup>
import { ref, onMounted, watch } from 'vue';
import { useProductStore } from '@/stores/product'; // Adjust the path to your store
import debounce from 'lodash/debounce';
import { headers } from './utils';

// Pinia store
const productStore = useProductStore();

const itemsPerPage = ref(10);
const sortBy = ref([{ key: 'created_at', order: 'desc' }]);
const statusFilter = ref('');
const selectedImage = ref(null); // For modal image display
const dialog = ref(false); // Modal visibility
const deleteDialog = ref(false); // Delete confirmation dialog
const productToDelete = ref(null); // Track product to delete
const search = ref(''); // Search term

// Debounced load function
const loadItems = debounce(async (options) => {
    const page = options.page || 1;
    const perPage = options.itemsPerPage || itemsPerPage.value;
    const sort = options.sortBy?.[0] || { key: 'created_at', order: 'desc' };

    try {
        await productStore.fetchProducts(
            page,
            perPage,
            search.value,
            sort.key,
            sort.order,
            statusFilter.value
        );
    } catch (error) {
        console.error('Failed to load products:', error);
    }
}, 300);

// Initial load
onMounted(() => {
    loadItems({ page: 1, itemsPerPage: itemsPerPage.value, sortBy: sortBy.value });
});

// Watch for search changes
watch([search, statusFilter], () => {
    loadItems({ page: 1, itemsPerPage: itemsPerPage.value, sortBy: sortBy.value });
});

// Status chip color
const getStatusColor = (status) => {
    return status === 'active' ? 'green' : 'grey';
};

// Open image modal
const openImageModal = (imageUrl) => {
    selectedImage.value = imageUrl;
    dialog.value = true;
};

// Show delete confirmation
const confirmDelete = (product) => {
    productToDelete.value = product;
    deleteDialog.value = true;
};

// Delete product
const deleteProduct = async () => {
    try {
        await productStore.deleteProduct(productToDelete.value.slug);
        deleteDialog.value = false;
        productToDelete.value = null;
    } catch (error) {
        console.error('Failed to delete product:', error);
    }
};
</script>

<template>
    <v-container class="py-6">
        <!-- Filters and Search -->
        <v-card class="pa-6 mb-6 rounded-xl elevation-3">
            <v-row justify="space-between" no-gutters>
                <v-col cols="12" sm="6" md="4" class="pa-2">
                    <v-text-field v-model="search" label="Search Products" prepend-inner-icon="mdi-magnify"
                        variant="outlined" clearable density="comfortable" class="rounded-lg" bg-color="white" flat />
                </v-col>
                <v-col cols="12" sm="6" md="4" class="pa-2">
                    <v-select v-model="statusFilter" :items="[
                        { title: 'All', value: '' },
                        { title: 'Active', value: 'active' },
                        { title: 'Inactive', value: 'inactive' }
                    ]" label="Filter by Status" prepend-inner-icon="mdi-filter" variant="outlined"
                        density="comfortable" class="rounded-lg" bg-color="white" flat clearable />
                </v-col>
                <v-col cols="12" sm="6" md="4" class="pa-2 text-right mt-2">
                    <router-link :to="{ name: 'AddProduct' }" class="text-decoration-none">
                        <v-btn color="teal-darken-2" class="rounded-lg px-6" elevation="2">
                            <v-icon left class="mr-2">mdi-plus</v-icon>
                            Add Product
                        </v-btn>
                    </router-link>
                </v-col>
            </v-row>
        </v-card>

        <!-- Data Table Server -->
        <v-card class="rounded-lg elevation-2">
            <v-data-table-server v-model:items-per-page="itemsPerPage" :headers="headers" :items="productStore.products"
                :items-length="productStore.pagination.total || 0" :loading="productStore.loading" item-value="id"
                v-model:sort-by="sortBy" :items-per-page-options="[5, 10, 20, 50]" @update:options="loadItems"
                height="550" fixed-header>
                <!-- S:N column -->
                <template v-slot:item.sn="{ index }">
                    {{ (productStore.pagination.current_page - 1) * itemsPerPage + index + 1 }}
                </template>

                <!-- Image column -->
                <template v-slot:item.image="{ item }">
                    <v-img v-if="item.image" :src="item.image" height="40" width="40"
                        class="my-2 rounded-lg cursor-pointer" @click="openImageModal(item.image)" />
                    <span v-else>No Image</span>
                </template>

                <!-- Status column -->
                <template v-slot:item.status="{ item }">
                    <v-chip :color="getStatusColor(item.status)" small class="text-uppercase font-weight-bold">
                        {{ item.status }}
                    </v-chip>
                </template>

                <!-- Actions column -->
                <template v-slot:item.actions="{ item }">
                    <router-link :to="{ name: 'EditProduct', params: { slug: item.slug } }">
                        <v-btn icon color="warning" size="small">
                            <v-icon>mdi-pencil</v-icon>
                        </v-btn>
                    </router-link>
                    <v-btn icon color="red-darken-2" size="small" class="ms-2" @click="confirmDelete(item)">
                        <v-icon>mdi-delete</v-icon>
                    </v-btn>
                </template>
            </v-data-table-server>
        </v-card>

        <!-- Image Modal -->
        <v-dialog v-model="dialog" max-width="500">
            <v-card class="rounded-lg elevation-3">
                <v-card-title class="d-flex align-center" style="background: linear-gradient(45deg, #00695c, #00897b)">
                    <span class="text-h6 font-weight-bold text-white">Product Image</span>
                    <v-spacer />
                    <v-btn icon @click="dialog = false" variant="plain">
                        <v-icon color="white">mdi-close</v-icon>
                    </v-btn>
                </v-card-title>
                <v-card-text class="text-center py-4">
                    <v-img :src="selectedImage" max-height="400" contain class="mx-auto rounded-lg" />
                </v-card-text>
            </v-card>
        </v-dialog>

        <!-- Delete Confirmation Dialog -->
        <v-dialog v-model="deleteDialog" max-width="400px">
            <v-card class="rounded-lg elevation-4">
                <v-card-title class="text-h6 font-weight-bold pa-4"
                    style="background: linear-gradient(45deg, #d32f2f, #f44336); color: white;">
                    <v-icon color="white" class="mr-2">mdi-alert-circle</v-icon>
                    Confirm Deletion
                </v-card-title>
                <v-card-text class="pa-6 text-body-1 text-grey-darken-2">
                    Are you sure you want to delete the product "<strong>{{ productToDelete?.name }}</strong>"? This
                    action
                    cannot be undone.
                </v-card-text>
                <v-card-actions class="pa-4">
                    <v-spacer />
                    <v-btn color="grey-darken-1" variant="outlined" class="rounded-md" @click="deleteDialog = false">
                        Cancel
                    </v-btn>
                    <v-btn color="red-darken-2" variant="flat" class="rounded-md ml-2" @click="deleteProduct">
                        <v-icon left>mdi-delete</v-icon>
                        Delete
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<style scoped>
.cursor-pointer {
    cursor: pointer;
}
</style>
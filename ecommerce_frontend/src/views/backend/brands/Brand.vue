<script setup>
import { ref, watch } from 'vue';
import { useBrandStore } from '@/stores/brands';
import debounce from 'lodash/debounce';

// Initialize store
const brandStore = useBrandStore();

// === Table State ===
const page = ref(1);
const itemsPerPage = ref(10);
const search = ref('');
const sortBy = ref([{ key: 'updated_at', order: 'desc' }]);
const statusFilter = ref('');
const selectedBrands = ref([]); // Kept for UI consistency, but not used for delete

// === Add/Edit Modal State ===
const showAddModal = ref(false);
const showEditModal = ref(false);
const newBrand = ref({ slug: null, name: '', status: 'active' });
const addForm = ref(null);
const serverErrors = ref({});

// === Validation Rules ===
const rules = {
    name: [
        v => !!v || 'Name is required',
        v => (v && v.length <= 255) || 'Name must be less than 255 characters',
    ],
    status: [
        v => !!v || 'Status is required',
        v => ['active', 'inactive'].includes(v) || 'Status must be "active" or "inactive"',
    ],
};

// === Table Headers ===
const headers = [
    { title: 'S.N.', key: 'sn', sortable: false, width: '50px' },
    { title: 'Name', key: 'name', sortable: true },
    { title: 'Status', key: 'status', sortable: true },
    { title: 'Created At', key: 'created_at', sortable: true },
    { title: 'Actions', key: 'actions', sortable: false },
];

// === Data Loading ===
const loadItems = debounce(async (options) => {
    page.value = options.page;
    itemsPerPage.value = options.itemsPerPage;
    const sort = options.sortBy.length ? options.sortBy[0] : { key: 'updated_at', order: 'desc' };
    sortBy.value = [sort];
    // Handle "All" by setting a large perPage value
    const perPage = itemsPerPage.value === -1 ? 99999 : itemsPerPage.value;
    await brandStore.fetchBrands(
        page.value,
        perPage,
        search.value,
        sort.key,
        sort.order,
        statusFilter.value
    );
}, 300);

// === Filter Updates ===
const updateFilters = debounce(() => {
    page.value = 1;
    loadItems({ page: page.value, itemsPerPage: itemsPerPage.value, sortBy: sortBy.value });
}, 500);

watch([search, statusFilter], updateFilters);

// === Add/Edit Modal Handlers ===
const resetNewBrand = () => ({ slug: null, name: '', status: 'active' });

const openAddModal = () => {
    newBrand.value = resetNewBrand();
    brandStore.resetErrors();
    serverErrors.value = {};
    showAddModal.value = true;
};

const closeAddModal = () => {
    showAddModal.value = false;
};

const openEditModal = async (slug) => {
    try {
        await brandStore.fetchBrand(slug);
        newBrand.value = {
            slug,
            name: brandStore.currentBrand.name,
            status: brandStore.currentBrand.status,
        };
        brandStore.resetErrors();
        serverErrors.value = {};
        showEditModal.value = true;
    } catch (error) {
        console.error('Error opening edit modal:', error);
    }
};

const closeEditModal = () => {
    showEditModal.value = false;
    newBrand.value = resetNewBrand();
};

// === Submit Handlers ===
const submitBrand = async () => {
    const { valid } = await addForm.value.validate();
    if (!valid) return;
    try {
        await brandStore.storeBrand(newBrand.value);
        showAddModal.value = false;
    } catch (error) {
        serverErrors.value = brandStore.brandErrors;
    }
};

const submitEditBrand = async () => {
    const { valid } = await addForm.value.validate();
    if (!valid) return;
    try {
        await brandStore.updateBrand(newBrand.value.slug, newBrand.value);
        showEditModal.value = false;
    } catch (error) {
        serverErrors.value = brandStore.brandErrors;
    }
};

// === Utility Functions ===
const getStatusColor = status => (status === 'active' ? 'green' : 'red');

// === CRUD Operations ===
const editBrand = slug => openEditModal(slug);

const deleteBrand = async (slug) => {
    if (!confirm('Are you sure you want to delete this brand?')) return;
    try {
        await brandStore.deleteBrand(slug);
    } catch (error) {
        alert('Failed to delete brand. Please try again.');
    }
};

// Deletes multiple selected brands after confirmation
const deleteMultipleBrands = async () => {
    if (!confirm('Are you sure you want to delete the selected brands?')) return; // Prompt user confirmation
    try {
        await brandStore.deleteMultipleBrands(selectedBrands.value); // Delete selected brands
        selectedBrands.value = []; // Clear selection on success
    } catch (error) {
        alert('Failed to delete brands. Please try again.'); // Notify user of failure
    }
};
</script>

<template>
    <v-container class="py-6">
        <!-- Filters and Search -->
        <v-card class="pa-4 mb-4 rounded-lg elevation-2">
            <v-row justify="space-between">
                <v-col cols="12" sm="6" md="4">
                    <v-text-field v-model="search" label="Search Brands" prepend-inner-icon="mdi-magnify"
                        variant="outlined" clearable density="comfortable" class="rounded-lg" />
                </v-col>
                <v-col cols="12" sm="6" md="4">
                    <v-select v-model="statusFilter" :items="[
                        { title: 'All', value: '' },
                        { title: 'Active', value: 'active' },
                        { title: 'Inactive', value: 'inactive' }
                    ]" label="Filter by Status" prepend-inner-icon="mdi-filter" variant="outlined"
                        density="comfortable" class="rounded-lg" clearable />
                </v-col>
                <v-spacer />
                <v-col cols="12" sm="auto">
                    <v-btn color="teal-darken-2" class="rounded-lg px-4 mr-2" @click="openAddModal">
                        <v-icon left>mdi-plus</v-icon> Add Category
                    </v-btn>
                    <v-btn color="red-darken-2" class="rounded-lg px-4" :disabled="selectedBrands.length === 0"
                        @click="deleteMultipleBrands">
                        <v-icon left>mdi-delete</v-icon> Delete Selected
                    </v-btn>
                </v-col>
            </v-row>
        </v-card>

        <!-- Data Table -->
        <v-card class="rounded-lg elevation-2">
            <v-data-table-server v-model:items-per-page="itemsPerPage" :headers="headers" :items="brandStore.brands"
                :items-length="brandStore.pagination.total || 0" :loading="brandStore.loading" item-value="slug"
                v-model:sort-by="sortBy" v-model="selectedBrands" show-select :items-per-page-options="[
                    { value: 5, title: '5' },
                    { value: 10, title: '10' },
                    { value: 20, title: '20' },
                    { value: 50, title: '50' },
                    { value: -1, title: 'All' }
                ]" @update:options="loadItems">
                <!-- S:N column -->
                <template v-slot:item.sn="{ index }">
                    {{ (brandStore.pagination.current_page - 1) * itemsPerPage + index + 1 }}
                </template>
                <!-- status -->
                <template v-slot:item.status="{ item }">
                    <v-chip :color="getStatusColor(item.status)" small class="text-uppercase font-weight-bold">
                        {{ item.status }}
                    </v-chip>
                </template>
                <!-- action buttons -->
                <template v-slot:item.actions="{ item }">
                    <v-btn icon color="warning" size="small" @click="editBrand(item.slug)">
                        <v-icon>mdi-pencil</v-icon>
                    </v-btn>
                    <v-btn icon color="red-darken-2" size="small" class="ms-2" @click="deleteBrand(item.slug)">
                        <v-icon>mdi-delete</v-icon>
                    </v-btn>
                </template>
            </v-data-table-server>
        </v-card>

        <!-- Add Brand Modal -->
        <v-dialog v-model="showAddModal" max-width="500" persistent transition="dialog-bottom-transition">
            <v-card rounded="xl" elevation="12">
                <v-card-title class="py-4 px-6" style="background: linear-gradient(45deg, #00695c, #00897b)">
                    <v-row align="center">
                        <v-col>
                            <span class="text-h6 font-weight-medium text-white">Add New Brand</span>
                        </v-col>
                        <v-col cols="auto">
                            <v-btn icon @click="closeAddModal" variant="text" color="white" class="mt-n1">
                                <v-icon>mdi-close</v-icon>
                            </v-btn>
                        </v-col>
                    </v-row>
                </v-card-title>
                <v-card-text class="pa-6">
                    <v-form ref="addForm" @submit.prevent="submitBrand" class="d-flex flex-column gap-4">
                        <v-text-field v-model="newBrand.name" label="Brand Name" prepend-inner-icon="mdi-tag"
                            density="comfortable" variant="outlined" color="teal-darken-2" class="rounded-lg mb-4"
                            :rules="rules.name" required :error-messages="serverErrors.name" />
                        <v-select v-model="newBrand.status" :items="[
                            { title: 'Active', value: 'active' },
                            { title: 'Inactive', value: 'inactive' }
                        ]" label="Status" prepend-inner-icon="mdi-toggle-switch" density="comfortable"
                            variant="outlined" color="teal-darken-2" class="rounded-lg mb-4" :rules="rules.status"
                            required :error-messages="serverErrors.status" />
                        <v-btn type="submit" color="teal-darken-2" variant="flat" size="large" block
                            :loading="brandStore.loading" class="mt-4 rounded-lg" elevation="2">
                            Save Brand
                        </v-btn>
                    </v-form>
                </v-card-text>
            </v-card>
        </v-dialog>

        <!-- Edit Brand Modal -->
        <v-dialog v-model="showEditModal" max-width="500" persistent transition="dialog-bottom-transition">
            <v-card rounded="xl" elevation="12">
                <v-card-title class="py-4 px-6" style="background: linear-gradient(45deg, #00695c, #00897b)">
                    <v-row align="center">
                        <v-col>
                            <span class="text-h6 font-weight-medium text-white">Edit Brand</span>
                        </v-col>
                        <v-col cols="auto">
                            <v-btn icon @click="closeEditModal" variant="text" color="white" class="mt-n1">
                                <v-icon>mdi-close</v-icon>
                            </v-btn>
                        </v-col>
                    </v-row>
                </v-card-title>
                <v-card-text class="pa-6">
                    <v-form ref="addForm" @submit.prevent="submitEditBrand" class="d-flex flex-column gap-4">
                        <v-text-field v-model="newBrand.name" label="Brand Name" prepend-inner-icon="mdi-tag"
                            density="comfortable" variant="outlined" color="teal-darken-2" class="rounded-lg mb-4"
                            :rules="rules.name" required :error-messages="serverErrors.name" />
                        <v-select v-model="newBrand.status" :items="[
                            { title: 'Active', value: 'active' },
                            { title: 'Inactive', value: 'inactive' }
                        ]" label="Status" prepend-inner-icon="mdi-toggle-switch" density="comfortable"
                            variant="outlined" color="teal-darken-2" class="rounded-lg mb-4" :rules="rules.status"
                            required :error-messages="serverErrors.status" />
                        <v-btn type="submit" color="teal-darken-2" variant="flat" size="large" block
                            :loading="brandStore.loading" class="mt-4 rounded-lg" elevation="2">
                            Update Brand
                        </v-btn>
                    </v-form>
                </v-card-text>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<style scoped>
.gap-4 {
    gap: 1rem;
}
</style>
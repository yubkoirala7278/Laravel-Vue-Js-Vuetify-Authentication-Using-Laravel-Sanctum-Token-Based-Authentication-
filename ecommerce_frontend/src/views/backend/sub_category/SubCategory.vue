<script setup>
import { ref, watch } from 'vue';
import { useSubCategoryStore } from '@/stores/sub_category';
import debounce from 'lodash/debounce';
import { useCategoryStore } from '@/stores/category';

// Initialize stores
const subCategoryStore = useSubCategoryStore();
const categoryStore = useCategoryStore();

// === Table State ===
const page = ref(1);
const itemsPerPage = ref(10);
const search = ref('');
const sortBy = ref([{ key: 'updated_at', order: 'desc' }]);
const statusFilter = ref('');

// === Add/Edit Modal State ===
const showAddModal = ref(false);
const showEditModal = ref(false);
const newSubCategory = ref({ slug: null, name: '', status: 'active', category_id: null });
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
    category_id: [
        v => !!v || 'Category is required',
    ],
};

// === Table Headers ===
const headers = [
    { title: 'S:N', key: 'sn', align: 'start', sortable: false },
    { title: 'Name', key: 'name', sortable: true },
    { title: 'Category', key: 'category', sortable: true },
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
    await subCategoryStore.fetchSubCategories(
        page.value,
        itemsPerPage.value,
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
const resetNewSubCategory = () => ({ slug: null, name: '', status: 'active', category_id: null });

const openAddModal = () => {
    newSubCategory.value = resetNewSubCategory();
    subCategoryStore.resetErrors();
    serverErrors.value = {};
    showAddModal.value = true;
};

const closeAddModal = () => {
    showAddModal.value = false;
};

const openEditModal = async (slug) => {
    try {
        await subCategoryStore.fetchSubCategory(slug);
        newSubCategory.value = {
            slug,
            name: subCategoryStore.currentSubCategory.name,
            status: subCategoryStore.currentSubCategory.status,
            category_id: subCategoryStore.currentSubCategory.category_id,
        };
        subCategoryStore.resetErrors();
        serverErrors.value = {};
        showEditModal.value = true;
    } catch (error) {
        console.error('Error opening edit modal:', error);
    }
};

const closeEditModal = () => {
    showEditModal.value = false;
    newSubCategory.value = resetNewSubCategory();
};

// === Submit Handlers ===
const submitSubCategory = async () => {
    const { valid } = await addForm.value.validate();
    if (!valid) return;
    try {
        await subCategoryStore.storeSubCategory(newSubCategory.value);
        showAddModal.value = false;
    } catch (error) {
        serverErrors.value = subCategoryStore.subCategoryErrors;
    }
};

const submitEditSubCategory = async () => {
    const { valid } = await addForm.value.validate();
    if (!valid) return;
    try {
        await subCategoryStore.updateSubCategory(newSubCategory.value.slug, newSubCategory.value);
        showEditModal.value = false;
    } catch (error) {
        serverErrors.value = subCategoryStore.subCategoryErrors;
    }
};

// === Utility Functions ===
const getStatusColor = status => (status === 'active' ? 'green' : 'red');

// === CRUD Operations ===
const editSubCategory = slug => openEditModal(slug);

const deleteSubCategory = async (slug) => {
    if (!confirm('Are you sure you want to delete this subcategory?')) return;
    try {
        await subCategoryStore.deleteSubCategory(slug);
    } catch (error) {
        alert('Failed to delete subcategory. Please try again.');
    }
};

// === Fetch Categories for Dropdown ===
const loadCategories = async () => {
    await categoryStore.fetchCategories(1, 100, '', 'name', 'asc', 'active');
};
loadCategories(); // Fetch categories on mount
</script>

<template>
    <v-container class="py-6">
        <!-- Filters and Search -->
        <v-card class="pa-4 mb-4 rounded-lg elevation-2">
            <v-row align="center" justify="space-between">
                <v-col cols="12" sm="6" md="4">
                    <v-text-field v-model="search" label="Search Subcategories" prepend-inner-icon="mdi-magnify"
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
                    <v-btn color="teal-darken-2" class="rounded-lg px-4" @click="openAddModal">
                        <v-icon left>mdi-plus</v-icon> Add Subcategory
                    </v-btn>
                </v-col>
            </v-row>
        </v-card>

        <!-- Data Table -->
        <v-card class="rounded-lg elevation-2">
            <v-data-table-server v-model:items-per-page="itemsPerPage" :headers="headers"
                :items="subCategoryStore.subCategories" :items-length="subCategoryStore.pagination.total || 0"
                :loading="subCategoryStore.loading" item-value="slug" v-model:sort-by="sortBy" :items-per-page-options="[
                    { value: 5, title: '5' },
                    { value: 10, title: '10' },
                    { value: 20, title: '20' },
                    { value: 50, title: '50' },
                    { value: -1, title: 'All' }
                ]" @update:options="loadItems">
                <!-- S:N column -->
                <template v-slot:item.sn="{ index }">
                    {{ (subCategoryStore.pagination.current_page - 1) * itemsPerPage + index + 1 }}
                </template>
                <!-- category -->
                <template v-slot:item.category="{ item }">
                    <span>{{ item.category || 'N/A' }}</span>
                </template>
                <!-- status -->
                <template v-slot:item.status="{ item }">
                    <v-chip :color="getStatusColor(item.status)" small class="text-uppercase font-weight-bold">
                        {{ item.status }}
                    </v-chip>
                </template>
                <!-- action buttons -->
                <template v-slot:item.actions="{ item }">
                    <v-btn icon color="warning" size="small" @click="editSubCategory(item.slug)">
                        <v-icon>mdi-pencil</v-icon>
                    </v-btn>
                    <v-btn icon color="red-darken-2" size="small" class="ms-2" @click="deleteSubCategory(item.slug)">
                        <v-icon>mdi-delete</v-icon>
                    </v-btn>
                </template>
            </v-data-table-server>
        </v-card>

        <!-- Add Subcategory Modal -->
        <v-dialog v-model="showAddModal" max-width="500" persistent transition="dialog-bottom-transition">
            <v-card rounded="xl" elevation="12">
                <v-card-title class="py-4 px-6" style="background: linear-gradient(45deg, #00695c, #00897b)">
                    <v-row align="center">
                        <v-col>
                            <span class="text-h6 font-weight-medium text-white">Add New Subcategory</span>
                        </v-col>
                        <v-col cols="auto">
                            <v-btn icon @click="closeAddModal" variant="text" color="white" class="mt-n1">
                                <v-icon>mdi-close</v-icon>
                            </v-btn>
                        </v-col>
                    </v-row>
                </v-card-title>
                <v-card-text class="pa-6">
                    <v-form ref="addForm" @submit.prevent="submitSubCategory" class="d-flex flex-column gap-4">
                        <v-text-field v-model="newSubCategory.name" label="Subcategory Name"
                            prepend-inner-icon="mdi-tag" density="comfortable" variant="outlined" color="teal-darken-2"
                            class="rounded-lg mb-4" :rules="rules.name" required :error-messages="serverErrors.name" />
                        <v-select v-model="newSubCategory.status" :items="[
                            { title: 'Active', value: 'active' },
                            { title: 'Inactive', value: 'inactive' }
                        ]" label="Status" prepend-inner-icon="mdi-toggle-switch" density="comfortable"
                            variant="outlined" color="teal-darken-2" class="rounded-lg mb-4" :rules="rules.status"
                            required :error-messages="serverErrors.status" />
                        <v-select v-model="newSubCategory.category_id" :items="categoryStore.categories.map(cat => ({
                            title: cat.name,
                            value: cat.id
                        }))" label="Category" prepend-inner-icon="mdi-category" density="comfortable"
                            variant="outlined" color="teal-darken-2" class="rounded-lg mb-4" :rules="rules.category_id"
                            required :error-messages="serverErrors.category_id" />
                        <v-btn type="submit" color="teal-darken-2" variant="flat" size="large" block
                            :loading="subCategoryStore.loading" class="mt-4 rounded-lg" elevation="2">
                            Save Subcategory
                        </v-btn>
                    </v-form>
                </v-card-text>
            </v-card>
        </v-dialog>

        <!-- Edit Subcategory Modal -->
        <v-dialog v-model="showEditModal" max-width="500" persistent transition="dialog-bottom-transition">
            <v-card rounded="xl" elevation="12">
                <v-card-title class="py-4 px-6" style="background: linear-gradient(45deg, #00695c, #00897b)">
                    <v-row align="center">
                        <v-col>
                            <span class="text-h6 font-weight-medium text-white">Edit Subcategory</span>
                        </v-col>
                        <v-col cols="auto">
                            <v-btn icon @click="closeEditModal" variant="text" color="white" class="mt-n1">
                                <v-icon>mdi-close</v-icon>
                            </v-btn>
                        </v-col>
                    </v-row>
                </v-card-title>
                <v-card-text class="pa-6">
                    <v-form ref="addForm" @submit.prevent="submitEditSubCategory" class="d-flex flex-column gap-4">
                        <v-text-field v-model="newSubCategory.name" label="Subcategory Name"
                            prepend-inner-icon="mdi-tag" density="comfortable" variant="outlined" color="teal-darken-2"
                            class="rounded-lg mb-4" :rules="rules.name" required :error-messages="serverErrors.name" />
                        <v-select v-model="newSubCategory.status" :items="[
                            { title: 'Active', value: 'active' },
                            { title: 'Inactive', value: 'inactive' }
                        ]" label="Status" prepend-inner-icon="mdi-toggle-switch" density="comfortable"
                            variant="outlined" color="teal-darken-2" class="rounded-lg mb-4" :rules="rules.status"
                            required :error-messages="serverErrors.status" />
                        <v-select v-model="newSubCategory.category_id" :items="categoryStore.categories.map(cat => ({
                            title: cat.name,
                            value: cat.id
                        }))" label="Category" prepend-inner-icon="mdi-category" density="comfortable"
                            variant="outlined" color="teal-darken-2" class="rounded-lg mb-4" :rules="rules.category_id"
                            required :error-messages="serverErrors.category_id" />
                        <v-btn type="submit" color="teal-darken-2" variant="flat" size="large" block
                            :loading="subCategoryStore.loading" class="mt-4 rounded-lg" elevation="2">
                            Update Subcategory
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
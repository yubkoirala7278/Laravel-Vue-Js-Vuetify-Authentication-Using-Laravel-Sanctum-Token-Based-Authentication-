<script setup>
import { ref, watch } from 'vue';
import { useCategoryStore } from '@/stores/category';
import debounce from 'lodash/debounce';

// Initialize the category store from Pinia for state management
const categoryStore = useCategoryStore();

// === Table State ===
// Reactive variables for managing table pagination, sorting, filtering, and selection
const page = ref(1); // Current page number for pagination
const itemsPerPage = ref(10); // Number of items per page
const search = ref(''); // Search query for filtering categories
const sortBy = ref([{ key: 'updated_at', order: 'desc' }]); // Sorting criteria (default: updated_at descending)
const statusFilter = ref(''); // Filter by category status (active/inactive)
const selectedCategories = ref([]); // Array of selected category slugs for multi-delete

// === Image Modal State ===
// Controls for displaying and managing the image preview modal
const showImageModal = ref(false); // Toggle for image modal visibility
const selectedImage = ref(null); // URL of the selected category image

// === Add/Edit Modal State ===
// Reactive state for managing add/edit modals and form data
const showAddModal = ref(false); // Toggle for add category modal
const showEditModal = ref(false); // Toggle for edit category modal
const newCategory = ref({ slug: null, name: '', status: 'active', image: null }); // Form data for new/edit category
const addForm = ref(null); // Reference to the Vuetify form for validation
const serverErrors = ref({}); // Server-side validation errors from API

// === Validation Rules ===
// Client-side validation rules for category form fields
const rules = {
    name: [
        v => !!v || 'Name is required', // Ensure name is not empty
        v => (v && v.length <= 255) || 'Name must be less than 255 characters', // Enforce max length
    ],
    status: [
        v => !!v || 'Status is required', // Ensure status is selected
        v => ['active', 'inactive'].includes(v) || 'Status must be "active" or "inactive"', // Restrict to valid values
    ],
    image: [
        v => !v || v.size <= 2 * 1024 * 1024 || 'Image must be less than 2MB', // Optional image with size limit
    ],
};

// === Table Headers ===
// Configuration for the Vuetify data table columns
const headers = [
    { title: '', key: 'data-table-select', sortable: false, width: '50px' }, // Checkbox column for selection
    { title: 'Image', key: 'image', sortable: false }, // Category image preview
    { title: 'Name', key: 'name', sortable: true }, // Category name (sortable)
    { title: 'Status', key: 'status', sortable: true }, // Category status (sortable)
    { title: 'Created At', key: 'created_at', sortable: true }, // Creation timestamp (sortable)
    { title: 'Actions', key: 'actions', sortable: false }, // Edit/Delete action buttons
];

// === Data Loading ===
// Debounced function to fetch categories from the server based on table options
const loadItems = debounce(async (options) => {
    page.value = options.page; // Update current page
    itemsPerPage.value = options.itemsPerPage; // Update items per page
    const sort = options.sortBy.length ? options.sortBy[0] : { key: 'updated_at', order: 'desc' }; // Default sort if none provided
    sortBy.value = [sort]; // Sync sortBy with table state

    // Fetch categories from the store with current filters and pagination
    await categoryStore.fetchCategories(
        page.value,
        itemsPerPage.value,
        search.value,
        sort.key,
        sort.order,
        statusFilter.value
    );
}, 300); // 300ms debounce to prevent rapid API calls

// === Filter Updates ===
// Debounced function to reset pagination and reload items when filters change
const updateFilters = debounce(() => {
    page.value = 1; // Reset to first page on filter change
    loadItems({ page: page.value, itemsPerPage: itemsPerPage.value, sortBy: sortBy.value }); // Reload with updated filters
}, 500); // 500ms debounce for smooth user input handling

// Watch search and status filter changes to trigger updates
watch([search, statusFilter], updateFilters);

// === Image Modal Handlers ===
// Opens the image preview modal with the selected image URL
const openImageModal = (imageUrl) => {
    selectedImage.value = imageUrl; // Set the image to display
    showImageModal.value = true; // Show the modal
};

// === Add/Edit Modal Handlers ===
// Resets the newCategory object to default values
const resetNewCategory = () => ({ slug: null, name: '', status: 'active', image: null });

// Opens the add category modal with a clean state
const openAddModal = () => {
    newCategory.value = resetNewCategory(); // Reset form data
    categoryStore.resetErrors(); // Clear previous store errors
    serverErrors.value = {}; // Clear previous server errors
    showAddModal.value = true; // Display the modal
};

// Closes the add category modal
const closeAddModal = () => {
    showAddModal.value = false; // Hide the modal
};

// Opens the edit category modal and pre-fills with existing data
const openEditModal = async (slug) => {
    try {
        await categoryStore.fetchCategory(slug); // Fetch category details by slug
        newCategory.value = {
            slug, // Store slug for update reference
            name: categoryStore.currentCategory.name, // Pre-fill name
            status: categoryStore.currentCategory.status, // Pre-fill status
            image: null, // New image optional
        };
        categoryStore.resetErrors(); // Clear store errors
        serverErrors.value = {}; // Clear server errors
        showEditModal.value = true; // Show the modal
    } catch (error) {
        console.error('Error opening edit modal:', error); // Log fetch errors
    }
};

// Closes the edit category modal and resets form
const closeEditModal = () => {
    showEditModal.value = false; // Hide the modal
    newCategory.value = resetNewCategory(); // Reset form data
};

// === Submit Handlers ===
// Submits a new category to the store
const submitCategory = async () => {
    const { valid } = await addForm.value.validate(); // Validate form
    if (!valid) return; // Exit if validation fails

    try {
        await categoryStore.storeCategory(newCategory.value); // Save new category
        showAddModal.value = false; // Close modal on success
    } catch (error) {
        serverErrors.value = categoryStore.categoryErrors; // Display validation errors from store
    }
};

// Submits an updated category to the store
const submitEditCategory = async () => {
    const { valid } = await addForm.value.validate(); // Validate form
    if (!valid) return; // Exit if validation fails

    try {
        await categoryStore.updateCategory(newCategory.value.slug, newCategory.value); // Update category by slug
        showEditModal.value = false; // Close modal on success
    } catch (error) {
        serverErrors.value = categoryStore.categoryErrors; // Display validation errors from store
    }
};

// === Utility Functions ===
// Returns a color for the status chip based on category status
const getStatusColor = status => (status === 'active' ? 'green' : 'red');

// === CRUD Operations ===
// Triggers edit modal for a specific category
const editCategory = slug => openEditModal(slug);

// Deletes a single category after confirmation
const deleteCategory = async (slug) => {
    if (!confirm('Are you sure you want to delete this category?')) return; // Prompt user confirmation
    try {
        await categoryStore.deleteCategory(slug); // Delete category by slug
    } catch (error) {
        alert('Failed to delete category. Please try again.'); // Notify user of failure
    }
};

// Deletes multiple selected categories after confirmation
const deleteMultipleCategories = async () => {
    if (!confirm('Are you sure you want to delete the selected categories?')) return; // Prompt user confirmation
    try {
        await categoryStore.deleteMultipleCategories(selectedCategories.value); // Delete selected categories
        selectedCategories.value = []; // Clear selection on success
    } catch (error) {
        alert('Failed to delete categories. Please try again.'); // Notify user of failure
    }
};
</script>

<template>
    <v-container class="py-6">
        <!-- Filters and Search -->
        <v-card class="pa-4 mb-4 rounded-lg elevation-2">
            <v-row align="center" justify="space-between">
                <v-col cols="12" sm="6" md="4">
                    <v-text-field v-model="search" label="Search Categories" prepend-inner-icon="mdi-magnify"
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
                    <v-btn color="red-darken-2" class="rounded-lg px-4" :disabled="selectedCategories.length === 0"
                        @click="deleteMultipleCategories">
                        <v-icon left>mdi-delete</v-icon> Delete Selected
                    </v-btn>
                </v-col>
            </v-row>
        </v-card>

        <!-- Data Table -->
        <v-card class="rounded-lg elevation-2">
            <v-data-table-server v-model:items-per-page="itemsPerPage" :headers="headers"
                :items="categoryStore.categories" :items-length="categoryStore.pagination.total || 0"
                :loading="categoryStore.loading" item-value="slug" v-model:sort-by="sortBy" v-model="selectedCategories"
                show-select :items-per-page-options="[5, 10, 20, 50]" @update:options="loadItems">
                <template v-slot:item.image="{ item }">
                    <v-img v-if="item.image" :src="item.image" height="40" width="40"
                        class="my-2 rounded-lg cursor-pointer" @click="openImageModal(item.image)" />
                    <span v-else>No Image</span>
                </template>
                <template v-slot:item.status="{ item }">
                    <v-chip :color="getStatusColor(item.status)" small class="text-uppercase font-weight-bold">
                        {{ item.status }}
                    </v-chip>
                </template>
                <template v-slot:item.actions="{ item }">
                    <v-btn icon color="warning" size="small" @click="editCategory(item.slug)">
                        <v-icon>mdi-pencil</v-icon>
                    </v-btn>
                    <v-btn icon color="red-darken-2" size="small" class="ms-2" @click="deleteCategory(item.slug)">
                        <v-icon>mdi-delete</v-icon>
                    </v-btn>
                </template>
            </v-data-table-server>
        </v-card>

        <!-- Image Modal -->
        <v-dialog v-model="showImageModal" max-width="500">
            <v-card class="rounded-lg elevation-3">
                <v-card-title class="d-flex align-center" style="background: linear-gradient(45deg, #00695c, #00897b)">
                    <span class="text-h6 font-weight-bold text-white">Category Image</span>
                    <v-spacer />
                    <v-btn icon @click="showImageModal = false" variant="plain">
                        <v-icon color="white">mdi-close</v-icon>
                    </v-btn>
                </v-card-title>
                <v-card-text class="text-center py-4">
                    <v-img :src="selectedImage" max-height="400" contain class="mx-auto rounded-lg" />
                </v-card-text>
            </v-card>
        </v-dialog>

        <!-- Add Category Modal -->
        <v-dialog v-model="showAddModal" max-width="500" persistent transition="dialog-bottom-transition">
            <v-card rounded="xl" elevation="12">
                <v-card-title class="py-4 px-6" style="background: linear-gradient(45deg, #00695c, #00897b)">
                    <v-row align="center">
                        <v-col>
                            <span class="text-h6 font-weight-medium text-white">Add New Category</span>
                        </v-col>
                        <v-col cols="auto">
                            <v-btn icon @click="closeAddModal" variant="text" color="white" class="mt-n1">
                                <v-icon>mdi-close</v-icon>
                            </v-btn>
                        </v-col>
                    </v-row>
                </v-card-title>
                <v-card-text class="pa-6">
                    <v-form ref="addForm" @submit.prevent="submitCategory" class="d-flex flex-column gap-4">
                        <v-text-field v-model="newCategory.name" label="Category Name" prepend-icon="mdi-tag"
                            density="comfortable" variant="outlined" color="teal-darken-2" class="rounded-lg mb-4"
                            :rules="rules.name" required :error-messages="serverErrors.name" />
                        <v-select v-model="newCategory.status" :items="[
                            { title: 'Active', value: 'active' },
                            { title: 'Inactive', value: 'inactive' }
                        ]" label="Status" prepend-icon="mdi-toggle-switch" density="comfortable" variant="outlined"
                            color="teal-darken-2" class="rounded-lg mb-4" :rules="rules.status" required
                            :error-messages="serverErrors.status" />
                        <v-file-input v-model="newCategory.image" label="Category Image" prepend-icon="mdi-image"
                            accept="image/*" density="comfortable" variant="outlined" class="rounded-lg mb-4"
                            color="teal-darken-2" :rules="rules.image" required :error-messages="serverErrors.image" />
                        <v-btn type="submit" color="teal-darken-2" variant="flat" size="large" block
                            :loading="categoryStore.loading" class="mt-4 rounded-lg" elevation="2">
                            Save Category
                        </v-btn>
                    </v-form>
                </v-card-text>
            </v-card>
        </v-dialog>

        <!-- Edit Category Modal -->
        <v-dialog v-model="showEditModal" max-width="500" persistent transition="dialog-bottom-transition">
            <v-card rounded="xl" elevation="12">
                <v-card-title class="py-4 px-6" style="background: linear-gradient(45deg, #00695c, #00897b)">
                    <v-row align="center">
                        <v-col>
                            <span class="text-h6 font-weight-medium text-white">Edit Category</span>
                        </v-col>
                        <v-col cols="auto">
                            <v-btn icon @click="closeEditModal" variant="text" color="white" class="mt-n1">
                                <v-icon>mdi-close</v-icon>
                            </v-btn>
                        </v-col>
                    </v-row>
                </v-card-title>
                <v-card-text class="pa-6">
                    <v-form ref="addForm" @submit.prevent="submitEditCategory" class="d-flex flex-column gap-4">
                        <v-text-field v-model="newCategory.name" label="Category Name" prepend-icon="mdi-tag"
                            density="comfortable" variant="outlined" color="teal-darken-2" class="rounded-lg mb-4"
                            :rules="rules.name" required :error-messages="serverErrors.name" />
                        <v-select v-model="newCategory.status" :items="[
                            { title: 'Active', value: 'active' },
                            { title: 'Inactive', value: 'inactive' }
                        ]" label="Status" prepend-icon="mdi-toggle-switch" density="comfortable" variant="outlined"
                            color="teal-darken-2" class="rounded-lg mb-4" :rules="rules.status" required
                            :error-messages="serverErrors.status" />
                        <v-file-input v-model="newCategory.image" label="Category Image" prepend-icon="mdi-image"
                            accept="image/*" density="comfortable" variant="outlined" class="rounded-lg mb-4"
                            color="teal-darken-2" :rules="rules.image" :error-messages="serverErrors.image" />
                        <v-btn type="submit" color="teal-darken-2" variant="flat" size="large" block
                            :loading="categoryStore.loading" class="mt-4 rounded-lg" elevation="2">
                            Update Category
                        </v-btn>
                    </v-form>
                </v-card-text>
            </v-card>
        </v-dialog>
    </v-container>
</template>

<style scoped>
.cursor-pointer {
    cursor: pointer;
}

.gap-4 {
    gap: 1rem;
}
</style>
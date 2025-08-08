// Store Categories Module - Handles store category management in admin dashboard
import { showNotification } from './notifications.js';
import { hideModal, showModal } from './modals.js';

let storeCategories = [];

/**
 * Load store categories for admin dashboard
 */
export async function loadStoreCategories() {
    try {
        const response = await fetch('api/store_categories_admin.php');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        if (data.success && Array.isArray(data.data)) {
            storeCategories = data.data;
            renderStoreCategories(storeCategories);
        } else {
            console.error('Failed to load store categories:', data.error);
            showNotification('Failed to load store categories', 'error');
        }
    } catch (error) {
        console.error('Error loading store categories:', error);
        showNotification('Error loading store categories', 'error');
    }
}

/**
 * Render store categories in the admin table
 */
export function renderStoreCategories(categories = storeCategories) {
    const tbody = document.getElementById('store-categories-tbody');
    if (!tbody) return;

    if (!categories || categories.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align: center; padding: 20px; color: #666;">No store categories found</td></tr>';
        return;
    }

    tbody.innerHTML = categories.map(category => `
        <tr data-id="${category.id}">
            <td>${category.id || 'N/A'}</td>
            <td>
                ${category.image_url ? 
                    `<img src="${category.image_url}" alt="${category.display_name}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">` :
                    `<i class="${category.icon_class || 'fas fa-box'}" style="font-size: 1.5rem; color: #666;"></i>`
                }
            </td>
            <td>${category.name}</td>
            <td>${category.display_name}</td>
            <td>${category.filter_value}</td>
            <td>${category.sort_order || 0}</td>
            <td>
                <span class="status-badge ${category.is_active ? 'active' : 'inactive'}">
                    ${category.is_active ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td>
                <div class="action-buttons">
                    <button class="btn btn-sm btn-primary" onclick="storeCategoriesModule.editCategory(${category.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="storeCategoriesModule.deleteCategory(${category.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

/**
 * Add a new store category
 */
export async function addStoreCategory(formData) {
    try {
        // Check if there's a file upload
        const imageFile = formData.get('image_upload');
        let response;
        
        if (imageFile && imageFile.size > 0) {
            // First add the category without image
            const categoryData = Object.fromEntries(formData.entries());
            delete categoryData.image_upload;
            
            const addResponse = await fetch('api/store_categories_admin.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(categoryData)
            });
            
            if (!addResponse.ok) {
                throw new Error(`HTTP error! status: ${addResponse.status}`);
            }
            
            const addData = await addResponse.json();
            if (!addData.success) {
                throw new Error(addData.error || 'Failed to add category');
            }
            
            // Now upload the image and update the category
            formData.append('category_id', addData.category_id);
            response = await fetch('api/upload_category_image.php', {
                method: 'POST',
                body: formData
            });
        } else {
            // Handle regular JSON data
            const data = Object.fromEntries(formData.entries());
            response = await fetch('api/store_categories_admin.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });
        }

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        if (data.success) {
            showNotification('Store category added successfully', 'success');
            hideModal('add-store-category-modal');
            loadStoreCategories(); // Reload the list
        } else {
            showNotification(data.error || 'Failed to add store category', 'error');
        }
    } catch (error) {
        console.error('Error adding store category:', error);
        showNotification('Error adding store category', 'error');
    }
}

/**
 * Edit a store category
 */
export async function editStoreCategory(id, formData) {
    try {
        // Check if there's a file upload
        const imageFile = formData.get('image_upload');
        let response;
        
        if (imageFile && imageFile.size > 0) {
            // Handle file upload
            formData.append('category_id', id);
            response = await fetch('api/upload_category_image.php', {
                method: 'POST',
                body: formData
            });
        } else {
            // Handle regular JSON data
            const data = Object.fromEntries(formData.entries());
            response = await fetch('api/store_categories_admin.php', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id, ...data })
            });
        }

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        if (data.success) {
            showNotification('Store category updated successfully', 'success');
            hideModal('edit-store-category-modal');
            loadStoreCategories(); // Reload the list
        } else {
            showNotification(data.error || 'Failed to update store category', 'error');
        }
    } catch (error) {
        console.error('Error updating store category:', error);
        showNotification('Error updating store category', 'error');
    }
}

/**
 * Delete a store category
 */
export async function deleteStoreCategory(id) {
    if (!confirm('Are you sure you want to delete this store category?')) {
        return;
    }

    try {
        const response = await fetch('api/store_categories_admin.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        if (data.success) {
            showNotification('Store category deleted successfully', 'success');
            loadStoreCategories(); // Reload the list
        } else {
            showNotification(data.error || 'Failed to delete store category', 'error');
        }
    } catch (error) {
        console.error('Error deleting store category:', error);
        showNotification('Error deleting store category', 'error');
    }
}

/**
 * Load category data for editing
 */
export function loadCategoryForEdit(id) {
    const category = storeCategories.find(cat => cat.id == id);
    if (!category) {
        showNotification('Category not found', 'error');
        return;
    }

    // Populate the edit form
    document.getElementById('edit-store-category-id').value = category.id;
    document.getElementById('edit-store-category-name').value = category.name;
    document.getElementById('edit-store-category-display-name').value = category.display_name;
    document.getElementById('edit-store-category-image-url').value = category.image_url || '';
    document.getElementById('edit-store-category-icon-class').value = category.icon_class || '';
    document.getElementById('edit-store-category-filter-value').value = category.filter_value;
    document.getElementById('edit-store-category-sort-order').value = category.sort_order || 0;
    document.getElementById('edit-store-category-status').value = category.is_active;

    showModal('edit-store-category-modal');
}

/**
 * Search store categories
 */
export function searchStoreCategories(searchTerm) {
    if (!searchTerm) {
        renderStoreCategories(storeCategories);
        return;
    }

    const filtered = storeCategories.filter(category => 
        category.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        category.display_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        category.filter_value.toLowerCase().includes(searchTerm.toLowerCase())
    );

    renderStoreCategories(filtered);
}

/**
 * Export store categories to CSV
 */
export function exportStoreCategoriesCSV() {
    if (!storeCategories || storeCategories.length === 0) {
        showNotification('No categories to export', 'warning');
        return;
    }

    const headers = ['ID', 'Name', 'Display Name', 'Image URL', 'Icon Class', 'Filter Value', 'Sort Order', 'Status'];
    const csvContent = [
        headers.join(','),
        ...storeCategories.map(category => [
            category.id,
            `"${category.name}"`,
            `"${category.display_name}"`,
            `"${category.image_url || ''}"`,
            `"${category.icon_class || ''}"`,
            `"${category.filter_value}"`,
            category.sort_order || 0,
            category.is_active ? 'Active' : 'Inactive'
        ].join(','))
    ].join('\n');

    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'store_categories.csv';
    a.click();
    window.URL.revokeObjectURL(url);

    showNotification('Store categories exported successfully', 'success');
}

// Initialize event listeners
export function initializeStoreCategories() {
    // Add store category form submission
    const addForm = document.getElementById('add-store-category-form');
    if (addForm) {
        addForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(addForm);
            
            // Handle file upload
            const imageFile = formData.get('image_upload');
            if (imageFile && imageFile.size > 0) {
                // If file is uploaded, remove the URL field
                formData.delete('image_url');
            } else {
                // If no file, remove the file field
                formData.delete('image_upload');
            }
            
            await addStoreCategory(formData);
        });
    }

    // Edit store category form submission
    const editForm = document.getElementById('edit-store-category-form');
    if (editForm) {
        editForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(editForm);
            
            // Handle file upload
            const imageFile = formData.get('image_upload');
            if (imageFile && imageFile.size > 0) {
                // If file is uploaded, remove the URL field
                formData.delete('image_url');
            } else {
                // If no file, remove the file field
                formData.delete('image_upload');
            }
            
            const id = formData.get('id');
            formData.delete('id');
            
            await editStoreCategory(id, formData);
        });
    }

    // Search functionality
    const searchInput = document.getElementById('category-search');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            searchStoreCategories(e.target.value);
        });
    }
}

// Export functions for global access
export const storeCategoriesModule = {
    loadStoreCategories,
    renderStoreCategories,
    addStoreCategory,
    editStoreCategory,
    deleteStoreCategory,
    loadCategoryForEdit,
    searchStoreCategories,
    exportStoreCategoriesCSV,
    initializeStoreCategories
}; 
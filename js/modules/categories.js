import { sanitizeHTML } from './utils.js';
import { showNotification } from './notifications.js';
import { updateProductCategoryOptions } from './products.js';

function processCategoryData(category) {
    return {
        id: parseInt(category.id) || 0,
        name: sanitizeHTML(category.name || ''),
        description: sanitizeHTML(category.description || '')
    };
}

export function renderCategories(categoriesToRender, state) {
    const tbody = document.getElementById('categories-tbody');
    if (!tbody) {
        console.error('categories-tbody element not found!');
        return;
    }
    
    tbody.innerHTML = '';
    
    if (!Array.isArray(categoriesToRender) || categoriesToRender.length === 0) {
        tbody.innerHTML = `<tr><td colspan="4" style="text-align: center; padding: 20px; color: #666;">No categories found</td></tr>`;
        return;
    }
    
    categoriesToRender.forEach(category => {
        const tr = document.createElement('tr');
        
        tr.innerHTML = `
            <td>${category.id}</td>
            <td>${category.name}</td>
            <td>${category.description}</td>
            <td>
                <button class="btn btn-warning btn-sm" onclick="window.adminModules.editCategory(${category.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-danger btn-sm" onclick="window.adminModules.deleteCategory(${category.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

export function fetchCategories(state) {
    return fetch('api/get_categories_admin.php')
        .then(response => {
            if (!response.ok) {
                // Gracefully handle 404 errors as requested
                if (response.status === 404) {
                    console.warn('Categories endpoint not found (404), using empty categories list');
                    state.categories = [];
                    renderCategories([], state);
                    return;
                }
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (data && data.success !== false && Array.isArray(data.data)) {
                state.categories = data.data.map(category => processCategoryData(category));
                renderCategories(state.categories, state);
                updateProductCategoryOptions(state);
            } else if (data) {
                throw new Error(data.error || 'Failed to fetch categories');
            }
        })
        .catch(error => {
            console.error('Categories fetch error:', error);
            if (!error.message.includes('404')) {
                showNotification(`Error fetching categories: ${error.message}`, 'error');
            }
            renderCategories([], state);
        });
}

// CRUD operations
export function addCategory(categoryData, state) {
    return fetch('api/get_categories_admin.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(categoryData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Category added successfully!', 'success');
            fetchCategories(state);
        } else {
            throw new Error(data.error || 'Failed to add category');
        }
    })
    .catch(error => {
        console.error('Add category error:', error);
        showNotification(`Error adding category: ${error.message}`, 'error');
    });
}

export function editCategory(categoryId, state) {
    const category = state.categories.find(c => c.id === categoryId);
    if (!category) {
        showNotification('Category not found!', 'error');
        return;
    }
    
    // Create a simple prompt-based edit (you can replace this with a modal later)
    const newName = prompt('Enter new category name:', category.name);
    if (newName === null) return; // User cancelled
    
    const newDescription = prompt('Enter new subcategory/description:', category.description);
    if (newDescription === null) return; // User cancelled
    
    const updatedData = {
        id: categoryId,
        name: newName.trim(),
        description: newDescription.trim()
    };
    
    if (!updatedData.name) {
        showNotification('Category name cannot be empty!', 'error');
        return;
    }
    
    fetch('api/get_categories_admin.php', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(updatedData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Category updated successfully!', 'success');
            fetchCategories(state);
        } else {
            throw new Error(data.error || 'Failed to update category');
        }
    })
    .catch(error => {
        console.error('Edit category error:', error);
        showNotification(`Error updating category: ${error.message}`, 'error');
    });
}

export function deleteCategory(categoryId, state) {
    if (confirm('Are you sure you want to delete this category?')) {
        fetch('api/get_categories_admin.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: categoryId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Category deleted successfully!', 'success');
                fetchCategories(state);
            } else {
                throw new Error(data.error || 'Failed to delete category');
            }
        })
        .catch(error => {
            console.error('Delete category error:', error);
            showNotification(`Error deleting category: ${error.message}`, 'error');
        });
    }
}

// Add category with simple prompt (you can replace with modal later)
export function promptAddCategory(state) {
    const name = prompt('Enter category name:');
    if (name === null) return; // User cancelled
    
    const description = prompt('Enter subcategory/description (optional):') || '';
    
    const categoryData = {
        name: name.trim(),
        description: description.trim()
    };
    
    if (!categoryData.name) {
        showNotification('Category name cannot be empty!', 'error');
        return;
    }
    
    addCategory(categoryData, state);
}

export function initializeCategories(state) {
    return fetchCategories(state);
}

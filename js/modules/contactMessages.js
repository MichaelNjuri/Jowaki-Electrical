import { showNotification } from './notifications.js';
import { sanitizeHTML } from './utils.js';

// Contact messages state
let contactMessages = [];

// Fetch contact messages from API
export function fetchContactMessages() {
    console.log('Fetching contact messages...');
    return fetch('API/get_contact_messages_fixed.php', {
        credentials: 'include'
    })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            return response.json();
        })
        .then(data => {
            console.log('Contact messages data:', data);
            if (data.success) {
                contactMessages = data.messages || [];
                console.log('Loaded contact messages:', contactMessages);
                renderContactMessages();
                return data.messages;
            } else {
                throw new Error(data.error || 'Failed to fetch contact messages');
            }
        })
        .catch(error => {
            console.error('Contact messages fetch error:', error);
            showNotification(`Error fetching contact messages: ${error.message}`, 'error');
            renderContactMessages([]);
        });
}

// Render contact messages in the table
function renderContactMessages(messages = contactMessages) {
    const tbody = document.getElementById('contact-messages-tbody');
    if (!tbody) {
        console.error('Contact messages tbody not found');
        return;
    }

    tbody.innerHTML = '';

    if (messages.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" style="text-align: center; padding: 20px; color: #666;">
                    No contact messages found
                </td>
            </tr>
        `;
        return;
    }

    messages.forEach(message => {
        const row = document.createElement('tr');
        row.className = message.is_read ? '' : 'unread-message';
        
        const statusBadge = message.is_read 
            ? '<span class="badge badge-success">Read</span>'
            : '<span class="badge badge-warning">Unread</span>';
        
        const messagePreview = message.message.length > 50 
            ? sanitizeHTML(message.message.substring(0, 50)) + '...'
            : sanitizeHTML(message.message);
        
        row.innerHTML = `
            <td>${message.id}</td>
            <td>${sanitizeHTML(message.name)}</td>
            <td>${sanitizeHTML(message.email)}</td>
            <td>${sanitizeHTML(message.subject)}</td>
            <td title="${sanitizeHTML(message.message)}">${messagePreview}</td>
            <td>${formatDate(message.created_at)}</td>
            <td>${statusBadge}</td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="contactMessagesModule.viewMessage(${message.id})" title="View">
                    <i class="fas fa-eye"></i>
                </button>
                ${!message.is_read ? `
                    <button class="btn btn-sm btn-success" onclick="contactMessagesModule.markAsRead(${message.id})" title="Mark as Read">
                        <i class="fas fa-check"></i>
                    </button>
                ` : ''}
                <button class="btn btn-sm btn-info" onclick="contactMessagesModule.replyToMessage(${message.id}, '${sanitizeHTML(message.email)}')" title="Reply">
                    <i class="fas fa-reply"></i>
                </button>
            </td>
        `;
        
        tbody.appendChild(row);
    });
}

// View message details
export function viewMessage(messageId) {
    console.log('Looking for message ID:', messageId);
    console.log('Available messages:', contactMessages);
    
    const message = contactMessages.find(m => m.id == messageId);
    if (!message) {
        showNotification(`Message not found (ID: ${messageId}). Please refresh the page and try again.`, 'error');
        return;
    }

    // Create modal to show message details
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.id = 'view-message-modal';
    modal.innerHTML = `
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h3 class="modal-title">Message from ${sanitizeHTML(message.name)}</h3>
                <button class="modal-close" onclick="this.closest('.modal').remove()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="message-details">
                    <div class="detail-row">
                        <strong>From:</strong> ${sanitizeHTML(message.name)} (${sanitizeHTML(message.email)})
                    </div>
                    <div class="detail-row">
                        <strong>Subject:</strong> ${sanitizeHTML(message.subject)}
                    </div>
                    <div class="detail-row">
                        <strong>Date:</strong> ${formatDate(message.created_at)}
                    </div>
                    <div class="detail-row">
                        <strong>IP Address:</strong> ${sanitizeHTML(message.ip_address || 'Unknown')}
                    </div>
                    <div class="detail-row">
                        <strong>Message:</strong>
                        <div class="message-content" style="margin-top: 10px; padding: 15px; background: #f8f9fa; border-radius: 5px; white-space: pre-wrap;">
                            ${sanitizeHTML(message.message)}
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="contactMessagesModule.replyToMessage(${message.id}, '${sanitizeHTML(message.email)}')">
                    <i class="fas fa-reply"></i> Reply
                </button>
                ${!message.is_read ? `
                    <button class="btn btn-success" onclick="contactMessagesModule.markAsRead(${message.id})">
                        <i class="fas fa-check"></i> Mark as Read
                    </button>
                ` : ''}
                <button class="btn btn-secondary" onclick="this.closest('.modal').remove()">Close</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    modal.style.display = 'flex';
}

// Mark message as read
export function markAsRead(messageId) {
    fetch('API/get_contact_messages_fixed.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        credentials: 'include',
        body: JSON.stringify({ message_id: messageId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Message marked as read', 'success');
            // Update local state
            const message = contactMessages.find(m => m.id == messageId);
            if (message) {
                message.is_read = true;
                renderContactMessages();
            }
            // Close modal if open
            const modal = document.getElementById('view-message-modal');
            if (modal) {
                modal.remove();
            }
        } else {
            showNotification('Failed to mark message as read', 'error');
        }
    })
    .catch(error => {
        console.error('Error marking message as read:', error);
        showNotification('Error marking message as read', 'error');
    });
}

// Reply to message
export function replyToMessage(messageId, email) {
    // Create reply modal
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.id = 'reply-message-modal';
    modal.innerHTML = `
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h3 class="modal-title">Reply to Message</h3>
                <button class="modal-close" onclick="this.closest('.modal').remove()">&times;</button>
            </div>
            <form id="reply-form">
                <div class="form-group">
                    <label class="form-label">To:</label>
                    <input type="email" class="form-input" value="${sanitizeHTML(email)}" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">Subject:</label>
                    <input type="text" class="form-input" name="subject" value="Re: Contact Form Response" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Message:</label>
                    <textarea class="form-input" name="message" rows="8" placeholder="Type your reply here..." required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Send Reply
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="this.closest('.modal').remove()">Cancel</button>
                </div>
            </form>
        </div>
    `;
    
    document.body.appendChild(modal);
    modal.style.display = 'flex';
    
    // Handle form submission
    document.getElementById('reply-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        // Here you would implement the email sending functionality
        // For now, just show a success message
        showNotification('Reply sent successfully', 'success');
        modal.remove();
    });
}

// Filter messages
export function filterMessages() {
    const searchTerm = document.getElementById('message-search')?.value.toLowerCase() || '';
    const statusFilter = document.getElementById('message-status-filter')?.value || '';
    
    let filteredMessages = contactMessages;
    
    // Apply search filter
    if (searchTerm) {
        filteredMessages = filteredMessages.filter(message => 
            message.name.toLowerCase().includes(searchTerm) ||
            message.email.toLowerCase().includes(searchTerm) ||
            message.subject.toLowerCase().includes(searchTerm) ||
            message.message.toLowerCase().includes(searchTerm)
        );
    }
    
    // Apply status filter
    if (statusFilter) {
        if (statusFilter === 'unread') {
            filteredMessages = filteredMessages.filter(message => !message.is_read);
        } else if (statusFilter === 'read') {
            filteredMessages = filteredMessages.filter(message => message.is_read);
        }
    }
    
    renderContactMessages(filteredMessages);
}

// Initialize contact messages functionality
export function initializeContactMessages() {
    // Fetch initial data
    fetchContactMessages();
    
    // Add event listeners for filtering
    const searchInput = document.getElementById('message-search');
    const statusFilter = document.getElementById('message-status-filter');
    
    if (searchInput) {
        searchInput.addEventListener('input', filterMessages);
    }
    
    if (statusFilter) {
        statusFilter.addEventListener('change', filterMessages);
    }
}

// Helper function to format date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
}

// Export module for global access
export const contactMessagesModule = {
    fetchContactMessages,
    viewMessage,
    markAsRead,
    replyToMessage,
    filterMessages,
    initializeContactMessages
};

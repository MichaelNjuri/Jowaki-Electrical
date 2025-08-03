// Utility functions
export function sanitizeHTML(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

export function showSection(sectionId) {
    console.log(`Switching to section: ${sectionId}`);
    const sections = document.querySelectorAll('.content-section');
    const navLinks = document.querySelectorAll('.nav-link');
    const pageTitle = document.getElementById('page-title');

    if (!pageTitle) {
        console.error('page-title element not found!');
        return;
    }

    sections.forEach(section => section.classList.remove('active'));
    const section = document.querySelector(`#${sectionId}`);
    if (section) {
        section.classList.add('active');
    } else {
        console.error(`Section #${sectionId} not found!`);
    }

    navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('data-section') === sectionId) {
            link.classList.add('active');
        }
    });

    pageTitle.textContent = sectionId;
    
    // Load analytics data when switching to analytics section
    if (sectionId === 'analytics') {
        // Import and call analytics function
        import('./analytics.js').then(module => {
            module.fetchAnalyticsData();
        }).catch(error => {
            console.error('Failed to load analytics:', error);
        });
    }
}

export function formatStatusText(status) {
    const statusMap = {
        'pending': 'Pending',
        'processing': 'Processing',
        'shipped': 'Shipped',
        'delivered': 'Delivered',
        'cancelled': 'Cancelled'
    };
    return statusMap[status] || status;
}

import React from 'react';
import { __ } from '@wordpress/i18n';
import SupportHelperActivityPanel from './components/SupportHelperActivityPanel';

console.log('Support Helper React components loaded');
console.log('React version:', React.version);
console.log('ReactDOM available:', typeof ReactDOM !== 'undefined');
console.log('ReactDOM.createRoot available:', typeof ReactDOM.createRoot !== 'undefined');
console.log('ReactDOM.render available:', typeof ReactDOM.render !== 'undefined');

// Icon component for the panel tab
const SupportHelperIcon = () => (
    <svg 
        width="24" 
        height="24" 
        viewBox="0 0 24 24" 
        fill="none" 
        xmlns="http://www.w3.org/2000/svg"
        className="woocommerce-layout__activity-panel-tab-icon"
    >
        <path 
            d="M12 2L2 7V10C2 16 6 21 12 22C18 21 22 16 22 10V7L12 2Z" 
            stroke="currentColor" 
            strokeWidth="2" 
            strokeLinecap="round" 
            strokeLinejoin="round"
        />
        <path 
            d="M12 9L12 13" 
            stroke="currentColor" 
            strokeWidth="2" 
            strokeLinecap="round" 
            strokeLinejoin="round"
        />
        <path 
            d="M12 17H12.01" 
            stroke="currentColor" 
            strokeWidth="2" 
            strokeLinecap="round" 
            strokeLinejoin="round"
        />
    </svg>
);

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, waiting for WooCommerce activity panel...');
    
    // Wait for WooCommerce admin to be fully loaded
    const waitForWooCommerce = setInterval(() => {
        const activityPanel = document.querySelector('#woocommerce-activity-panel');
        if (activityPanel) {
            console.log('WooCommerce activity panel found, initializing Support Helper...');
            clearInterval(waitForWooCommerce);
            initializeSupportHelper();
        }
    }, 100);
    
    // Timeout after 10 seconds
    setTimeout(() => {
        clearInterval(waitForWooCommerce);
        console.warn('WooCommerce activity panel not found after 10 seconds');
    }, 10000);
});

function initializeSupportHelper() {
    // Find the activity panel tabs container
    const tabsContainer = document.querySelector('.woocommerce-layout__activity-panel-tabs');
    if (!tabsContainer) {
        console.warn('WooCommerce activity panel tabs container not found');
        return;
    }

    console.log('Initializing Support Helper tab...');

    // Create our tab button
    const supportTab = document.createElement('button');
    supportTab.type = 'button';
    supportTab.className = 'components-button woocommerce-layout__activity-panel-tab';
    supportTab.id = 'activity-panel-tab-support-helper';
    supportTab.setAttribute('aria-selected', 'false');
    supportTab.setAttribute('role', 'tab');
    supportTab.setAttribute('aria-controls', 'activity-panel-support-helper');
    supportTab.setAttribute('data-testid', 'activity-panel-tab-support-helper');
    
    // Add icon and text
    supportTab.innerHTML = `
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="woocommerce-layout__activity-panel-tab-icon">
            <path d="M12 2L2 7V10C2 16 6 21 12 22C18 21 22 16 22 10V7L12 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M12 9L12 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M12 17H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span>Support Helper</span>
    `;

    // Add click handler
    supportTab.addEventListener('click', function(e) {
        e.preventDefault();
        console.log('Support Helper tab clicked');
        
        // Remove active class from all tabs
        document.querySelectorAll('.woocommerce-layout__activity-panel-tab').forEach(tab => {
            tab.classList.remove('is-active', 'is-opened');
        });
        
        // Add active class to our tab
        supportTab.classList.add('is-active', 'is-opened');
        
        console.log('Tab classes updated, showing panel...');
        
        // Show our panel content
        showSupportHelperPanel();
        
        // Debug: Check if panel is visible
        setTimeout(() => {
            const supportPanel = document.getElementById('activity-panel-support-helper');
            console.log('Panel element:', supportPanel);
            console.log('Panel display style:', supportPanel ? supportPanel.style.display : 'not found');
            console.log('Panel visibility:', supportPanel ? window.getComputedStyle(supportPanel).display : 'not found');
        }, 100);
        
        // Prevent immediate hiding by stopping event propagation
        e.stopPropagation();
    });

    // Add our tab to the container
    tabsContainer.appendChild(supportTab);
    console.log('Support Helper tab added to activity panel');

    // Create our own panel wrapper and content
    const existingWrapper = document.querySelector('.woocommerce-layout__activity-panel-wrapper');
    if (existingWrapper) {
        // Create our own wrapper
        const supportWrapper = document.createElement('div');
        supportWrapper.id = 'support-helper-panel-wrapper';
        supportWrapper.className = 'woocommerce-layout__support-helper-wrapper';
        supportWrapper.style.display = 'none';
        supportWrapper.style.position = 'fixed';
        supportWrapper.style.top = '92px'; // Match WooCommerce admin header height
        supportWrapper.style.right = '0';
        supportWrapper.style.width = '400px';
        supportWrapper.style.height = 'calc(100vh - 92px)'; // Match WooCommerce activity panel height
        supportWrapper.style.backgroundColor = '#fff';
        supportWrapper.style.borderLeft = '1px solid #dcdcde';
        supportWrapper.style.zIndex = '9999';
        supportWrapper.style.boxShadow = '-2px 0 8px rgba(0, 0, 0, 0.1)';
        supportWrapper.style.transform = 'translateX(100%)';
        supportWrapper.style.transition = 'transform 0.3s ease-out';
        
        // Create panel header
        const panelHeader = document.createElement('div');
        panelHeader.className = 'support-helper-panel-header';
        panelHeader.style.padding = '16px 24px';
        panelHeader.style.borderBottom = '1px solid #dcdcde';
        panelHeader.style.display = 'flex';
        panelHeader.style.justifyContent = 'space-between';
        panelHeader.style.alignItems = 'center';
        panelHeader.style.backgroundColor = '#f6f7f7';
        
        const headerTitle = document.createElement('h2');
        headerTitle.textContent = 'Support Helper';
        headerTitle.style.margin = '0';
        headerTitle.style.fontSize = '14px';
        headerTitle.style.fontWeight = '600';
        headerTitle.style.color = '#1d2327';
        headerTitle.style.textTransform = 'uppercase';
        headerTitle.style.letterSpacing = '0.5px';
        
        const closeButton = document.createElement('button');
        closeButton.innerHTML = '×';
        closeButton.style.background = 'none';
        closeButton.style.border = 'none';
        closeButton.style.fontSize = '24px';
        closeButton.style.cursor = 'pointer';
        closeButton.style.color = '#666';
        closeButton.style.padding = '0';
        closeButton.style.width = '24px';
        closeButton.style.height = '24px';
        closeButton.style.display = 'flex';
        closeButton.style.alignItems = 'center';
        closeButton.style.justifyContent = 'center';
        
        closeButton.addEventListener('click', function() {
            hideSupportHelperPanel();
        });
        
        panelHeader.appendChild(headerTitle);
        panelHeader.appendChild(closeButton);
        
        // Create panel content
        const supportPanel = document.createElement('div');
        supportPanel.id = 'support-helper-panel-content';
        supportPanel.className = 'support-helper-panel-content';
        supportPanel.style.flex = '1';
        supportPanel.style.overflowY = 'auto';
        supportPanel.style.padding = '0';
        supportPanel.style.display = 'flex';
        supportPanel.style.flexDirection = 'column';
        
        // Add React component container
        const reactContainer = document.createElement('div');
        reactContainer.id = 'support-helper-react-container';
        reactContainer.style.flex = '1';
        reactContainer.style.overflowY = 'auto';
        reactContainer.style.padding = '16px';
        supportPanel.appendChild(reactContainer);
        
        // Assemble the wrapper
        supportWrapper.appendChild(panelHeader);
        supportWrapper.appendChild(supportPanel);
        
        // Add to body
        document.body.appendChild(supportWrapper);
        console.log('Support Helper panel wrapper created');
    }
}

function showSupportHelperPanel() {
    console.log('Showing Support Helper panel...');
    
    // Show our own panel wrapper
    const supportWrapper = document.getElementById('support-helper-panel-wrapper');
    console.log('Found support wrapper:', supportWrapper);
    if (supportWrapper) {
        console.log('Setting wrapper display to block');
        supportWrapper.style.display = 'block';
        
        // Force a reflow to ensure the display change is applied before animation
        supportWrapper.offsetHeight;
        
        // Add slide-in animation
        supportWrapper.style.transform = 'translateX(0)';
        
        // Render React component
        const reactContainer = document.getElementById('support-helper-react-container');
        if (reactContainer && !reactContainer.hasAttribute('data-rendered')) {
            console.log('Rendering React component...');
            
            // Mark as rendered to prevent re-rendering
            reactContainer.setAttribute('data-rendered', 'true');
            
            // Store the root reference for cleanup
            let reactRoot = null;
            
            // Render the React component
            try {
                // Check React version and use appropriate rendering method
                if (ReactDOM.createRoot) {
                    // React 18+
                    console.log('Using React 18+ createRoot method');
                    reactRoot = ReactDOM.createRoot(reactContainer);
                    reactRoot.render(React.createElement(SupportHelperActivityPanel));
                } else if (ReactDOM.render) {
                    // React 17 and earlier
                    console.log('Using React 17 render method');
                    ReactDOM.render(React.createElement(SupportHelperActivityPanel), reactContainer);
                } else {
                    throw new Error('ReactDOM not available');
                }
                
                // Store the root for cleanup
                reactContainer._reactRoot = reactRoot;
                console.log('React component rendered successfully');
            } catch (error) {
                console.error('Error rendering React component:', error);
                reactContainer.innerHTML = '<div style="padding: 20px; color: #721c24; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px;">Error loading Support Helper panel. Please refresh the page and try again.</div>';
            }
        }
    }
}

// Handle clicks outside our panel to close it
document.addEventListener('click', function(e) {
    const supportWrapper = document.getElementById('support-helper-panel-wrapper');
    const supportTab = document.getElementById('activity-panel-tab-support-helper');
    
    if (supportWrapper && supportWrapper.style.display === 'block') {
        // If click is outside our panel and not on our tab, close the panel
        if (!supportWrapper.contains(e.target) && !supportTab.contains(e.target)) {
            console.log('Click outside panel, closing Support Helper');
            hideSupportHelperPanel();
        }
    }
});

function hideSupportHelperPanel() {
    console.log('Hiding Support Helper panel...');
    const supportWrapper = document.getElementById('support-helper-panel-wrapper');
    if (supportWrapper) {
        // Add slide-out animation
        supportWrapper.style.transform = 'translateX(100%)';
        supportWrapper.style.transition = 'transform 0.3s ease-in';
        
        // Hide after animation
        setTimeout(() => {
            supportWrapper.style.display = 'none';
            console.log('Support Helper panel hidden');
        }, 300);
        
        // Clean up React component with a small delay to prevent flash
        setTimeout(() => {
            const reactContainer = document.getElementById('support-helper-react-container');
            if (reactContainer && reactContainer._reactRoot) {
                try {
                    // Unmount React component
                    if (reactContainer._reactRoot.unmount) {
                        reactContainer._reactRoot.unmount();
                    } else if (ReactDOM.unmountComponentAtNode) {
                        ReactDOM.unmountComponentAtNode(reactContainer);
                    }
                    console.log('React component unmounted');
                } catch (error) {
                    console.error('Error unmounting React component:', error);
                }
                
                // Clear the container
                reactContainer.innerHTML = '';
                reactContainer.removeAttribute('data-rendered');
                reactContainer._reactRoot = null;
            }
        }, 150); // Small delay to prevent flash
    }
}

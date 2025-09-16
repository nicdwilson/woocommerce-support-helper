import React, { useState } from 'react';

const SupportHelperPanelSimple = () => {
    const [isLoading, setIsLoading] = useState(false);
    const [message, setMessage] = useState('');

    const handleTestClick = () => {
        setMessage('Button clicked! React component is working.');
    };

    const handleExportTest = async () => {
        setIsLoading(true);
        setMessage('Testing export functionality...');
        
        try {
            // Use WooCommerce's blueprint export endpoint directly
            const response = await fetch('/wp-json/wc-admin/blueprint/export?_locale=user', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': window.wcSupportHelper ? window.wcSupportHelper.nonce : ''
                },
                body: JSON.stringify({
                    steps: {
                        settings: [
                            "setWCSettingsGeneral",
                            "setWCSettingsProducts", 
                            "setWCSettingsTax",
                            "setWCShipping",
                            "wcPaymentGateways",
                            "setWCSettingsAccount",
                            "setWCSettingsEmails",
                            "setWCSettingsIntegrations",
                            "setWCSettingsSiteVisibility",
                            "setWCSettingsAdvanced"
                        ],
                        plugins: [
                            "woocommerce-pinterest/woocommerce-pinterest.php",
                            "pu2-devtools-trunk/pu2-devtools.php",
                            "woocommerce/woocommerce.php",
                            "woocommerce-gateway-stripe/woocommerce-gateway-stripe.php",
                            "woocommerce-support-helper/woocommerce-support-helper.php",
                            "woocommerce-shipping-usps/woocommerce-shipping-usps.php",
                            "woocommerce-payments/woocommerce-payments.php",
                            "wp-crontrol/wp-crontrol.php"
                        ],
                        themes: ["storefront"],
                        plugin_settings: ["woocommerce-shipping-usps"]
                    }
                })
            });
            
            if (response.ok) {
                const data = await response.json();
                setMessage(`Export successful! Response: ${JSON.stringify(data, null, 2)}`);
            } else {
                const errorData = await response.json();
                setMessage(`Export failed with status: ${response.status} - ${errorData.message || 'Unknown error'}`);
            }
        } catch (error) {
            setMessage(`Error: ${error.message}`);
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <div style={{ padding: '20px', fontFamily: 'Arial, sans-serif' }}>
            <h2 style={{ color: '#007cba', marginBottom: '20px' }}>Support Helper Panel</h2>
            
            <div style={{ marginBottom: '20px' }}>
                <p>This is a simplified React component to test the integration.</p>
            </div>

            <div style={{ marginBottom: '20px' }}>
                <button 
                    onClick={handleTestClick}
                    style={{
                        background: '#007cba',
                        color: 'white',
                        border: 'none',
                        padding: '10px 20px',
                        borderRadius: '4px',
                        cursor: 'pointer',
                        marginRight: '10px'
                    }}
                >
                    Test Button
                </button>
                
                <button 
                    onClick={handleExportTest}
                    disabled={isLoading}
                    style={{
                        background: isLoading ? '#ccc' : '#28a745',
                        color: 'white',
                        border: 'none',
                        padding: '10px 20px',
                        borderRadius: '4px',
                        cursor: isLoading ? 'not-allowed' : 'pointer'
                    }}
                >
                    {isLoading ? 'Testing...' : 'Test Export'}
                </button>
            </div>

            {message && (
                <div style={{
                    padding: '15px',
                    background: '#f8f9fa',
                    border: '1px solid #dee2e6',
                    borderRadius: '4px',
                    marginTop: '20px'
                }}>
                    <strong>Status:</strong> {message}
                </div>
            )}

            <div style={{ marginTop: '20px', fontSize: '12px', color: '#666' }}>
                <p><strong>Debug Info:</strong></p>
                <ul>
                    <li>React Version: {React.version}</li>
                    <li>wcSupportHelper available: {window.wcSupportHelper ? 'Yes' : 'No'}</li>
                    <li>API URL: {window.wcSupportHelper ? window.wcSupportHelper.apiUrl : 'Not available'}</li>
                    <li>Nonce: {window.wcSupportHelper ? (window.wcSupportHelper.nonce ? 'Available' : 'Not available') : 'Not available'}</li>
                </ul>
            </div>
        </div>
    );
};

export default SupportHelperPanelSimple;

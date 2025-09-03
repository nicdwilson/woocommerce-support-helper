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
            // Test if the API endpoint is available
            const response = await fetch((window.wcSupportHelper ? window.wcSupportHelper.apiUrl : '') + 'export', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': window.wcSupportHelper ? window.wcSupportHelper.nonce : ''
                },
                body: JSON.stringify({ type: 'blueprint' })
            });
            
            if (response.ok) {
                const data = await response.json();
                setMessage(`Export successful! Download URL: ${data.download_url}`);
            } else {
                setMessage(`Export failed with status: ${response.status}`);
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

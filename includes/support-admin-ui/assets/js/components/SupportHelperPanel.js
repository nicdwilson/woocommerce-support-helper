import React, { useState, useEffect } from 'react';
import { Card, CardBody, CardHeader } from '@wordpress/components';
import { Text, Button, Spinner } from '@woocommerce/components';
import { __ } from '@wordpress/i18n';

const SupportHelperPanel = () => {
    const [isLoading, setIsLoading] = useState(false);
    const [exportData, setExportData] = useState(null);
    const [activeTab, setActiveTab] = useState('export');
    const [error, setError] = useState(null);

    const handleExport = async (type) => {
        setIsLoading(true);
        try {
            const response = await fetch((window.wcSupportHelper ? window.wcSupportHelper.apiUrl : '') + 'export', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': window.wcSupportHelper ? window.wcSupportHelper.nonce : ''
                },
                body: JSON.stringify({ type })
            });
            
            if (response.ok) {
                const data = await response.json();
                setExportData(data);
            } else {
                throw new Error('Export failed');
            }
        } catch (error) {
            console.error('Export error:', error);
            setError(error.message);
        } finally {
            setIsLoading(false);
        }
    };

    const renderExportTab = () => (
        <div className="woocommerce-support-helper-export">
            <Text variant="body" as="p">
                {__('Export your WooCommerce configuration and data for support purposes.', 'woocommerce-support-helper')}
            </Text>
            
            <div className="woocommerce-support-helper-export-buttons">
                <Button 
                    isPrimary 
                    onClick={() => handleExport('blueprint')}
                    isBusy={isLoading}
                    disabled={isLoading}
                >
                    {__('Export Blueprint', 'woocommerce-support-helper')}
                </Button>
                
                <Button 
                    isSecondary 
                    onClick={() => handleExport('shipping')}
                    isBusy={isLoading}
                    disabled={isLoading}
                >
                    {__('Export Shipping Methods', 'woocommerce-support-helper')}
                </Button>
            </div>

            {error && (
                <div className="woocommerce-support-helper-error" style={{ color: '#721c24', background: '#f8d7da', border: '1px solid #f5c6cb', borderRadius: '4px', padding: '10px', margin: '10px 0' }}>
                    <Text variant="body" as="p">
                        {__('Error:', 'woocommerce-support-helper')} {error}
                    </Text>
                </div>
            )}

            {exportData && (
                <div className="woocommerce-support-helper-export-result">
                    <Card>
                        <CardHeader>
                            <Text variant="title.small">
                                {__('Export Complete', 'woocommerce-support-helper')}
                            </Text>
                        </CardHeader>
                        <CardBody>
                            <Text variant="body" as="p">
                                {__('Your export is ready for download.', 'woocommerce-support-helper')}
                            </Text>
                            <Button 
                                isPrimary 
                                href={exportData.download_url}
                                target="_blank"
                            >
                                {__('Download Export', 'woocommerce-support-helper')}
                            </Button>
                        </CardBody>
                    </Card>
                </div>
            )}
        </div>
    );

    const renderSettingsTab = () => (
        <div className="woocommerce-support-helper-settings">
            <Text variant="body" as="p">
                {__('Configure your support helper settings.', 'woocommerce-support-helper')}
            </Text>
            {/* Add settings form here */}
        </div>
    );

    return (
        <div className="woocommerce-support-helper-panel">
            <div className="woocommerce-support-helper-tabs">
                <Button 
                    isPrimary={activeTab === 'export'}
                    isSecondary={activeTab !== 'export'}
                    onClick={() => setActiveTab('export')}
                >
                    {__('Export', 'woocommerce-support-helper')}
                </Button>
                <Button 
                    isPrimary={activeTab === 'settings'}
                    isSecondary={activeTab !== 'settings'}
                    onClick={() => setActiveTab('settings')}
                >
                    {__('Settings', 'woocommerce-support-helper')}
                </Button>
            </div>

            <div className="woocommerce-support-helper-content">
                {isLoading && <Spinner />}
                {activeTab === 'export' && renderExportTab()}
                {activeTab === 'settings' && renderSettingsTab()}
            </div>
        </div>
    );
};

export default SupportHelperPanel;

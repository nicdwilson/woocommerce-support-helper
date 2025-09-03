import React, { lazy, Suspense } from 'react';
import { Spinner } from '@woocommerce/components';
import { __ } from '@wordpress/i18n';

// Lazy load the main panel component
const SupportHelperPanel = lazy(() => import('./SupportHelperPanelSimple'));

const SupportHelperActivityPanel = () => {
    return (
        <div className="woocommerce-support-helper-activity-panel">
            <Suspense fallback={<Spinner />}>
                <SupportHelperPanel />
            </Suspense>
        </div>
    );
};

export default SupportHelperActivityPanel;

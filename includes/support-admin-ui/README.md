# Support Admin UI - Modular Interface System

The Support Admin UI module provides a modular system for displaying different interfaces in the WooCommerce activity panel. This allows other plugins to add their own interfaces alongside the export functionality.

## Overview

The system consists of:
- **Interface Container**: A container that holds all interfaces
- **Individual Interfaces**: Separate interface components that can be added by different plugins
- **Filter System**: A WordPress filter system for extensibility

## Adding Your Own Interface

### Method 1: Using the Filter

The simplest way to add an interface is by using the `wc_support_helper_interfaces` filter:

```php
add_filter( 'wc_support_helper_interfaces', function( $interfaces ) {
    $interfaces[] = '<section class="woocommerce-inbox-message plain">
        <div class="woocommerce-inbox-message__wrapper">
            <div class="woocommerce-inbox-message__content">
                <span class="woocommerce-inbox-message__date">My Plugin</span>
                <h4 class="woocommerce-inbox-message__title">
                    <span>My Interface Title</span>
                </h4>
                <div class="woocommerce-inbox-message__text">
                    <span>My interface description and content.</span>
                </div>
            </div>
            <div class="woocommerce-inbox-message__actions">
                <button type="button" class="components-button is-primary">
                    <span>My Action</span>
                </button>
            </div>
        </div>
    </section>';
    
    return $interfaces;
});
```

### Method 2: Using the Template Helper

For a more structured approach, you can use the template helper:

```php
$support_admin_ui = \WooCommerceSupportHelper\SupportAdminUI\Support_Admin_UI::get_instance();

if ( $support_admin_ui ) {
    $actions_html = '<button type="button" class="components-button is-primary">
        <span>My Action</span>
    </button>';
    
    $interface_html = $support_admin_ui->get_interface_template(
        'My Plugin',           // Date label
        'My Interface Title',  // Title
        'My description text', // Description
        $actions_html          // Actions HTML
    );
    
    $support_admin_ui->register_interface( $interface_html );
}
```

### Method 3: Direct Registration

You can also register an interface directly:

```php
$support_admin_ui = \WooCommerceSupportHelper\SupportAdminUI\Support_Admin_UI::get_instance();

if ( $support_admin_ui ) {
    $my_interface_html = '<section class="woocommerce-inbox-message plain">
        <!-- Your interface HTML here -->
    </section>';
    
    $support_admin_ui->register_interface( $my_interface_html );
}
```

## Interface Structure

Each interface should follow the WooCommerce inbox message structure:

```html
<section class="woocommerce-inbox-message plain">
    <div class="woocommerce-inbox-message__wrapper">
        <div class="woocommerce-inbox-message__content">
            <span class="woocommerce-inbox-message__date">Date Label</span>
            <h4 class="woocommerce-inbox-message__title">
                <span>Interface Title</span>
            </h4>
            <div class="woocommerce-inbox-message__text">
                <span>Description and content</span>
            </div>
        </div>
        <div class="woocommerce-inbox-message__actions">
            <!-- Action buttons -->
        </div>
    </div>
</section>
```

## Best Practices

1. **Use WooCommerce Classes**: Leverage existing WooCommerce CSS classes for consistency
2. **Keep Interfaces Focused**: Each interface should have a single, clear purpose
3. **Provide Actions**: Include relevant action buttons in the actions section
4. **Use Proper Escaping**: Always escape user data and use proper HTML structure
5. **Test Responsiveness**: Ensure your interface works well on mobile devices

## Available CSS Classes

- `components-button is-primary` - Primary action buttons
- `components-button is-secondary` - Secondary action buttons
- `components-text` - Text styling
- `woocommerce-inbox-message__date` - Date/timestamp styling
- `woocommerce-inbox-message__title` - Title styling
- `woocommerce-inbox-message__text` - Content text styling
- `woocommerce-inbox-message__actions` - Action buttons container

## Example: Complete Plugin Integration

Here's a complete example of how a plugin might integrate with the Support Admin UI:

```php
class My_Plugin_Support_Interface {
    
    public function __construct() {
        add_action( 'init', array( $this, 'register_support_interface' ) );
    }
    
    public function register_support_interface() {
        $support_admin_ui = \WooCommerceSupportHelper\SupportAdminUI\Support_Admin_UI::get_instance();
        
        if ( $support_admin_ui ) {
            $actions_html = '<button type="button" class="components-button is-primary" id="my-plugin-action">
                <span>Run My Action</span>
            </button>';
            
            $interface_html = $support_admin_ui->get_interface_template(
                'My Plugin',
                'Plugin Status Check',
                'Check the status of your plugin configuration and get recommendations.',
                $actions_html
            );
            
            $support_admin_ui->register_interface( $interface_html );
        }
    }
}

new My_Plugin_Support_Interface();
```

This modular system allows for easy extensibility while maintaining consistency with WooCommerce's design patterns.

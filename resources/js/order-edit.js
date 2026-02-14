/**
 * Order Edit Vue Application
 * 
 * This is the entry point for the Vue-based order edit interface.
 * It mounts the OrderEdit component and handles all interactions.
 */

import { createApp } from 'vue'
import OrderEdit from './components/orders/OrderEdit.vue'

// Create Vue application
const app = createApp({
    // Global event handlers for blade template integration
    methods: {
        handlePrintOrder(event) {
            const orderId = event.detail;
            window.open(`/orders/${orderId}/print`, '_blank');
        },
        handleProcessPayment(event) {
            const orderId = event.detail;
            window.location.href = `/orders/${orderId}/payment`;
        },
        handleCancelOrder(event) {
            const orderId = event.detail;
            window.location.href = "/orders";
        }
    }
})

// Register components
app.component('order-edit', OrderEdit)

// Mount to DOM
app.mount('#order-edit-app')

// Export for potential external use
export default app
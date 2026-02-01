/**
 * Order Edit Vue Application
 * 
 * This is the entry point for the Vue-based order edit interface.
 * It mounts the OrderEdit component and handles all interactions.
 */

import { createApp } from 'vue'
import OrderEdit from './components/orders/OrderEdit.vue'

// Create Vue application
const app = createApp({})

// Register components
app.component('order-edit', OrderEdit)

// Mount to DOM
app.mount('#order-edit-app')

// Export for potential external use
export default app
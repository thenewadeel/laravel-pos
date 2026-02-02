/**
 * Orders Workspace Vue Application
 * 
 * This is the entry point for the tabbed orders workspace interface.
 * It allows waiters to manage multiple table orders simultaneously.
 */

import { createApp } from 'vue'
import OrdersWorkspace from './components/orders/OrdersWorkspace.vue'

// Create Vue application
const app = createApp({})

// Register components
app.component('orders-workspace', OrdersWorkspace)

// Mount to DOM
app.mount('#orders-workspace-app')

// Export for potential external use
export default app
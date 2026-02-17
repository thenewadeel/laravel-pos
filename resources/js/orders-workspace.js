/**
 * Orders Workspace Vue Application
 * 
 * This is the entry point for the tabbed orders workspace interface.
 * It allows waiters to manage multiple table orders simultaneously.
 */

import { createApp } from 'vue'
import OrdersWorkspace from './components/orders/OrdersWorkspace.vue'
import offlineManager from './offline-manager'

// Make offline manager available globally for service worker communication
window.offlineManager = offlineManager

// Create Vue application
const app = createApp({})

// Register components
app.component('orders-workspace', OrdersWorkspace)

// Mount to DOM
app.mount('#orders-workspace-app')

// Register Service Worker for offline support
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/service-worker.js')
      .then((registration) => {
        console.log('[Orders Workspace] Service Worker registered:', registration.scope)
        
        // Listen for service worker messages
        navigator.serviceWorker.addEventListener('message', (event) => {
          if (event.data && event.data.type === 'SYNC_ORDERS') {
            console.log('[Orders Workspace] Received sync message from Service Worker')
            // The offline manager will handle the actual sync
            if (window.offlineManager) {
              window.offlineManager.syncPendingOrders()
            }
          }
        })
      })
      .catch((error) => {
        console.error('[Orders Workspace] Service Worker registration failed:', error)
      })
  })
}

// Export for potential external use
export default app
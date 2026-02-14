/**
 * Floor Restaurant Vue Application
 * 
 * Single-page floor and restaurant management interface
 * for managers and cashiers.
 */

import { createApp } from 'vue'
import FloorRestaurantView from './components/floor/FloorRestaurantView.vue'

// Create Vue application
const app = createApp({})

// Register components
app.component('floor-restaurant-view', FloorRestaurantView)

// Mount to DOM
app.mount('#floor-restaurant-app')

// Export for potential external use
export default app
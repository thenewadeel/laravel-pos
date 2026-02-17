<template>
  <div class="orders-workspace">
    <!-- Network Status Bar -->
    <NetworkStatus :pending-count="offlinePendingCount" />
    
    <!-- Orders Tabs Header -->
    <div class="orders-tabs-header">
      <div class="tabs-container">
        <div 
          v-for="(order, index) in orders" 
          :key="order.id || order.tempId"
          class="order-tab"
          :class="{
            'active': activeOrderIndex === index,
            'synced': order.syncStatus === 'synced',
            'pending': order.syncStatus === 'pending',
            'syncing': order.syncStatus === 'syncing',
            'error': order.syncStatus === 'error'
          }"
          @click="switchOrder(index)"
        >
          <div class="tab-content">
            <span class="tab-number">{{ order.table_number || 'New' }}</span>
            <span class="tab-status-icon">
              <i v-if="order.syncStatus === 'synced'" class="fas fa-check-circle" title="Synced"></i>
              <i v-else-if="order.syncStatus === 'pending'" class="fas fa-clock" title="Pending Sync"></i>
              <i v-else-if="order.syncStatus === 'syncing'" class="fas fa-sync fa-spin" title="Syncing..."></i>
              <i v-else-if="order.syncStatus === 'error'" class="fas fa-exclamation-circle" title="Sync Error"></i>
            </span>
          </div>
          <button 
            v-if="orders.length > 1" 
            class="tab-close"
            @click.stop="closeOrder(index)"
            title="Close order"
          >
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <!-- Add New Order Button -->
        <button class="add-order-btn" @click="createNewOrder" title="New Order">
          <i class="fas fa-plus"></i>
        </button>
      </div>
      
      <!-- Sync All Button -->
      <div class="sync-controls">
        <div v-if="pendingCount > 0" class="pending-badge">
          {{ pendingCount }} pending
        </div>
        <button 
          class="btn-sync-all"
          :class="{ 'syncing': isSyncing }"
          :disabled="isSyncing || pendingCount === 0"
          @click="syncAllOrders"
        >
          <i class="fas" :class="isSyncing ? 'fa-sync fa-spin' : 'fa-cloud-upload-alt'"></i>
          {{ isSyncing ? 'Syncing...' : 'Sync All' }}
        </button>
      </div>
    </div>

    <!-- Active Order Edit Area -->
    <div class="order-edit-wrapper">
      <order-edit
        v-if="activeOrder"
        :key="activeOrder.id || activeOrder.tempId"
        :order="activeOrder"
        :user="user"
        :user-shops="userShops"
        :categories="categories"
        :discounts="discounts"
        :customers="customers"
        :tables="tables"
        @order-updated="handleOrderUpdated"
        @print-order="handlePrintOrder"
        @process-payment="handleProcessPayment"
        @cancel-order="handleCancelOrder"
      />
    </div>

    <!-- Sync Status Panel -->
    <div v-if="showSyncPanel" class="sync-panel">
      <div class="sync-panel-header">
        <h4><i class="fas fa-sync"></i> Sync Status</h4>
        <button class="btn-close" @click="showSyncPanel = false">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <div class="sync-panel-body">
        <div 
          v-for="order in orders" 
          :key="order.id || order.tempId"
          class="sync-item"
          :class="order.syncStatus"
        >
          <span class="sync-table">Table {{ order.table_number || 'New' }}</span>
          <span class="sync-status">
            <StatusBadge 
              :status="order.syncStatus" 
              type="sync"
              size="small"
              :show-label="true"
            />
          </span>
          <span v-if="order.lastSyncError" class="sync-error">
            {{ order.lastSyncError }}
          </span>
        </div>
      </div>
    </div>

    <!-- Floating Sync Button (Mobile) -->
    <button 
      v-if="pendingCount > 0"
      class="fab-sync"
      :class="{ 'syncing': isSyncing }"
      :disabled="isSyncing"
      @click="syncAllOrders"
    >
      <i class="fas" :class="isSyncing ? 'fa-sync fa-spin' : 'fa-cloud-upload-alt'"></i>
      <span class="fab-badge">{{ pendingCount }}</span>
    </button>
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import OrderEdit from './OrderEdit.vue'
import StatusBadge from '../business/StatusBadge.vue'
import NetworkStatus from './NetworkStatus.vue'
import offlineManager from '../../offline-manager'

export default {
  name: 'OrdersWorkspace',

  components: {
    OrderEdit,
    StatusBadge,
    NetworkStatus
  },

  props: {
    ordersList: {
      type: Array,
      default: () => []
    },
    initialOrder: {
      type: Object,
      default: null
    },
    user: {
      type: Object,
      required: true
    },
    userShops: {
      type: Array,
      default: () => []
    },
    categories: {
      type: Array,
      default: () => []
    },
    discounts: {
      type: Array,
      default: () => []
    },
    customers: {
      type: Array,
      default: () => []
    },
    tables: {
      type: Array,
      default: () => []
    }
  },

  emits: ['print-order', 'process-payment', 'cancel-order'],

  setup(props, { emit }) {
    // State
    const orders = ref([])
    const activeOrderIndex = ref(0)
    const isSyncing = ref(false)
    const showSyncPanel = ref(false)
    const tempIdCounter = ref(1)
    const isOnline = ref(offlineManager.getNetworkStatus())
    let networkUnsubscribe = null

    // Safe notification helper
    const notify = {
      success: (message) => {
        if (typeof window.toastr !== 'undefined') {
          window.notify.success(message)
        } else {
          console.log('✓ Success:', message)
        }
      },
      error: (message) => {
        if (typeof window.toastr !== 'undefined') {
          window.notify.error(message)
        } else {
          console.error('✗ Error:', message)
          alert('Error: ' + message)
        }
      },
      warning: (message) => {
        if (typeof window.toastr !== 'undefined') {
          window.notify.warning(message)
        } else {
          console.warn('⚠ Warning:', message)
        }
      },
      info: (message) => {
        if (typeof window.toastr !== 'undefined') {
          window.notify.info(message)
        } else {
          console.log('ℹ Info:', message)
        }
      }
    }

    // CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || ''

    // Computed
    const activeOrder = computed(() => orders.value[activeOrderIndex.value])

    const pendingCount = computed(() => {
      return orders.value.filter(o => o.syncStatus === 'pending' || o.syncStatus === 'error').length
    })

    const syncedCount = computed(() => {
      return orders.value.filter(o => o.syncStatus === 'synced').length
    })

    const offlinePendingCount = computed(() => {
      return offlineManager.getPendingCount()
    })

    // Watch for order changes and save to offline storage
    const saveOrdersToStorage = () => {
      offlineManager.saveOrders(orders.value)
    }

    // Initialize with all today's orders
    onMounted(() => {
      // Subscribe to network status changes
      networkUnsubscribe = offlineManager.subscribe((online) => {
        isOnline.value = online
        if (online) {
          notify.success('Connection restored. You can now sync orders.')
        } else {
          notify.warning('You are offline. Orders will be saved locally.')
        }
      })

      // First try to load from offline storage
      const storedOrders = offlineManager.loadOrders()
      
      if (storedOrders && storedOrders.length > 0) {
        // Filter out closed/cancelled orders from storage first
        const activeStoredOrders = storedOrders.filter(o => o.state !== 'closed' && o.state !== 'cancelled')
        
        // Start with stored orders, but update with fresh server data for synced orders
        const mergedOrders = [...activeStoredOrders]
        
        if (props.ordersList && props.ordersList.length > 0) {
          // Update existing orders with fresh data from server
          props.ordersList.forEach(serverOrder => {
            const index = mergedOrders.findIndex(o => o.id === serverOrder.id)
            if (index !== -1) {
              // Always update with server data (server is source of truth for state)
              mergedOrders[index] = { 
                ...serverOrder, 
                syncStatus: 'synced', 
                tempId: null 
              }
            } else if (index === -1) {
              // Add new orders from server that aren't in storage
              mergedOrders.push({ 
                ...serverOrder, 
                syncStatus: 'synced', 
                tempId: null 
              })
            }
          })
        }
        
        // Remove closed orders from local storage (server filters them out)
        const activeOrders = mergedOrders.filter(o => o.state !== 'closed' && o.state !== 'cancelled')
        
        orders.value = activeOrders
        saveOrdersToStorage()
        notify.info(`${activeOrders.length} orders loaded (merged with offline storage)`)
      } else if (props.ordersList && props.ordersList.length > 0) {
        // Filter out closed/cancelled orders and convert to local format
        orders.value = props.ordersList
          .filter(order => order.state !== 'closed' && order.state !== 'cancelled')
          .map(order => ({
            ...order,
            syncStatus: 'synced',
            tempId: null
          }))
        
        // Set active order to the initialOrder if provided, otherwise first
        if (props.initialOrder && props.initialOrder.id) {
          const index = orders.value.findIndex(o => o.id === props.initialOrder.id)
          if (index >= 0) {
            activeOrderIndex.value = index
          } else {
            activeOrderIndex.value = 0
          }
        } else {
          activeOrderIndex.value = 0
        }
        
        // Save to offline storage
        saveOrdersToStorage()
      } else if (props.initialOrder && props.initialOrder.id) {
        // Fallback to single initial order - only if not closed/cancelled
        if (props.initialOrder.state !== 'closed' && props.initialOrder.state !== 'cancelled') {
          const initial = { ...props.initialOrder, syncStatus: 'synced', tempId: null }
          orders.value = [initial]
          saveOrdersToStorage()
        }
      }
      
      // Try to sync any pending orders if online
      if (offlineManager.getNetworkStatus()) {
        offlineManager.syncPendingOrders().then(result => {
          if (result.synced > 0) {
            notify.success(`${result.synced} offline orders synced successfully`)
          }
        })
      }
    })
    
    // Cleanup on unmount
    onUnmounted(() => {
      if (networkUnsubscribe) {
        networkUnsubscribe()
      }
      // Save orders before leaving
      saveOrdersToStorage()
    })

    // Methods
    const generateTempId = () => {
      return `temp-${Date.now()}-${tempIdCounter.value++}`
    }

    const createNewOrder = () => {
      const newOrder = {
        tempId: generateTempId(),
        id: null,
        POS_number: 'NEW-' + Date.now().toString().slice(-4),
        table_number: '',
        waiter_name: props.user.first_name + ' ' + props.user.last_name,
        type: 'dine-in',
        state: 'preparing',
        notes: '',
        items: [],
        customer: null,
        customer_id: null,
        // shop_id is intentionally NOT set - it's derived from table's floor in backend
        user_id: props.user.id,
        syncStatus: 'pending',
        lastSyncError: null,
        created_at: new Date().toISOString()
      }
      
      orders.value.push(newOrder)
      activeOrderIndex.value = orders.value.length - 1
      
      // Save to offline storage
      saveOrdersToStorage()
      
      // If offline, queue for later sync
      if (!isOnline.value) {
        offlineManager.queueOperation({
          type: 'create_order',
          url: '/api/v1/orders',
          method: 'POST',
          data: {
            table_number: newOrder.table_number,
            waiter_name: newOrder.waiter_name,
            type: newOrder.type,
            customer_id: newOrder.customer_id,
            notes: newOrder.notes,
            items: []
          }
        })
        notify.info('Order saved offline. Will sync when connection is restored.')
      }
    }

    const switchOrder = (index) => {
      activeOrderIndex.value = index
    }

    const closeOrder = async (index) => {
      const order = orders.value[index]
      
      // If order has items and not synced, confirm before closing
      if (order.items.length > 0 && order.syncStatus === 'pending') {
        if (!confirm('This order has unsaved items. Close anyway?')) {
          return
        }
      }

      // If order is synced and has ID, we might want to actually close it on server
      if (order.id && order.syncStatus === 'synced') {
        // Optionally close the order on the server
        // await closeOrderOnServer(order.id)
      }

      orders.value.splice(index, 1)
      
      // Adjust active index if needed
      if (activeOrderIndex.value >= orders.value.length) {
        activeOrderIndex.value = orders.value.length - 1
      }
      if (activeOrderIndex.value < 0) {
        activeOrderIndex.value = 0
      }
    }

    const handleOrderUpdated = (updatedOrder) => {
      // Check if this is a new order being created (has isNew flag or no matching id/tempId)
      let index = -1;
      
      if (updatedOrder.id) {
        // First try to find by id
        index = orders.value.findIndex(o => o.id === updatedOrder.id);
      }
      
      // If not found by id, try tempId (for orders created locally)
      if (index === -1 && updatedOrder.tempId) {
        index = orders.value.findIndex(o => o.tempId === updatedOrder.tempId);
      }
      
      // If still not found and this is a new order, update the active order
      if (index === -1 && updatedOrder.isNew) {
        index = activeOrderIndex.value;
      }
      
      if (index !== -1) {
        // Check if order is being closed/cancelled - remove it from workspace
        if (updatedOrder.state === 'closed' || updatedOrder.state === 'cancelled') {
          orders.value.splice(index, 1)
          // Adjust active index if needed
          if (activeOrderIndex.value >= orders.value.length) {
            activeOrderIndex.value = orders.value.length - 1
          }
          if (activeOrderIndex.value < 0) {
            activeOrderIndex.value = 0
          }
          saveOrdersToStorage()
          return
        }
        
        // Merge the updated data, preserving local tempId if it exists
        orders.value[index] = { 
          ...orders.value[index], 
          ...updatedOrder,
          // Ensure we keep the original tempId if it was a local order
          tempId: orders.value[index].tempId || null,
          syncStatus: 'synced'
        };
        
        // If this was a new order, clear the tempId and set syncStatus
        if (updatedOrder.isNew) {
          orders.value[index].tempId = null;
          orders.value[index].syncStatus = 'synced';
        }
        
        // Save to offline storage after any update
        saveOrdersToStorage()
      }
    }

    const syncAllOrders = async () => {
      if (isSyncing.value) return
      
      isSyncing.value = true
      showSyncPanel.value = true
      
      const pendingOrders = orders.value.filter(o => 
        o.syncStatus === 'pending' || o.syncStatus === 'error'
      )

      for (const order of pendingOrders) {
        order.syncStatus = 'syncing'
        order.lastSyncError = null
        
        try {
          if (order.id) {
            // Update existing order - queue for sync
            await queueOrderUpdate(order)
          } else {
            // Create new order - queue for sync
            await queueOrderCreate(order)
          }
          
          order.syncStatus = 'synced'
        } catch (error) {
          order.syncStatus = 'error'
          order.lastSyncError = error.message || 'Sync failed'
          console.error('Sync error:', error)
        }
      }
      
      // Now execute the sync through offline manager
      const result = await offlineManager.syncPendingOrders()
      
      isSyncing.value = false
      
      // Show success message based on results
      if (result.failed === 0) {
        notify.success('All orders synced successfully!')
      } else {
        notify.warning(`${result.failed} orders failed to sync`)
      }
    }

    const queueOrderCreate = async (order) => {
      // Queue order creation for sync
      offlineManager.queueOperation({
        type: 'create_order',
        url: '/api/v1/orders',
        method: 'POST',
        data: {
          table_number: order.table_number,
          waiter_name: order.waiter_name,
          type: order.type,
          customer_id: order.customer_id,
          notes: order.notes,
          items: order.items.map(item => ({
            product_id: item.product_id,
            quantity: item.quantity,
            unit_price: item.unit_price
          }))
        }
      })
      
      // Simulate immediate success for better UX
      // The actual sync happens in the background
      order.tempId = `queued-${Date.now()}`
    }

    const queueOrderUpdate = async (order) => {
      // Queue order update for sync
      offlineManager.queueOperation({
        type: 'update_order',
        url: `/api/v1/orders/${order.id}`,
        method: 'PUT',
        data: {
          table_number: order.table_number,
          waiter_name: order.waiter_name,
          type: order.type,
          customer_id: order.customer_id,
          notes: order.notes
        }
      })
    }

    const handlePrintOrder = (orderId) => {
      emit('print-order', orderId)
    }

    const handleProcessPayment = (orderId) => {
      emit('process-payment', orderId)
    }

    const handleCancelOrder = (orderId) => {
      // Remove from local orders
      const index = orders.value.findIndex(o => o.id === orderId)
      if (index !== -1) {
        orders.value.splice(index, 1)
      }
      emit('cancel-order', orderId)
    }

    return {
      orders,
      activeOrderIndex,
      activeOrder,
      isSyncing,
      showSyncPanel,
      pendingCount,
      syncedCount,
      isOnline,
      offlinePendingCount,
      createNewOrder,
      switchOrder,
      closeOrder,
      handleOrderUpdated,
      syncAllOrders,
      handlePrintOrder,
      handleProcessPayment,
      handleCancelOrder
    }
  }
}
</script>

<style scoped>
.orders-workspace {
  display: flex;
  flex-direction: column;
  height: calc(100vh - 180px);
  background: #f5f5f5;
}

/* Tabs Header */
.orders-tabs-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #fff;
  border-bottom: 2px solid #dee2e6;
  padding: 8px 12px;
  gap: 12px;
}

.tabs-container {
  display: flex;
  gap: 4px;
  overflow-x: auto;
  flex: 1;
  padding-bottom: 4px;
}

/* Order Tab */
.order-tab {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 12px;
  background: #e9ecef;
  border: 2px solid transparent;
  border-radius: 6px 6px 0 0;
  cursor: pointer;
  min-width: 100px;
  max-width: 150px;
  transition: all 0.2s;
  position: relative;
}

.order-tab:hover {
  background: #dee2e6;
}

.order-tab.active {
  background: #fff;
  border-color: #007bff;
  border-bottom-color: #fff;
  margin-bottom: -2px;
}

/* Sync Status Colors */
.order-tab.synced {
  border-top: 3px solid #28a745;
}

.order-tab.pending {
  border-top: 3px solid #ffc107;
}

.order-tab.syncing {
  border-top: 3px solid #17a2b8;
}

.order-tab.error {
  border-top: 3px solid #dc3545;
}

.tab-content {
  display: flex;
  align-items: center;
  gap: 6px;
  flex: 1;
  overflow: hidden;
}

.tab-number {
  font-weight: 600;
  font-size: 13px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.tab-status-icon {
  font-size: 12px;
}

.tab-status-icon .fa-check-circle {
  color: #28a745;
}

.tab-status-icon .fa-clock {
  color: #ffc107;
}

.tab-status-icon .fa-sync {
  color: #17a2b8;
}

.tab-status-icon .fa-exclamation-circle {
  color: #dc3545;
}

.tab-close {
  width: 18px;
  height: 18px;
  border: none;
  background: transparent;
  color: #6c757d;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  font-size: 10px;
  opacity: 0.6;
  transition: all 0.2s;
}

.tab-close:hover {
  background: #dc3545;
  color: #fff;
  opacity: 1;
}

/* Add Order Button */
.add-order-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border: 2px dashed #adb5bd;
  background: transparent;
  color: #6c757d;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
  flex-shrink: 0;
}

.add-order-btn:hover {
  border-color: #007bff;
  color: #007bff;
  background: rgba(0, 123, 255, 0.1);
}

/* Sync Controls */
.sync-controls {
  display: flex;
  align-items: center;
  gap: 10px;
}

.pending-badge {
  background: #ffc107;
  color: #856404;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}

.btn-sync-all {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  background: #28a745;
  color: #fff;
  border: none;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-sync-all:hover:not(:disabled) {
  background: #218838;
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.btn-sync-all:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-sync-all.syncing {
  background: #17a2b8;
}

/* Order Edit Wrapper */
.order-edit-wrapper {
  flex: 1;
  overflow: hidden;
  background: #fff;
}

/* Sync Panel */
.sync-panel {
  position: fixed;
  bottom: 20px;
  right: 20px;
  width: 350px;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  z-index: 1000;
  max-height: 400px;
  display: flex;
  flex-direction: column;
}

.sync-panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 16px;
  border-bottom: 1px solid #dee2e6;
  background: #f8f9fa;
  border-radius: 8px 8px 0 0;
}

.sync-panel-header h4 {
  margin: 0;
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 6px;
}

.btn-close {
  width: 24px;
  height: 24px;
  border: none;
  background: transparent;
  color: #6c757d;
  cursor: pointer;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-close:hover {
  background: #e9ecef;
}

.sync-panel-body {
  overflow-y: auto;
  padding: 8px;
}

.sync-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 12px;
  border-bottom: 1px solid #f1f3f5;
  font-size: 13px;
}

.sync-item:last-child {
  border-bottom: none;
}

.sync-table {
  font-weight: 600;
}

.sync-error {
  color: #dc3545;
  font-size: 11px;
  margin-top: 4px;
}

/* Floating Action Button (Mobile) */
.fab-sync {
  position: fixed;
  bottom: 20px;
  right: 20px;
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: #28a745;
  color: #fff;
  border: none;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  z-index: 999;
  transition: all 0.2s;
}

.fab-sync:hover:not(:disabled) {
  transform: scale(1.1);
}

.fab-sync:disabled {
  opacity: 0.7;
}

.fab-sync.syncing {
  background: #17a2b8;
}

.fab-badge {
  position: absolute;
  top: -4px;
  right: -4px;
  background: #dc3545;
  color: #fff;
  font-size: 11px;
  font-weight: 700;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Responsive */
@media (max-width: 768px) {
  .orders-tabs-header {
    flex-direction: column;
    align-items: stretch;
    gap: 8px;
  }

  .tabs-container {
    order: 2;
  }

  .sync-controls {
    order: 1;
    justify-content: space-between;
  }

  .sync-panel {
    width: calc(100% - 40px);
    left: 20px;
    right: 20px;
  }
}
</style>
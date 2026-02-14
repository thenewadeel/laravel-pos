<template>
  <div class="floor-restaurant-view">
    <!-- Header Stats -->
    <div class="stats-bar">
      <div class="stat-item">
        <span class="stat-value">{{ availableTables }}</span>
        <span class="stat-label">Available</span>
      </div>
      <div class="stat-item occupied">
        <span class="stat-value">{{ occupiedTables }}</span>
        <span class="stat-label">Occupied</span>
      </div>
      <div class="stat-item reserved">
        <span class="stat-value">{{ reservedTables }}</span>
        <span class="stat-label">Reserved</span>
      </div>
      <div class="stat-item total">
        <span class="stat-value">${{ formatCurrency(dailyTotal) }}</span>
        <span class="stat-label">Today's Sales</span>
      </div>
    </div>

    <!-- Main Floor Layout -->
    <div class="floor-layout">
      <div 
        v-for="floor in floors" 
        :key="floor.id"
        class="floor-section"
      >
        <div class="floor-header">
          <div v-if="editingFloor?.id === floor.id" class="inline-edit-form">
            <input 
              v-model="editingFloor.name" 
              type="text" 
              class="form-control"
              placeholder="Floor name"
              @keyup.enter="saveFloorEdit"
            >
            <button @click="saveFloorEdit" class="btn-icon success" title="Save">
              <i class="fas fa-check"></i>
            </button>
            <button @click="cancelFloorEdit" class="btn-icon" title="Cancel">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <template v-else>
            <h3>{{ floor.name }}</h3>
            <div class="floor-actions" v-if="canManageFloors">
              <button @click="startEditFloor(floor)" class="btn-icon" title="Edit Floor">
                <i class="fas fa-edit"></i>
              </button>
              <button @click="deleteFloor(floor)" class="btn-icon danger" title="Delete Floor">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </template>
        </div>

        <div class="tables-grid">
          <div 
            v-for="table in floor.tables" 
            :key="table.id"
            class="table-card"
            :class="[
              `status-${table.status}`,
              { 'has-order': table.currentOrder }
            ]"
            @click="handleTableClick(table)"
          >
            <div class="table-number">{{ table.number }}</div>
            <div class="table-capacity">
              <i class="fas fa-users"></i> {{ table.capacity }}
            </div>
            <div class="table-status">
              <StatusBadge 
                :status="table.status" 
                type="table" 
                size="small" 
                :show-label="false"
              />
            </div>
            <div v-if="table.currentOrder" class="table-order-info">
              <span class="order-amount">${{ formatCurrency(table.currentOrder.total) }}</span>
              <span class="order-time">{{ formatTime(table.currentOrder.created_at) }}</span>
            </div>
          </div>

          <!-- Inline Add Table Form -->
          <div v-if="addingTableFloor?.id === floor.id" class="inline-add-form table-add-form">
            <div class="form-group">
              <label>Table #</label>
              <input 
                v-model="newTable.number" 
                type="text" 
                class="form-control"
                placeholder="e.g., 12A"
              >
            </div>
            <div class="form-group">
              <label>Capacity</label>
              <input 
                v-model.number="newTable.capacity" 
                type="number" 
                min="1"
                class="form-control"
              >
            </div>
            <div class="form-actions">
              <button @click="saveNewTable(floor)" class="btn-icon success" title="Save">
                <i class="fas fa-check"></i>
              </button>
              <button @click="cancelAddTable" class="btn-icon" title="Cancel">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>

          <!-- Add Table Button (Manager only) -->
          <button 
            v-else-if="canManageFloors"
            class="add-table-btn"
            @click="startAddTable(floor)"
          >
            <i class="fas fa-plus"></i>
            <span>Add Table</span>
          </button>
        </div>
      </div>

      <!-- Inline Add Floor Form -->
      <div v-if="isAddingFloor" class="inline-add-form floor-add-form">
        <div class="form-group">
          <label>Floor Name</label>
          <input 
            v-model="newFloor.name" 
            type="text" 
            class="form-control"
            placeholder="e.g., Second Floor"
          >
        </div>
        <div class="form-actions">
          <button @click="saveNewFloor" class="btn success" :disabled="!newFloor.name">
            <i class="fas fa-check"></i> Save
          </button>
          <button @click="cancelAddFloor" class="btn secondary">
            <i class="fas fa-times"></i> Cancel
          </button>
        </div>
      </div>

      <!-- Add Floor Button (Manager only) -->
      <button 
        v-else-if="canManageFloors && floors.length < 5"
        class="add-floor-btn"
        @click="startAddFloor"
      >
        <i class="fas fa-plus-circle"></i>
        <span>Add New Floor</span>
      </button>
    </div>

    <!-- Quick Actions Panel -->
    <div class="quick-actions">
      <h4>Quick Actions</h4>
      <div class="actions-grid">
        <!-- Inline New Order Form -->
        <div v-if="isCreatingOrder" class="inline-action-form">
          <div class="form-group">
            <label>Table</label>
            <select v-model="newOrder.tableId" class="form-control">
              <option value="">Select table...</option>
              <optgroup v-for="floor in floors" :key="floor.id" :label="floor.name">
                <option 
                  v-for="table in getAvailableTables(floor)" 
                  :key="table.id" 
                  :value="table.id"
                >
                  Table {{ table.number }}
                </option>
              </optgroup>
            </select>
          </div>
          <div class="form-group">
            <label>Type</label>
            <select v-model="newOrder.type" class="form-control">
              <option value="dine-in">Dine-in</option>
              <option value="take-away">Take-away</option>
              <option value="delivery">Delivery</option>
            </select>
          </div>
          <div class="form-actions">
            <button @click="createNewOrder" class="btn primary" :disabled="!newOrder.tableId">
              <i class="fas fa-check"></i> Create
            </button>
            <button @click="cancelCreateOrder" class="btn secondary">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>

        <button 
          v-else 
          @click="startCreateOrder" 
          class="action-btn primary"
        >
          <i class="fas fa-plus"></i>
          <span>New Order</span>
        </button>

        <button @click="showTransfer = true" class="action-btn">
          <i class="fas fa-exchange-alt"></i>
          <span>Transfer</span>
        </button>
        <button @click="showMerge = true" class="action-btn">
          <i class="fas fa-object-group"></i>
          <span>Merge</span>
        </button>
      </div>
    </div>

    <!-- Selected Table Panel -->
    <div v-if="selectedTable" class="table-detail-panel">
      <div class="panel-header">
        <h4>Table {{ selectedTable.number }}</h4>
        <button @click="selectedTable = null" class="btn-close">
          <i class="fas fa-times"></i>
        </button>
      </div>
      
      <div class="panel-body">
        <div v-if="selectedTable.currentOrder" class="order-details">
          <h5>Current Order</h5>
          <div class="order-meta">
            <span>Order #{{ selectedTable.currentOrder.id }}</span>
            <StatusBadge 
              :status="selectedTable.currentOrder.status" 
              type="order"
              size="small"
            />
          </div>
          <div class="order-items">
            <div 
              v-for="item in selectedTable.currentOrder.items" 
              :key="item.id"
              class="order-item"
            >
              <span class="item-name">{{ item.name }}</span>
              <span class="item-qty">x{{ item.quantity }}</span>
              <span class="item-price">${{ formatCurrency(item.price) }}</span>
            </div>
          </div>
          <div class="order-total">
            <strong>Total: ${{ formatCurrency(selectedTable.currentOrder.total) }}</strong>
          </div>
          
          <div class="order-actions">
            <button @click="viewOrder(selectedTable.currentOrder.id)" class="btn primary">
              <i class="fas fa-eye"></i> View Order
            </button>
            <button @click="addToOrder(selectedTable.currentOrder.id)" class="btn">
              <i class="fas fa-plus"></i> Add Items
            </button>
            <button 
              v-if="canProcessPayment"
              @click="processPayment(selectedTable.currentOrder.id)" 
              class="btn success"
            >
              <i class="fas fa-credit-card"></i> Pay
            </button>
          </div>
        </div>
        
        <div v-else class="empty-table-actions">
          <p>No active order</p>
          <button @click="createOrder(selectedTable.id)" class="btn primary btn-block">
            <i class="fas fa-plus"></i> Create Order
          </button>
        </div>

        <div class="table-management-actions" v-if="canManageTables">
          <hr>
          <button @click="editTable(selectedTable)" class="btn secondary btn-sm">
            <i class="fas fa-edit"></i> Edit Table
          </button>
          <button @click="changeTableStatus(selectedTable)" class="btn secondary btn-sm">
            <i class="fas fa-sync"></i> Change Status
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue'
import axios from 'axios'
import StatusBadge from '../business/StatusBadge.vue'

export default {
  name: 'FloorRestaurantView',

  components: {
    StatusBadge
  },

  props: {
    initialFloors: {
      type: Array,
      default: () => []
    },
    user: {
      type: Object,
      required: true
    },
    dailyStats: {
      type: Object,
      default: () => ({
        total: 0,
        orders: 0
      })
    }
  },

  emits: ['create-order', 'view-order', 'process-payment'],

  setup(props, { emit }) {
    // Get CSRF token from meta tag
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || ''
    
    // Configure axios defaults
    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken
    axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

    // State
    const floors = ref(props.initialFloors || [])
    const selectedTable = ref(null)
    const isAddingFloor = ref(false)
    const isCreatingOrder = ref(false)
    const addingTableFloor = ref(null)
    const editingFloor = ref(null)
    const showTransfer = ref(false)
    const showMerge = ref(false)
    const isLoading = ref(false)

    const newFloor = ref({ name: '' })
    
    const newTable = ref({
      number: '',
      capacity: 4
    })

    const newOrder = ref({
      tableId: '',
      type: 'dine-in',
      waiterName: props.user?.first_name + ' ' + props.user?.last_name || ''
    })

    // Helper function to show notifications
    const showNotification = (message, type = 'success') => {
      if (typeof window.toastr !== 'undefined') {
        if (type === 'success') {
          window.toastr.success(message)
        } else if (type === 'error') {
          window.toastr.error(message)
        } else {
          window.toastr.info(message)
        }
      } else {
        if (type === 'error') {
          alert('Error: ' + message)
        } else {
          alert(message)
        }
      }
    }

    // Helper function to refresh floors data
    const refreshFloors = async () => {
      try {
        isLoading.value = true
        const response = await axios.get('/api/v1/floors')
        if (response.data.success) {
          floors.value = response.data.data.map(floor => ({
            ...floor,
            tables: floor.tables.map(table => ({
              ...table,
              number: table.table_number
            }))
          }))
        }
      } catch (error) {
        console.error('Failed to refresh floors:', error)
        showNotification('Failed to refresh floor data', 'error')
      } finally {
        isLoading.value = false
      }
    }

    // RBAC Computed
    const userType = computed(() => props.user?.type || 'cashier')

    const canManageFloors = computed(() => {
      return ['admin', 'manager'].includes(userType.value)
    })

    const canManageTables = computed(() => {
      return ['admin', 'manager'].includes(userType.value)
    })

    const canProcessPayment = computed(() => {
      return ['admin', 'manager', 'cashier'].includes(userType.value)
    })

    // Stats Computed
    const availableTables = computed(() => {
      return floors.value.reduce((count, floor) => {
        return count + floor.tables.filter(t => t.status === 'available').length
      }, 0)
    })

    const occupiedTables = computed(() => {
      return floors.value.reduce((count, floor) => {
        return count + floor.tables.filter(t => t.status === 'occupied').length
      }, 0)
    })

    const reservedTables = computed(() => {
      return floors.value.reduce((count, floor) => {
        return count + floor.tables.filter(t => t.status === 'reserved').length
      }, 0)
    })

    const dailyTotal = computed(() => props.dailyStats?.total || 0)

    // Methods
    const handleTableClick = (table) => {
      selectedTable.value = table
    }

    const getAvailableTables = (floor) => {
      return floor.tables.filter(t => t.status === 'available' || t.status === 'reserved')
    }

    // Floor Management
    const startAddFloor = () => {
      isAddingFloor.value = true
      newFloor.value = { name: '' }
    }

    const cancelAddFloor = () => {
      isAddingFloor.value = false
      newFloor.value = { name: '' }
    }

    const saveNewFloor = async () => {
      if (!newFloor.value.name || isLoading.value) return
      
      try {
        isLoading.value = true
        const response = await axios.post('/api/v1/floors', {
          name: newFloor.value.name
        })
        
        if (response.data.success) {
          showNotification('Floor created successfully')
          await refreshFloors()
          isAddingFloor.value = false
          newFloor.value = { name: '' }
        } else {
          showNotification(response.data.error?.message || 'Failed to create floor', 'error')
        }
      } catch (error) {
        console.error('Error creating floor:', error)
        const message = error.response?.data?.error?.message || 'Failed to create floor'
        showNotification(message, 'error')
      } finally {
        isLoading.value = false
      }
    }

    const startEditFloor = (floor) => {
      editingFloor.value = { ...floor }
    }

    const saveFloorEdit = async () => {
      if (!editingFloor.value?.name || isLoading.value) return
      
      try {
        isLoading.value = true
        const response = await axios.put(`/api/v1/floors/${editingFloor.value.id}`, {
          name: editingFloor.value.name
        })
        
        if (response.data.success) {
          showNotification('Floor updated successfully')
          await refreshFloors()
          editingFloor.value = null
        } else {
          showNotification(response.data.error?.message || 'Failed to update floor', 'error')
        }
      } catch (error) {
        console.error('Error updating floor:', error)
        const message = error.response?.data?.error?.message || 'Failed to update floor'
        showNotification(message, 'error')
      } finally {
        isLoading.value = false
      }
    }

    const cancelFloorEdit = () => {
      editingFloor.value = null
    }

    const deleteFloor = async (floor) => {
      if (!confirm(`Delete floor "${floor.name}"? This will also delete all tables on this floor.`)) {
        return
      }
      
      if (isLoading.value) return
      
      try {
        isLoading.value = true
        const response = await axios.delete(`/api/v1/floors/${floor.id}`)
        
        if (response.data.success) {
          showNotification('Floor deleted successfully')
          await refreshFloors()
        } else {
          showNotification(response.data.error?.message || 'Failed to delete floor', 'error')
        }
      } catch (error) {
        console.error('Error deleting floor:', error)
        const message = error.response?.data?.error?.message || 'Failed to delete floor'
        showNotification(message, 'error')
      } finally {
        isLoading.value = false
      }
    }

    // Table Management
    const startAddTable = (floor) => {
      addingTableFloor.value = floor
      newTable.value = { number: '', capacity: 4 }
    }

    const cancelAddTable = () => {
      addingTableFloor.value = null
      newTable.value = { number: '', capacity: 4 }
    }

    const saveNewTable = async (floor) => {
      if (!newTable.value.number || isLoading.value) return
      
      try {
        isLoading.value = true
        const response = await axios.post(`/api/v1/floors/${floor.id}/tables`, {
          table_number: newTable.value.number,
          capacity: newTable.value.capacity
        })
        
        if (response.data.success) {
          showNotification('Table created successfully')
          await refreshFloors()
          addingTableFloor.value = null
          newTable.value = { number: '', capacity: 4 }
        } else {
          showNotification(response.data.error?.message || 'Failed to create table', 'error')
        }
      } catch (error) {
        console.error('Error creating table:', error)
        const message = error.response?.data?.error?.message || 'Failed to create table'
        showNotification(message, 'error')
      } finally {
        isLoading.value = false
      }
    }

    const editTable = async (table) => {
      const newNumber = prompt('Edit table number:', table.number)
      if (!newNumber || newNumber === table.number) return
      if (isLoading.value) return
      
      try {
        isLoading.value = true
        const response = await axios.put(`/api/v1/tables/${table.id}`, {
          table_number: newNumber
        })
        
        if (response.data.success) {
          showNotification('Table updated successfully')
          await refreshFloors()
        } else {
          showNotification(response.data.error?.message || 'Failed to update table', 'error')
        }
      } catch (error) {
        console.error('Error updating table:', error)
        const message = error.response?.data?.error?.message || 'Failed to update table'
        showNotification(message, 'error')
      } finally {
        isLoading.value = false
      }
    }

    const deleteTable = async (table) => {
      if (!confirm(`Delete table ${table.number}?`)) return
      if (isLoading.value) return
      
      try {
        isLoading.value = true
        const response = await axios.delete(`/api/v1/tables/${table.id}`)
        
        if (response.data.success) {
          showNotification('Table deleted successfully')
          await refreshFloors()
          if (selectedTable.value?.id === table.id) {
            selectedTable.value = null
          }
        } else {
          showNotification(response.data.error?.message || 'Failed to delete table', 'error')
        }
      } catch (error) {
        console.error('Error deleting table:', error)
        const message = error.response?.data?.error?.message || 'Failed to delete table'
        showNotification(message, 'error')
      } finally {
        isLoading.value = false
      }
    }

    const changeTableStatus = async (table) => {
      if (isLoading.value) return
      
      const statuses = ['available', 'occupied', 'reserved', 'cleaning']
      const currentIndex = statuses.indexOf(table.status)
      const nextStatus = statuses[(currentIndex + 1) % statuses.length]
      
      try {
        isLoading.value = true
        const response = await axios.patch(`/api/v1/tables/${table.id}/status`, {
          status: nextStatus
        })
        
        if (response.data.success) {
          showNotification(`Table status changed to ${nextStatus}`)
          await refreshFloors()
          // Update selected table if it's the one being changed
          if (selectedTable.value?.id === table.id) {
            selectedTable.value = { ...selectedTable.value, status: nextStatus }
          }
        } else {
          showNotification(response.data.error?.message || 'Failed to update table status', 'error')
        }
      } catch (error) {
        console.error('Error updating table status:', error)
        const message = error.response?.data?.error?.message || 'Failed to update table status'
        showNotification(message, 'error')
      } finally {
        isLoading.value = false
      }
    }

    // Order Management
    const startCreateOrder = () => {
      isCreatingOrder.value = true
      newOrder.value = {
        tableId: '',
        type: 'dine-in',
        waiterName: props.user?.first_name + ' ' + props.user?.last_name || ''
      }
    }

    const cancelCreateOrder = () => {
      isCreatingOrder.value = false
      newOrder.value = {
        tableId: '',
        type: 'dine-in',
        waiterName: props.user?.first_name + ' ' + props.user?.last_name || ''
      }
    }

    const createNewOrder = () => {
      if (!newOrder.value.tableId) return
      
      emit('create-order', {
        tableId: newOrder.value.tableId,
        type: newOrder.value.type,
        waiterName: newOrder.value.waiterName
      })
      
      isCreatingOrder.value = false
      newOrder.value = {
        tableId: '',
        type: 'dine-in',
        waiterName: props.user?.first_name + ' ' + props.user?.last_name || ''
      }
    }

    const createOrder = (tableId) => {
      emit('create-order', { tableId, type: 'dine-in' })
    }

    const viewOrder = (orderId) => {
      emit('view-order', orderId)
    }

    const addToOrder = (orderId) => {
      emit('view-order', orderId)
    }

    const processPayment = (orderId) => {
      emit('process-payment', orderId)
    }

    // Formatters
    const formatCurrency = (amount) => {
      return parseFloat(amount).toFixed(2)
    }

    const formatTime = (dateString) => {
      if (!dateString) return ''
      const date = new Date(dateString)
      return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
    }

    return {
      floors,
      selectedTable,
      isAddingFloor,
      isCreatingOrder,
      isLoading,
      addingTableFloor,
      editingFloor,
      showTransfer,
      showMerge,
      newFloor,
      newTable,
      newOrder,
      canManageFloors,
      canManageTables,
      canProcessPayment,
      availableTables,
      occupiedTables,
      reservedTables,
      dailyTotal,
      handleTableClick,
      getAvailableTables,
      startAddFloor,
      cancelAddFloor,
      saveNewFloor,
      startEditFloor,
      saveFloorEdit,
      cancelFloorEdit,
      deleteFloor,
      startAddTable,
      cancelAddTable,
      saveNewTable,
      editTable,
      deleteTable,
      changeTableStatus,
      startCreateOrder,
      cancelCreateOrder,
      createNewOrder,
      createOrder,
      viewOrder,
      addToOrder,
      processPayment,
      formatCurrency,
      formatTime
    }
  }
}
</script>

<style scoped>
.floor-restaurant-view {
  display: flex;
  flex-direction: column;
  height: calc(100vh - 180px);
  background: #f8f9fa;
  gap: 16px;
  padding: 16px;
}

/* Stats Bar */
.stats-bar {
  display: flex;
  gap: 16px;
  background: #fff;
  padding: 16px 24px;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.stat-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 0 24px;
  border-right: 1px solid #e9ecef;
}

.stat-item:last-child {
  border-right: none;
  margin-left: auto;
}

.stat-value {
  font-size: 28px;
  font-weight: 700;
  color: #28a745;
}

.stat-item.occupied .stat-value {
  color: #dc3545;
}

.stat-item.reserved .stat-value {
  color: #ffc107;
}

.stat-item.total .stat-value {
  color: #007bff;
}

.stat-label {
  font-size: 12px;
  color: #6c757d;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Floor Layout */
.floor-layout {
  flex: 1;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.floor-section {
  background: #fff;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.floor-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
  padding-bottom: 12px;
  border-bottom: 2px solid #e9ecef;
  min-height: 44px;
}

.floor-header h3 {
  margin: 0;
  font-size: 18px;
  font-weight: 600;
  color: #212529;
}

.floor-actions {
  display: flex;
  gap: 8px;
}

/* Inline Edit Form */
.inline-edit-form {
  display: flex;
  align-items: center;
  gap: 8px;
  flex: 1;
}

.inline-edit-form input {
  flex: 1;
  max-width: 300px;
}

/* Tables Grid */
.tables-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
  gap: 16px;
}

.table-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 16px;
  background: #fff;
  border: 2px solid #dee2e6;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s;
  position: relative;
}

.table-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.table-card.status-available {
  border-color: #28a745;
  background: #f8fff9;
}

.table-card.status-occupied {
  border-color: #dc3545;
  background: #fff8f8;
}

.table-card.status-reserved {
  border-color: #ffc107;
  background: #fffbf0;
}

.table-card.status-cleaning {
  border-color: #6c757d;
  background: #f8f9fa;
}

.table-card.has-order::after {
  content: '';
  position: absolute;
  top: 8px;
  right: 8px;
  width: 10px;
  height: 10px;
  background: #dc3545;
  border-radius: 50%;
}

.table-number {
  font-size: 24px;
  font-weight: 700;
  color: #212529;
}

.table-capacity {
  font-size: 12px;
  color: #6c757d;
  margin-top: 4px;
}

.table-capacity i {
  margin-right: 4px;
}

.table-status {
  margin-top: 8px;
}

.table-order-info {
  margin-top: 8px;
  text-align: center;
  font-size: 11px;
}

.order-amount {
  display: block;
  font-weight: 600;
  color: #28a745;
}

.order-time {
  display: block;
  color: #6c757d;
}

/* Inline Add Forms */
.inline-add-form {
  background: #f8f9fa;
  border: 2px dashed #adb5bd;
  border-radius: 8px;
  padding: 16px;
}

.table-add-form {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.floor-add-form {
  display: flex;
  flex-direction: column;
  gap: 12px;
  max-width: 400px;
}

.inline-add-form .form-group {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.inline-add-form label {
  font-size: 12px;
  font-weight: 500;
  color: #495057;
}

.inline-add-form input,
.inline-add-form select {
  padding: 8px 12px;
  border: 1px solid #ced4da;
  border-radius: 4px;
  font-size: 14px;
}

.form-actions {
  display: flex;
  gap: 8px;
  justify-content: flex-end;
}

/* Add Buttons */
.add-table-btn,
.add-floor-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 16px;
  background: transparent;
  border: 2px dashed #adb5bd;
  border-radius: 8px;
  color: #6c757d;
  cursor: pointer;
  transition: all 0.2s;
  min-height: 100px;
}

.add-table-btn:hover,
.add-floor-btn:hover {
  border-color: #007bff;
  color: #007bff;
  background: rgba(0,123,255,0.05);
}

.add-floor-btn {
  flex-direction: row;
  gap: 8px;
  min-height: auto;
  padding: 12px 24px;
  margin-top: 8px;
}

/* Quick Actions */
.quick-actions {
  background: #fff;
  padding: 16px 20px;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.quick-actions h4 {
  margin: 0 0 12px 0;
  font-size: 14px;
  color: #6c757d;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.actions-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 12px;
}

.action-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  padding: 16px;
  background: #f8f9fa;
  border: 1px solid #dee2e6;
  border-radius: 6px;
  color: #495057;
  cursor: pointer;
  transition: all 0.2s;
}

.action-btn:hover {
  background: #e9ecef;
  border-color: #adb5bd;
}

.action-btn.primary {
  background: #007bff;
  border-color: #007bff;
  color: #fff;
}

.action-btn.primary:hover {
  background: #0056b3;
  border-color: #0056b3;
}

.action-btn i {
  font-size: 20px;
}

.action-btn span {
  font-size: 12px;
  font-weight: 500;
}

/* Inline Action Form */
.inline-action-form {
  grid-column: 1 / -1;
  background: #f8f9fa;
  border: 1px solid #dee2e6;
  border-radius: 6px;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.inline-action-form .form-group {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.inline-action-form label {
  font-size: 12px;
  font-weight: 500;
  color: #495057;
}

.inline-action-form select {
  padding: 8px 12px;
  border: 1px solid #ced4da;
  border-radius: 4px;
  font-size: 14px;
}

/* Table Detail Panel */
.table-detail-panel {
  position: fixed;
  right: 0;
  top: 0;
  width: 380px;
  height: 100vh;
  background: #fff;
  box-shadow: -4px 0 12px rgba(0,0,0,0.15);
  z-index: 1000;
  display: flex;
  flex-direction: column;
}

.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  border-bottom: 1px solid #e9ecef;
  background: #f8f9fa;
}

.panel-header h4 {
  margin: 0;
  font-size: 18px;
}

.btn-close {
  width: 32px;
  height: 32px;
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

.panel-body {
  flex: 1;
  overflow-y: auto;
  padding: 20px;
}

.order-details h5 {
  margin: 0 0 12px 0;
  font-size: 14px;
  color: #6c757d;
}

.order-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
  padding: 12px;
  background: #f8f9fa;
  border-radius: 6px;
}

.order-items {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 16px;
}

.order-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 12px;
  background: #fff;
  border: 1px solid #e9ecef;
  border-radius: 4px;
}

.item-name {
  flex: 1;
  font-weight: 500;
}

.item-qty {
  color: #6c757d;
  margin: 0 12px;
}

.item-price {
  font-weight: 600;
  color: #28a745;
}

.order-total {
  text-align: right;
  padding: 12px;
  background: #f8f9fa;
  border-radius: 6px;
  margin-bottom: 16px;
  font-size: 16px;
}

.order-actions {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.order-actions .btn {
  justify-content: center;
}

.empty-table-actions {
  text-align: center;
  padding: 40px 20px;
}

.empty-table-actions p {
  color: #6c757d;
  margin-bottom: 20px;
}

.table-management-actions {
  margin-top: 20px;
  display: flex;
  gap: 8px;
}

/* Buttons */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn:hover:not(:disabled) {
  transform: translateY(-1px);
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn.primary {
  background: #007bff;
  color: #fff;
}

.btn.primary:hover {
  background: #0056b3;
}

.btn.secondary {
  background: #6c757d;
  color: #fff;
}

.btn.secondary:hover {
  background: #545b62;
}

.btn.success {
  background: #28a745;
  color: #fff;
}

.btn.success:hover {
  background: #218838;
}

.btn.btn-block {
  width: 100%;
  justify-content: center;
}

.btn.btn-sm {
  padding: 6px 12px;
  font-size: 12px;
}

.btn-icon {
  width: 32px;
  height: 32px;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #e9ecef;
  border: none;
  border-radius: 6px;
  color: #495057;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-icon:hover {
  background: #dee2e6;
}

.btn-icon.success {
  background: #d4edda;
  color: #155724;
}

.btn-icon.success:hover {
  background: #c3e6cb;
}

.btn-icon.danger {
  background: #f8d7da;
  color: #721c24;
}

.btn-icon.danger:hover {
  background: #f5c6cb;
}

/* Form Controls */
.form-control {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #ced4da;
  border-radius: 4px;
  font-size: 14px;
}

.form-control:focus {
  outline: none;
  border-color: #007bff;
  box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
}

/* Responsive */
@media (max-width: 768px) {
  .stats-bar {
    flex-wrap: wrap;
    gap: 12px;
  }

  .stat-item {
    flex: 1;
    min-width: 100px;
    padding: 0 12px;
    border-right: none;
  }

  .tables-grid {
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
  }

  .actions-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .table-detail-panel {
    width: 100%;
  }
}
</style>
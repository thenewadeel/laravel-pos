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
          <h3>{{ floor.name }}</h3>
          <div class="floor-actions" v-if="canManageFloors">
            <button @click="editFloor(floor)" class="btn-icon" title="Edit Floor">
              <i class="fas fa-edit"></i>
            </button>
            <button @click="deleteFloor(floor)" class="btn-icon danger" title="Delete Floor">
              <i class="fas fa-trash"></i>
            </button>
          </div>
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

          <!-- Add Table Button (Manager only) -->
          <button 
            v-if="canManageFloors"
            class="add-table-btn"
            @click="addTable(floor)"
          >
            <i class="fas fa-plus"></i>
            <span>Add Table</span>
          </button>
        </div>
      </div>

      <!-- Add Floor Button (Manager only) -->
      <button 
        v-if="canManageFloors && floors.length < 5"
        class="add-floor-btn"
        @click="addFloor"
      >
        <i class="fas fa-plus-circle"></i>
        <span>Add New Floor</span>
      </button>
    </div>

    <!-- Quick Actions Panel -->
    <div class="quick-actions">
      <h4>Quick Actions</h4>
      <div class="actions-grid">
        <button @click="showNewOrderModal = true" class="action-btn primary">
          <i class="fas fa-plus"></i>
          <span>New Order</span>
        </button>
        <button @click="showReservationModal = true" class="action-btn">
          <i class="fas fa-calendar-check"></i>
          <span>Reservation</span>
        </button>
        <button @click="showTransferModal = true" class="action-btn">
          <i class="fas fa-exchange-alt"></i>
          <span>Transfer</span>
        </button>
        <button @click="showMergeModal = true" class="action-btn">
          <i class="fas fa-object-group"></i>
          <span>Merge Tables</span>
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
          <button 
            v-if="canReserveTable"
            @click="reserveTable(selectedTable.id)" 
            class="btn btn-block"
          >
            <i class="fas fa-calendar"></i> Reserve Table
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

    <!-- Modals -->
    <Teleport to="body">
      <!-- New Order Modal -->
      <div v-if="showNewOrderModal" class="modal-overlay" @click.self="showNewOrderModal = false">
        <div class="modal-content">
          <h3>New Order</h3>
          <div class="form-group">
            <label>Select Table</label>
            <select v-model="newOrder.tableId" class="form-control">
              <option value="">Choose a table...</option>
              <optgroup v-for="floor in floors" :key="floor.id" :label="floor.name">
                <option 
                  v-for="table in getAvailableTables(floor)" 
                  :key="table.id" 
                  :value="table.id"
                >
                  Table {{ table.number }} (Capacity: {{ table.capacity }})
                </option>
              </optgroup>
            </select>
          </div>
          <div class="form-group">
            <label>Order Type</label>
            <select v-model="newOrder.type" class="form-control">
              <option value="dine-in">Dine-in</option>
              <option value="take-away">Take-away</option>
              <option value="delivery">Delivery</option>
            </select>
          </div>
          <div class="form-group" v-if="newOrder.type === 'dine-in'">
            <label>Waiter</label>
            <input v-model="newOrder.waiterName" type="text" class="form-control" placeholder="Waiter name">
          </div>
          <div class="modal-actions">
            <button @click="showNewOrderModal = false" class="btn secondary">Cancel</button>
            <button @click="createNewOrder" class="btn primary" :disabled="!newOrder.tableId">
              Create Order
            </button>
          </div>
        </div>
      </div>

      <!-- Reservation Modal -->
      <div v-if="showReservationModal" class="modal-overlay" @click.self="showReservationModal = false">
        <div class="modal-content">
          <h3>New Reservation</h3>
          <div class="form-group">
            <label>Customer Name</label>
            <input v-model="reservation.customerName" type="text" class="form-control" placeholder="Customer name">
          </div>
          <div class="form-group">
            <label>Phone</label>
            <input v-model="reservation.phone" type="tel" class="form-control" placeholder="Phone number">
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Date</label>
              <input v-model="reservation.date" type="date" class="form-control">
            </div>
            <div class="form-group">
              <label>Time</label>
              <input v-model="reservation.time" type="time" class="form-control">
            </div>
          </div>
          <div class="form-group">
            <label>Party Size</label>
            <input v-model.number="reservation.partySize" type="number" min="1" class="form-control">
          </div>
          <div class="form-group">
            <label>Preferred Table</label>
            <select v-model="reservation.tableId" class="form-control">
              <option value="">Any available table</option>
              <optgroup v-for="floor in floors" :key="floor.id" :label="floor.name">
                <option 
                  v-for="table in getAvailableTables(floor)" 
                  :key="table.id" 
                  :value="table.id"
                >
                  Table {{ table.number }} ({{ table.capacity }} seats)
                </option>
              </optgroup>
            </select>
          </div>
          <div class="modal-actions">
            <button @click="showReservationModal = false" class="btn secondary">Cancel</button>
            <button @click="createReservation" class="btn primary" :disabled="!reservation.customerName">
              Create Reservation
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import StatusBadge from '../business/StatusBadge.vue'

export default {
  name: 'FloorRestaurantView',

  components: {
    StatusBadge
  },

  props: {
    floors: {
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

  emits: ['create-order', 'view-order', 'process-payment', 'update-table', 'create-reservation'],

  setup(props, { emit }) {
    // State
    const selectedTable = ref(null)
    const showNewOrderModal = ref(false)
    const showReservationModal = ref(false)
    const showTransferModal = ref(false)
    const showMergeModal = ref(false)

    const newOrder = ref({
      tableId: '',
      type: 'dine-in',
      waiterName: props.user?.first_name + ' ' + props.user?.last_name || ''
    })

    const reservation = ref({
      customerName: '',
      phone: '',
      date: new Date().toISOString().split('T')[0],
      time: '19:00',
      partySize: 2,
      tableId: ''
    })

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

    const canReserveTable = computed(() => {
      return ['admin', 'manager', 'cashier'].includes(userType.value)
    })

    // Stats Computed
    const availableTables = computed(() => {
      return props.floors.reduce((count, floor) => {
        return count + floor.tables.filter(t => t.status === 'available').length
      }, 0)
    })

    const occupiedTables = computed(() => {
      return props.floors.reduce((count, floor) => {
        return count + floor.tables.filter(t => t.status === 'occupied').length
      }, 0)
    })

    const reservedTables = computed(() => {
      return props.floors.reduce((count, floor) => {
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

    const createNewOrder = () => {
      if (!newOrder.value.tableId) return
      
      emit('create-order', {
        tableId: newOrder.value.tableId,
        type: newOrder.value.type,
        waiterName: newOrder.value.waiterName
      })
      
      showNewOrderModal.value = false
      newOrder.value = {
        tableId: '',
        type: 'dine-in',
        waiterName: props.user?.first_name + ' ' + props.user?.last_name || ''
      }
    }

    const createReservation = () => {
      emit('create-reservation', { ...reservation.value })
      showReservationModal.value = false
      reservation.value = {
        customerName: '',
        phone: '',
        date: new Date().toISOString().split('T')[0],
        time: '19:00',
        partySize: 2,
        tableId: ''
      }
    }

    const formatCurrency = (amount) => {
      return parseFloat(amount).toFixed(2)
    }

    const formatTime = (dateString) => {
      if (!dateString) return ''
      const date = new Date(dateString)
      return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
    }

    // Placeholder methods for manager actions
    const editFloor = (floor) => {
      console.log('Edit floor:', floor)
    }

    const deleteFloor = (floor) => {
      if (confirm(`Delete floor "${floor.name}"?`)) {
        console.log('Delete floor:', floor)
      }
    }

    const addFloor = () => {
      console.log('Add new floor')
    }

    const addTable = (floor) => {
      console.log('Add table to floor:', floor)
    }

    const editTable = (table) => {
      console.log('Edit table:', table)
    }

    const changeTableStatus = (table) => {
      const statuses = ['available', 'occupied', 'reserved', 'cleaning']
      const currentIndex = statuses.indexOf(table.status)
      const nextStatus = statuses[(currentIndex + 1) % statuses.length]
      
      emit('update-table', { 
        tableId: table.id, 
        status: nextStatus 
      })
    }

    const reserveTable = (tableId) => {
      emit('update-table', { 
        tableId, 
        status: 'reserved' 
      })
    }

    return {
      selectedTable,
      showNewOrderModal,
      showReservationModal,
      showTransferModal,
      showMergeModal,
      newOrder,
      reservation,
      canManageFloors,
      canManageTables,
      canProcessPayment,
      canReserveTable,
      availableTables,
      occupiedTables,
      reservedTables,
      dailyTotal,
      handleTableClick,
      getAvailableTables,
      createOrder,
      viewOrder,
      addToOrder,
      processPayment,
      createNewOrder,
      createReservation,
      formatCurrency,
      formatTime,
      editFloor,
      deleteFloor,
      addFloor,
      addTable,
      editTable,
      changeTableStatus,
      reserveTable
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

.btn-icon.danger {
  background: #f8d7da;
  color: #721c24;
}

.btn-icon.danger:hover {
  background: #f5c6cb;
}

/* Modals */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
}

.modal-content {
  background: #fff;
  border-radius: 8px;
  padding: 24px;
  width: 100%;
  max-width: 480px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-content h3 {
  margin: 0 0 20px 0;
  font-size: 20px;
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  margin-bottom: 6px;
  font-size: 13px;
  font-weight: 500;
  color: #495057;
}

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

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 24px;
  padding-top: 16px;
  border-top: 1px solid #e9ecef;
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
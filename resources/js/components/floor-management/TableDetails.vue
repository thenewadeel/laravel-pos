<template>
  <div class="table-details" :class="{ 'has-table': !!table }">
    <div v-if="!table" class="no-selection">
      <i class="fas fa-hand-pointer"></i>
      <p>Select a table to view details</p>
    </div>

    <div v-else class="table-info-panel">
      <div class="panel-header">
        <h3 class="table-title">
          Table {{ table.table_number }}
          <span v-if="table.name" class="table-subtitle">{{ table.name }}</span>
        </h3>
        <div class="status-badge-large" :class="`status-${table.status}`">
          <i :class="statusIcon"></i>
          {{ formatStatus(table.status) }}
        </div>
      </div>

      <div class="panel-body">
        <!-- Table Info -->
        <div class="info-section">
          <h4>Table Information</h4>
          <div class="info-grid">
            <div class="info-item">
              <span class="label">Capacity:</span>
              <span class="value">{{ table.capacity }} seats</span>
            </div>
            <div class="info-item">
              <span class="label">Shape:</span>
              <span class="value capitalize">{{ table.shape || 'Rectangle' }}</span>
            </div>
            <div class="info-item">
              <span class="label">Floor:</span>
              <span class="value">{{ table.floor?.name || 'N/A' }}</span>
            </div>
          </div>
        </div>

        <!-- Current Order -->
        <div v-if="table.current_order" class="info-section">
          <h4>Current Order</h4>
          <div class="order-card">
            <div class="order-header">
              <span class="order-number">#{{ table.current_order.order_number }}</span>
              <span class="order-time">{{ formatTime(table.current_order.created_at) }}</span>
            </div>
            <div class="order-details">
              <div class="detail-row">
                <span class="label">Customer:</span>
                <span class="value">{{ table.current_order.customer?.name || 'Walk-in' }}</span>
              </div>
              <div class="detail-row">
                <span class="label">Items:</span>
                <span class="value">{{ table.current_order.items_count || 0 }}</span>
              </div>
              <div class="detail-row">
                <span class="label">Total:</span>
                <span class="value amount">${{ formatAmount(table.current_order.total) }}</span>
              </div>
            </div>
            <div v-if="table.current_order.status === 'occupied'" class="occupation-timer">
              <i class="fas fa-clock"></i>
              Occupied for {{ occupationTime }}
            </div>
          </div>
        </div>

        <!-- Reservation Info -->
        <div v-if="table.current_reservation" class="info-section">
          <h4>Reservation</h4>
          <div class="reservation-card">
            <div class="detail-row">
              <span class="label">Customer:</span>
              <span class="value">{{ table.current_reservation.customer_name }}</span>
            </div>
            <div class="detail-row">
              <span class="label">Time:</span>
              <span class="value">{{ formatTime(table.current_reservation.reservation_time) }}</span>
            </div>
            <div class="detail-row">
              <span class="label">Party Size:</span>
              <span class="value">{{ table.current_reservation.party_size }} people</span>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-section">
          <template v-if="table.status === 'available'">
            <button 
              v-if="canAssign"
              class="btn btn-primary btn-block"
              @click="assignOrder"
            >
              <i class="fas fa-plus"></i> Assign Order
            </button>
          </template>

          <template v-if="table.status === 'occupied'">
            <button 
              class="btn btn-info btn-block"
              @click="viewOrder"
            >
              <i class="fas fa-eye"></i> View Order
            </button>
            <button 
              v-if="canAssign"
              class="btn btn-warning btn-block"
              @click="releaseTable"
            >
              <i class="fas fa-door-open"></i> Release Table
            </button>
          </template>

          <template v-if="table.status === 'reserved'">
            <button 
              class="btn btn-info btn-block"
              @click="viewReservation"
            >
              <i class="fas fa-eye"></i> View Reservation
            </button>
            <button 
              v-if="canAssign"
              class="btn btn-danger btn-block"
              @click="cancelReservation"
            >
              <i class="fas fa-times"></i> Cancel Reservation
            </button>
          </template>

          <template v-if="table.status === 'cleaning'">
            <button 
              v-if="canAssign"
              class="btn btn-success btn-block"
              @click="markAvailable"
            >
              <i class="fas fa-check"></i> Mark Available
            </button>
          </template>

          <template v-if="table.status === 'maintenance'">
            <button 
              v-if="canAssign"
              class="btn btn-success btn-block"
              @click="markAvailable"
            >
              <i class="fas fa-check"></i> Mark Available
            </button>
          </template>
        </div>

        <!-- Quick Status Change -->
        <div v-if="canAssign" class="status-section">
          <h4>Quick Status Change</h4>
          <div class="status-buttons">
            <button 
              v-for="status in availableStatuses"
              :key="status.value"
              class="btn btn-sm"
              :class="`btn-${status.color}`"
              :disabled="table.status === status.value"
              @click="updateStatus(status.value)"
            >
              {{ status.label }}
            </button>
          </div>
        </div>

        <!-- Order History -->
        <div v-if="table.order_history && table.order_history.length > 0" class="history-section">
          <h4>Recent Orders</h4>
          <div class="history-list">
            <div 
              v-for="order in table.order_history.slice(0, 5)"
              :key="order.id"
              class="history-item"
            >
              <span class="history-number">#{{ order.order_number }}</span>
              <span class="history-amount">${{ formatAmount(order.total) }}</span>
              <span class="history-date">{{ formatDate(order.created_at) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'TableDetails',

  props: {
    table: {
      type: Object,
      default: null
    },
    canAssign: {
      type: Boolean,
      default: false
    }
  },

  emits: ['assign-order', 'release-table', 'update-status'],

  data() {
    return {
      occupationTimer: null,
      occupationSeconds: 0
    }
  },

  computed: {
    statusIcon() {
      const icons = {
        available: 'fas fa-check-circle',
        occupied: 'fas fa-user',
        reserved: 'fas fa-clock',
        cleaning: 'fas fa-broom',
        maintenance: 'fas fa-wrench'
      }
      return icons[this.table?.status] || icons.available
    },

    occupationTime() {
      const hours = Math.floor(this.occupationSeconds / 3600)
      const minutes = Math.floor((this.occupationSeconds % 3600) / 60)
      
      if (hours > 0) {
        return `${hours}h ${minutes}m`
      }
      return `${minutes}m`
    },

    availableStatuses() {
      return [
        { value: 'available', label: 'Available', color: 'success' },
        { value: 'occupied', label: 'Occupied', color: 'danger' },
        { value: 'reserved', label: 'Reserved', color: 'warning' },
        { value: 'cleaning', label: 'Cleaning', color: 'info' },
        { value: 'maintenance', label: 'Maintenance', color: 'secondary' }
      ]
    }
  },

  watch: {
    table: {
      immediate: true,
      handler(newTable) {
        this.clearTimer()
        if (newTable?.current_order?.occupied_at) {
          this.startTimer(newTable.current_order.occupied_at)
        }
      }
    }
  },

  beforeUnmount() {
    this.clearTimer()
  },

  methods: {
    formatStatus(status) {
      if (!status) return 'Available'
      return status.charAt(0).toUpperCase() + status.slice(1)
    },

    formatTime(dateString) {
      if (!dateString) return 'N/A'
      const date = new Date(dateString)
      return date.toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute: '2-digit' 
      })
    },

    formatDate(dateString) {
      if (!dateString) return 'N/A'
      const date = new Date(dateString)
      return date.toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric' 
      })
    },

    formatAmount(amount) {
      if (!amount) return '0.00'
      return parseFloat(amount).toFixed(2)
    },

    startTimer(occupiedAt) {
      const occupied = new Date(occupiedAt).getTime()
      
      const updateTimer = () => {
        const now = Date.now()
        this.occupationSeconds = Math.floor((now - occupied) / 1000)
      }
      
      updateTimer()
      this.occupationTimer = setInterval(updateTimer, 60000) // Update every minute
    },

    clearTimer() {
      if (this.occupationTimer) {
        clearInterval(this.occupationTimer)
        this.occupationTimer = null
      }
      this.occupationSeconds = 0
    },

    assignOrder() {
      this.$emit('assign-order')
    },

    viewOrder() {
      if (this.table?.current_order) {
        window.location.href = `/orders/${this.table.current_order.id}`
      }
    },

    releaseTable() {
      if (confirm('Are you sure you want to release this table?')) {
        this.$emit('release-table')
      }
    },

    viewReservation() {
      if (this.table?.current_reservation) {
        window.location.href = `/reservations/${this.table.current_reservation.id}`
      }
    },

    cancelReservation() {
      if (confirm('Are you sure you want to cancel this reservation?')) {
        this.updateStatus('available')
      }
    },

    markAvailable() {
      this.updateStatus('available')
    },

    updateStatus(status) {
      this.$emit('update-status', status)
    }
  }
}
</script>

<style scoped>
.table-details {
  width: 320px;
  background: #fff;
  border-left: 1px solid #dee2e6;
  display: flex;
  flex-direction: column;
  overflow-y: auto;
}

.no-selection {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px 20px;
  text-align: center;
  color: #6c757d;
}

.no-selection i {
  font-size: 48px;
  margin-bottom: 16px;
  color: #dee2e6;
}

.table-info-panel {
  display: flex;
  flex-direction: column;
  height: 100%;
}

.panel-header {
  padding: 20px;
  border-bottom: 1px solid #dee2e6;
  background: #f8f9fa;
}

.table-title {
  margin: 0 0 12px 0;
  font-size: 20px;
  font-weight: 700;
  color: #212529;
}

.table-subtitle {
  display: block;
  font-size: 14px;
  font-weight: 400;
  color: #6c757d;
  margin-top: 4px;
}

.status-badge-large {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 14px;
  font-weight: 600;
}

.status-badge-large.status-available {
  background: #d4edda;
  color: #155724;
}

.status-badge-large.status-occupied {
  background: #f8d7da;
  color: #721c24;
}

.status-badge-large.status-reserved {
  background: #fff3cd;
  color: #856404;
}

.status-badge-large.status-cleaning {
  background: #d1ecf1;
  color: #0c5460;
}

.status-badge-large.status-maintenance {
  background: #e2e3e5;
  color: #383d41;
}

.panel-body {
  flex: 1;
  padding: 20px;
  overflow-y: auto;
}

.info-section {
  margin-bottom: 24px;
}

.info-section h4 {
  font-size: 14px;
  font-weight: 600;
  color: #495057;
  margin: 0 0 12px 0;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.info-grid {
  display: grid;
  gap: 8px;
}

.info-item {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
  border-bottom: 1px solid #f1f3f5;
}

.info-item:last-child {
  border-bottom: none;
}

.label {
  color: #6c757d;
  font-size: 13px;
}

.value {
  color: #212529;
  font-weight: 500;
  font-size: 13px;
}

.value.capitalize {
  text-transform: capitalize;
}

.value.amount {
  color: #28a745;
  font-weight: 600;
}

.order-card,
.reservation-card {
  background: #f8f9fa;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  padding: 16px;
}

.order-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 12px;
  padding-bottom: 12px;
  border-bottom: 1px solid #dee2e6;
}

.order-number {
  font-weight: 700;
  color: #007bff;
}

.order-time {
  color: #6c757d;
  font-size: 12px;
}

.order-details .detail-row,
.reservation-card .detail-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
}

.order-details .detail-row:last-child,
.reservation-card .detail-row:last-child {
  margin-bottom: 0;
}

.occupation-timer {
  margin-top: 12px;
  padding-top: 12px;
  border-top: 1px solid #dee2e6;
  color: #6c757d;
  font-size: 13px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.action-section {
  margin-bottom: 24px;
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 10px 16px;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-block {
  width: 100%;
  margin-bottom: 8px;
}

.btn-primary {
  background: #007bff;
  color: white;
}

.btn-primary:hover {
  background: #0056b3;
}

.btn-info {
  background: #17a2b8;
  color: white;
}

.btn-info:hover {
  background: #138496;
}

.btn-success {
  background: #28a745;
  color: white;
}

.btn-success:hover {
  background: #218838;
}

.btn-warning {
  background: #ffc107;
  color: #212529;
}

.btn-warning:hover {
  background: #e0a800;
}

.btn-danger {
  background: #dc3545;
  color: white;
}

.btn-danger:hover {
  background: #c82333;
}

.btn-secondary {
  background: #6c757d;
  color: white;
}

.btn-sm {
  padding: 6px 12px;
  font-size: 12px;
}

.status-section {
  margin-bottom: 24px;
}

.status-buttons {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.history-section {
  margin-bottom: 24px;
}

.history-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.history-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 12px;
  background: #f8f9fa;
  border-radius: 6px;
  font-size: 13px;
}

.history-number {
  font-weight: 600;
  color: #007bff;
}

.history-amount {
  font-weight: 600;
  color: #28a745;
}

.history-date {
  color: #6c757d;
  font-size: 12px;
}

@media (max-width: 768px) {
  .table-details {
    width: 100%;
    border-left: none;
    border-top: 1px solid #dee2e6;
    max-height: 50vh;
  }
}
</style>

<template>
  <div 
    class="order-card"
    :class="[`status-${order.state}`, { selectable, compact }]"
    @click="handleClick"
  >
    <!-- Header -->
    <div class="card-header">
      <div class="order-type-icon" :class="order.type">
        <i :class="typeIcon"></i>
      </div>
      
      <div class="order-info">
        <div class="order-number">#{{ order.POS_number }}</div>
        <div class="order-meta">
          <span class="table-info">
            <i class="fas fa-chair"></i> {{ order.table_number || 'N/A' }}
          </span>
          <span class="waiter-info">
            <i class="fas fa-user"></i> {{ order.waiter_name }}
          </span>
        </div>
      </div>
      
      <StatusBadge 
        :status="order.state" 
        type="order"
        size="small"
        class="order-status"
      />
    </div>
    
    <!-- Customer & Details -->
    <div class="card-body" v-if="!compact">
      <div class="customer-row">
        <div class="customer-info">
          <i class="fas fa-user-circle"></i>
          <span>{{ customerName }}</span>
        </div>
        <div class="time-info" :title="order.created_at">
          <i class="fas fa-clock"></i>
          {{ relativeTime }}
        </div>
      </div>
      
      <div class="order-summary">
        <div class="summary-item">
          <i class="fas fa-shopping-basket"></i>
          <span>{{ itemCount }} items</span>
        </div>
        <div class="summary-total">
          <AmountDisplay :amount="order.total_amount" size="large" />
        </div>
      </div>
    </div>
    
    <!-- Compact Mode Info -->
    <div class="card-body compact" v-else>
      <div class="compact-row">
        <span>{{ itemCount }} items</span>
        <AmountDisplay :amount="order.total_amount" />
      </div>
    </div>
    
    <!-- Actions -->
    <div class="card-actions" v-if="showActions && !compact">
      <button 
        v-if="canEdit"
        @click.stop="handleEdit"
        class="btn-action"
        title="Edit Order"
      >
        <i class="fas fa-edit"></i>
        <span>Edit</span>
      </button>
      
      <button 
        v-if="canAssign && order.state === 'preparing'"
        @click.stop="handleAssign"
        class="btn-action"
        title="Assign to Table"
      >
        <i class="fas fa-chair"></i>
        <span>Assign</span>
      </button>
      
      <button 
        v-if="canPay && order.state === 'served'"
        @click.stop="handlePay"
        class="btn-action primary"
        title="Process Payment"
      >
        <i class="fas fa-credit-card"></i>
        <span>Pay</span>
      </button>
      
      <button 
        v-if="canCancel && order.state !== 'closed'"
        @click.stop="handleCancel"
        class="btn-action danger"
        title="Cancel Order"
      >
        <i class="fas fa-times"></i>
        <span>Cancel</span>
      </button>
    </div>
  </div>
</template>

<script>
import StatusBadge from './StatusBadge.vue'
import AmountDisplay from './AmountDisplay.vue'

export default {
  name: 'OrderCard',

  components: {
    StatusBadge,
    AmountDisplay
  },

  props: {
    order: {
      type: Object,
      required: true
    },
    showActions: {
      type: Boolean,
      default: true
    },
    selectable: {
      type: Boolean,
      default: false
    },
    compact: {
      type: Boolean,
      default: false
    },
    userType: {
      type: String,
      default: 'waiter'
    }
  },

  emits: ['click', 'edit', 'cancel', 'pay', 'assign'],

  computed: {
    typeIcon() {
      const icons = {
        'dine-in': 'fas fa-utensils',
        'take-away': 'fas fa-shopping-bag',
        'delivery': 'fas fa-motorcycle'
      }
      return icons[this.order.type] || 'fas fa-question'
    },

    customerName() {
      return this.order.customer?.name || 'Walk-in'
    },

    itemCount() {
      return this.order.item_count || this.order.items?.length || 0
    },

    relativeTime() {
      if (!this.order.created_at) return 'N/A'
      
      const date = new Date(this.order.created_at)
      const now = new Date()
      const diffMs = now - date
      const diffMins = Math.floor(diffMs / 60000)
      const diffHours = Math.floor(diffMs / 3600000)
      const diffDays = Math.floor(diffMs / 86400000)

      if (diffMins < 1) return 'Just now'
      if (diffMins < 60) return `${diffMins}m ago`
      if (diffHours < 24) return `${diffHours}h ago`
      if (diffDays < 7) return `${diffDays}d ago`
      
      return date.toLocaleDateString()
    },

    canEdit() {
      return ['admin', 'manager', 'waiter'].includes(this.userType) &&
             this.order.state !== 'closed'
    },

    canCancel() {
      return ['admin', 'manager', 'waiter'].includes(this.userType) &&
             this.order.state !== 'closed'
    },

    canPay() {
      return ['admin', 'manager', 'cashier'].includes(this.userType) &&
             this.order.state === 'served'
    },

    canAssign() {
      return ['admin', 'manager', 'waiter'].includes(this.userType)
    }
  },

  methods: {
    handleClick() {
      this.$emit('click', this.order)
    },

    handleEdit() {
      this.$emit('edit', this.order)
    },

    handleCancel() {
      if (confirm(`Cancel order #${this.order.POS_number}?`)) {
        this.$emit('cancel', this.order)
      }
    },

    handlePay() {
      this.$emit('pay', this.order)
    },

    handleAssign() {
      this.$emit('assign', this.order)
    }
  }
}
</script>

<style scoped>
.order-card {
  background: #fff;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  padding: 16px;
  transition: all 0.2s ease;
  cursor: default;
}

.order-card:hover {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.order-card.selectable {
  cursor: pointer;
}

.order-card.selectable:hover {
  border-color: #007bff;
  box-shadow: 0 2px 12px rgba(0, 123, 255, 0.15);
}

/* Status-based borders */
.status-preparing {
  border-left: 4px solid #17a2b8;
}

.status-served {
  border-left: 4px solid #28a745;
}

.status-closed {
  border-left: 4px solid #6c757d;
  opacity: 0.8;
}

/* Compact mode */
.compact {
  padding: 12px;
}

.compact .card-header {
  margin-bottom: 0;
}

/* Header */
.card-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
}

.order-type-icon {
  width: 40px;
  height: 40px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  color: #fff;
}

.order-type-icon.dine-in {
  background: #28a745;
}

.order-type-icon.take-away {
  background: #ffc107;
  color: #212529;
}

.order-type-icon.delivery {
  background: #17a2b8;
}

.order-info {
  flex: 1;
  min-width: 0;
}

.order-number {
  font-weight: 700;
  font-size: 16px;
  color: #212529;
  margin-bottom: 4px;
}

.order-meta {
  display: flex;
  gap: 12px;
  font-size: 12px;
  color: #6c757d;
}

.order-meta i {
  margin-right: 4px;
}

.order-status {
  flex-shrink: 0;
}

/* Body */
.card-body {
  border-top: 1px solid #e9ecef;
  padding-top: 12px;
}

.customer-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.customer-info {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #495057;
  font-size: 14px;
}

.customer-info i {
  font-size: 20px;
  color: #6c757d;
}

.time-info {
  font-size: 12px;
  color: #6c757d;
  display: flex;
  align-items: center;
  gap: 4px;
}

.order-summary {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.summary-item {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  color: #6c757d;
}

.summary-total {
  font-weight: 700;
}

/* Compact body */
.card-body.compact {
  border-top: none;
  padding-top: 8px;
}

.compact-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 13px;
  color: #6c757d;
}

/* Actions */
.card-actions {
  display: flex;
  gap: 8px;
  margin-top: 12px;
  padding-top: 12px;
  border-top: 1px solid #e9ecef;
}

.btn-action {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 12px;
  border: 1px solid #dee2e6;
  background: #fff;
  border-radius: 6px;
  font-size: 13px;
  color: #495057;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-action:hover {
  background: #f8f9fa;
  border-color: #adb5bd;
}

.btn-action.primary {
  background: #007bff;
  border-color: #007bff;
  color: #fff;
}

.btn-action.primary:hover {
  background: #0056b3;
  border-color: #0056b3;
}

.btn-action.danger {
  background: #dc3545;
  border-color: #dc3545;
  color: #fff;
}

.btn-action.danger:hover {
  background: #c82333;
  border-color: #c82333;
}

/* Responsive */
@media (max-width: 576px) {
  .card-header {
    flex-wrap: wrap;
  }
  
  .order-status {
    width: 100%;
    margin-top: 8px;
  }
  
  .card-actions {
    flex-wrap: wrap;
  }
  
  .btn-action {
    flex: 1;
    justify-content: center;
  }
}
</style>
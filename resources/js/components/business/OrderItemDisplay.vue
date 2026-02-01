<template>
  <div class="order-item" :class="[`status-${item.status || 'pending'}`, { compact }]">
    <div class="item-main">
      <!-- Quantity Controls -->
      <div class="item-quantity" v-if="canEditQuantity && !compact">
        <button 
          class="qty-btn" 
          @click="decrement"
          :disabled="item.quantity <= 1"
        >
          <i class="fas fa-minus"></i>
        </button>
        <span class="qty-value">{{ item.quantity }}</span>
        <button class="qty-btn" @click="increment">
          <i class="fas fa-plus"></i>
        </button>
      </div>
      
      <div class="item-quantity" v-else>
        <span class="qty-badge">{{ item.quantity }}×</span>
      </div>
      
      <!-- Product Details -->
      <div class="item-details">
        <div class="product-name">{{ item.product_name }}</div>
        <div class="product-meta">
          <span class="unit-price">
            <AmountDisplay :amount="item.unit_price" size="small" />
          </span>
          <span v-if="item.notes" class="item-notes">
            <i class="fas fa-comment"></i> {{ item.notes }}
          </span>
        </div>
      </div>
      
      <!-- Total Price -->
      <div class="item-total">
        <AmountDisplay :amount="item.total_price" />
      </div>
    </div>
    
    <!-- Actions -->
    <div class="item-actions" v-if="showActions && hasAnyPermission && !compact">
      <button 
        v-if="canUpdateStatus && item.status !== 'ready'"
        @click="markReady"
        class="btn-icon success"
        title="Mark as Ready"
      >
        <i class="fas fa-check"></i>
      </button>
      
      <button 
        v-if="canEditQuantity"
        @click="editNote"
        class="btn-icon"
        title="Add/Edit Note"
      >
        <i class="fas fa-comment"></i>
      </button>
      
      <button 
        v-if="canDelete"
        @click="confirmDelete"
        class="btn-icon danger"
        title="Delete Item"
      >
        <i class="fas fa-trash"></i>
      </button>
    </div>
    
    <!-- Status Badge -->
    <div class="item-status" v-if="!compact">
      <StatusBadge 
        :status="item.status || 'pending'" 
        type="order"
        size="small"
      />
    </div>
  </div>
</template>

<script>
import StatusBadge from './StatusBadge.vue'
import AmountDisplay from './AmountDisplay.vue'

export default {
  name: 'OrderItemDisplay',

  components: {
    StatusBadge,
    AmountDisplay
  },

  props: {
    item: {
      type: Object,
      required: true,
      validator: (value) => {
        return value.product_name !== undefined && 
               value.quantity !== undefined && 
               value.unit_price !== undefined
      }
    },
    orderStatus: {
      type: String,
      default: 'preparing'
    },
    showActions: {
      type: Boolean,
      default: true
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

  emits: ['update-quantity', 'delete-item', 'update-status', 'add-note'],

  computed: {
    canEditQuantity() {
      // Admin, Manager, Waiter can edit quantity
      return ['admin', 'manager', 'waiter'].includes(this.userType) &&
             this.orderStatus !== 'closed'
    },

    canDelete() {
      // Admin, Manager, Waiter can delete (if order not closed)
      return ['admin', 'manager', 'waiter'].includes(this.userType) &&
             this.orderStatus !== 'closed'
    },

    canUpdateStatus() {
      // Only Chef can update item status to ready
      return this.userType === 'chef' || this.userType === 'admin'
    },

    hasAnyPermission() {
      return this.canEditQuantity || this.canDelete || this.canUpdateStatus
    }
  },

  methods: {
    increment() {
      this.$emit('update-quantity', {
        itemId: this.item.id,
        quantity: this.item.quantity + 1
      })
    },

    decrement() {
      if (this.item.quantity > 1) {
        this.$emit('update-quantity', {
          itemId: this.item.id,
          quantity: this.item.quantity - 1
        })
      }
    },

    confirmDelete() {
      if (confirm(`Remove ${this.item.product_name} from order?`)) {
        this.$emit('delete-item', this.item.id)
      }
    },

    markReady() {
      this.$emit('update-status', {
        itemId: this.item.id,
        status: 'ready'
      })
    },

    editNote() {
      const note = prompt('Add special instructions:', this.item.notes || '')
      if (note !== null) {
        this.$emit('add-note', {
          itemId: this.item.id,
          note: note
        })
      }
    }
  }
}
</script>

<style scoped>
.order-item {
  display: flex;
  flex-direction: column;
  padding: 12px;
  border-bottom: 1px solid #e9ecef;
  background: #fff;
  transition: background-color 0.2s;
}

.order-item:hover {
  background: #f8f9fa;
}

.order-item:last-child {
  border-bottom: none;
}

/* Status-based styling */
.status-ready {
  background: #f0f9f4;
  border-left: 3px solid #28a745;
}

.status-served {
  opacity: 0.7;
  background: #f8f9fa;
}

.status-pending {
  border-left: 3px solid #ffc107;
}

.status-preparing {
  border-left: 3px solid #17a2b8;
}

/* Compact mode */
.compact {
  padding: 8px 12px;
}

.compact .item-actions,
.compact .item-status {
  display: none;
}

/* Main content layout */
.item-main {
  display: flex;
  align-items: center;
  gap: 12px;
}

/* Quantity section */
.item-quantity {
  display: flex;
  align-items: center;
  gap: 8px;
  min-width: 80px;
}

.qty-btn {
  width: 28px;
  height: 28px;
  border: 1px solid #dee2e6;
  background: #fff;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.qty-btn:hover:not(:disabled) {
  background: #e9ecef;
  border-color: #adb5bd;
}

.qty-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.qty-value {
  font-weight: 600;
  min-width: 24px;
  text-align: center;
}

.qty-badge {
  font-weight: 600;
  color: #495057;
  font-size: 14px;
}

/* Product details */
.item-details {
  flex: 1;
  min-width: 0;
}

.product-name {
  font-weight: 600;
  color: #212529;
  margin-bottom: 4px;
  font-size: 14px;
}

.product-meta {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 12px;
}

.unit-price {
  color: #6c757d;
}

.item-notes {
  color: #856404;
  background: #fff3cd;
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 11px;
  display: flex;
  align-items: center;
  gap: 4px;
}

.item-notes i {
  font-size: 10px;
}

/* Total price */
.item-total {
  font-weight: 700;
  color: #212529;
  min-width: 80px;
  text-align: right;
}

/* Actions */
.item-actions {
  display: flex;
  gap: 8px;
  margin-top: 8px;
  padding-top: 8px;
  border-top: 1px dashed #dee2e6;
}

.btn-icon {
  width: 32px;
  height: 32px;
  border: none;
  background: #e9ecef;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  color: #495057;
}

.btn-icon:hover {
  background: #dee2e6;
  transform: translateY(-1px);
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

/* Status badge container */
.item-status {
  margin-top: 8px;
  display: flex;
  justify-content: flex-end;
}

/* Responsive */
@media (max-width: 576px) {
  .item-main {
    flex-wrap: wrap;
  }
  
  .item-total {
    width: 100%;
    text-align: left;
    margin-top: 8px;
    padding-left: 92px;
  }
}
</style>
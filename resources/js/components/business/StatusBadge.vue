<template>
  <span 
    class="status-badge"
    :class="[`status-${config.color}`, `size-${size}`, { 'no-icon': !showIcon }]"
  >
    <i v-if="showIcon" :class="['fas', config.icon]"></i>
    <span v-if="showLabel" class="status-label">{{ config.label }}</span>
  </span>
</template>

<script>
export default {
  name: 'StatusBadge',

  props: {
    status: {
      type: String,
      required: true
    },
    type: {
      type: String,
      default: 'order',
      validator: (value) => ['order', 'table', 'payment', 'sync'].includes(value)
    },
    size: {
      type: String,
      default: 'medium',
      validator: (value) => ['small', 'medium', 'large'].includes(value)
    },
    showIcon: {
      type: Boolean,
      default: true
    },
    showLabel: {
      type: Boolean,
      default: true
    }
  },

  computed: {
    config() {
      const statusConfig = {
        order: {
          preparing: { color: 'blue', icon: 'fa-spinner', label: 'Preparing' },
          served: { color: 'green', icon: 'fa-check', label: 'Served' },
          closed: { color: 'gray', icon: 'fa-lock', label: 'Closed' },
          wastage: { color: 'red', icon: 'fa-trash', label: 'Wastage' },
          pending: { color: 'orange', icon: 'fa-clock', label: 'Pending' }
        },
        table: {
          available: { color: 'green', icon: 'fa-check-circle', label: 'Available' },
          occupied: { color: 'red', icon: 'fa-user', label: 'Occupied' },
          reserved: { color: 'yellow', icon: 'fa-clock', label: 'Reserved' },
          cleaning: { color: 'blue', icon: 'fa-broom', label: 'Cleaning' },
          maintenance: { color: 'gray', icon: 'fa-wrench', label: 'Maintenance' }
        },
        payment: {
          pending: { color: 'orange', icon: 'fa-clock', label: 'Pending' },
          paid: { color: 'green', icon: 'fa-check', label: 'Paid' },
          refunded: { color: 'purple', icon: 'fa-undo', label: 'Refunded' },
          failed: { color: 'red', icon: 'fa-times', label: 'Failed' }
        },
        sync: {
          synced: { color: 'green', icon: 'fa-check-circle', label: 'Synced' },
          pending: { color: 'orange', icon: 'fa-clock', label: 'Pending' },
          syncing: { color: 'blue', icon: 'fa-sync', label: 'Syncing' },
          error: { color: 'red', icon: 'fa-exclamation-circle', label: 'Error' },
          conflict: { color: 'red', icon: 'fa-exclamation-triangle', label: 'Conflict' },
          failed: { color: 'red', icon: 'fa-times', label: 'Failed' }
        }
      }

      const typeConfig = statusConfig[this.type] || statusConfig.order
      return typeConfig[this.status] || { 
        color: 'gray', 
        icon: 'fa-question', 
        label: this.status || 'Unknown' 
      }
    }
  }
}
</script>

<style scoped>
.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  border-radius: 20px;
  font-weight: 600;
  white-space: nowrap;
}

/* Size variants */
.size-small {
  padding: 2px 8px;
  font-size: 11px;
}

.size-small i {
  font-size: 10px;
}

.size-medium {
  padding: 4px 10px;
  font-size: 13px;
}

.size-medium i {
  font-size: 12px;
}

.size-large {
  padding: 6px 14px;
  font-size: 15px;
}

.size-large i {
  font-size: 14px;
}

/* Color variants */
.status-green {
  background: #d4edda;
  color: #155724;
}

.status-blue {
  background: #d1ecf1;
  color: #0c5460;
}

.status-yellow,
.status-orange {
  background: #fff3cd;
  color: #856404;
}

.status-red {
  background: #f8d7da;
  color: #721c24;
}

.status-gray {
  background: #e2e3e5;
  color: #383d41;
}

.status-purple {
  background: #e2d4f0;
  color: #5a2d8c;
}

/* No icon variant */
.no-icon {
  padding: 4px 10px;
}

/* Icon animation for spinner */
.status-blue .fa-spinner {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>
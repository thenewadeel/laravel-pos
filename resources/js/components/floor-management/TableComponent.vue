<template>
  <div
    class="table-component"
    :class="[
      `shape-${table.shape || 'rectangle'}`,
      `status-${table.status || 'available'}`,
      { selected: isSelected, 'edit-mode': isEditMode }
    ]"
    :style="tableStyle"
    @click="handleClick"
    @mousedown="handleMouseDown"
  >
    <div class="table-status-badge" :class="`status-${table.status}`">
      <i :class="statusIcon"></i>
    </div>

    <div class="table-content">
      <div class="table-number">{{ table.table_number }}</div>
      <div v-if="table.name" class="table-name">{{ table.name }}</div>
      <div class="table-capacity">
        <i class="fas fa-users"></i> {{ table.capacity || 0 }}
      </div>
    </div>

    <div v-if="table.current_order" class="order-indicator">
      <i class="fas fa-receipt"></i>
    </div>

    <!-- Resize handles (edit mode only) -->
    <template v-if="isEditMode">
      <div class="resize-handle resize-se" @mousedown.stop="startResize('se', $event)" />
      <div class="resize-handle resize-sw" @mousedown.stop="startResize('sw', $event)" />
      <div class="resize-handle resize-ne" @mousedown.stop="startResize('ne', $event)" />
      <div class="resize-handle resize-nw" @mousedown.stop="startResize('nw', $event)" />
    </template>
  </div>
</template>

<script>
export default {
  name: 'TableComponent',

  props: {
    table: {
      type: Object,
      required: true
    },
    isSelected: {
      type: Boolean,
      default: false
    },
    isEditMode: {
      type: Boolean,
      default: false
    }
  },

  emits: ['click', 'drag-start', 'drag-end'],

  computed: {
    tableStyle() {
      const width = this.table.width || 100
      const height = this.table.height || 100
      const x = this.table.position_x || 0
      const y = this.table.position_y || 0

      return {
        position: 'absolute',
        left: `${x}px`,
        top: `${y}px`,
        width: `${width}px`,
        height: `${height}px`,
        cursor: this.isEditMode ? 'move' : 'pointer'
      }
    },

    statusIcon() {
      const icons = {
        available: 'fas fa-check-circle',
        occupied: 'fas fa-user',
        reserved: 'fas fa-clock',
        cleaning: 'fas fa-broom',
        maintenance: 'fas fa-wrench'
      }
      return icons[this.table.status] || icons.available
    }
  },

  methods: {
    handleClick(event) {
      if (!this.isEditMode) {
        this.$emit('click')
      }
    },

    handleMouseDown(event) {
      if (this.isEditMode) {
        this.$emit('drag-start', this.table, event)
      }
    },

    startResize(direction, event) {
      event.preventDefault()
      event.stopPropagation()
      // Resize functionality would be implemented here
      console.log(`Resize ${direction} started`)
    }
  }
}
</script>

<style scoped>
.table-component {
  background: white;
  border: 3px solid #dee2e6;
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  user-select: none;
}

.table-component:hover {
  transform: scale(1.02);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.table-component.selected {
  border-color: #007bff;
  box-shadow: 0 0 0 4px rgba(0, 123, 255, 0.3);
}

/* Shapes */
.table-component.shape-circle {
  border-radius: 50%;
}

.table-component.shape-oval {
  border-radius: 50%;
}

/* Status Colors */
.table-component.status-available {
  border-color: #28a745;
  background: #d4edda;
}

.table-component.status-occupied {
  border-color: #dc3545;
  background: #f8d7da;
}

.table-component.status-reserved {
  border-color: #ffc107;
  background: #fff3cd;
}

.table-component.status-cleaning {
  border-color: #17a2b8;
  background: #d1ecf1;
}

.table-component.status-maintenance {
  border-color: #6c757d;
  background: #e2e3e5;
}

.table-status-badge {
  position: absolute;
  top: -10px;
  right: -10px;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  color: white;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.table-status-badge.status-available {
  background: #28a745;
}

.table-status-badge.status-occupied {
  background: #dc3545;
}

.table-status-badge.status-reserved {
  background: #ffc107;
  color: #212529;
}

.table-status-badge.status-cleaning {
  background: #17a2b8;
}

.table-status-badge.status-maintenance {
  background: #6c757d;
}

.table-content {
  text-align: center;
  padding: 8px;
}

.table-number {
  font-size: 18px;
  font-weight: 700;
  color: #212529;
  line-height: 1.2;
}

.table-name {
  font-size: 11px;
  color: #6c757d;
  margin-top: 2px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 90%;
}

.table-capacity {
  font-size: 12px;
  color: #6c757d;
  margin-top: 4px;
}

.table-capacity i {
  margin-right: 4px;
}

.order-indicator {
  position: absolute;
  bottom: 4px;
  right: 4px;
  font-size: 14px;
  color: #007bff;
}

/* Resize handles */
.resize-handle {
  position: absolute;
  width: 12px;
  height: 12px;
  background: #007bff;
  border: 2px solid white;
  border-radius: 50%;
  cursor: pointer;
  display: none;
}

.table-component.edit-mode .resize-handle {
  display: block;
}

.resize-handle:hover {
  background: #0056b3;
  transform: scale(1.2);
}

.resize-se {
  bottom: -6px;
  right: -6px;
  cursor: se-resize;
}

.resize-sw {
  bottom: -6px;
  left: -6px;
  cursor: sw-resize;
}

.resize-ne {
  top: -6px;
  right: -6px;
  cursor: ne-resize;
}

.resize-nw {
  top: -6px;
  left: -6px;
  cursor: nw-resize;
}

@media (max-width: 768px) {
  .table-component {
    min-width: 80px;
    min-height: 80px;
  }
  
  .table-number {
    font-size: 16px;
  }
  
  .table-name {
    font-size: 10px;
  }
  
  .table-capacity {
    font-size: 11px;
  }
}
</style>

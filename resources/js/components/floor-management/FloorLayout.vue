<template>
  <div class="floor-layout" ref="layoutContainer">
    <div
      class="floor-canvas"
      :style="canvasStyle"
      @click="deselectTable"
    >
      <TableComponent
        v-for="table in tables"
        :key="table.id"
        :table="table"
        :is-selected="selectedTable?.id === table.id"
        :is-edit-mode="isEditMode"
        @click="selectTable(table)"
        @drag-start="startDrag"
        @drag-end="endDrag"
      />
    </div>
    
    <div v-if="tables.length === 0" class="empty-floor">
      <div class="empty-message">
        <i class="fas fa-chair"></i>
        <p>No tables on this floor</p>
        <p v-if="isEditMode" class="hint">Click "Add Table" to get started</p>
      </div>
    </div>
  </div>
</template>

<script>
import TableComponent from './TableComponent.vue'

export default {
  name: 'FloorLayout',

  components: {
    TableComponent
  },

  props: {
    floor: {
      type: Object,
      default: null
    },
    tables: {
      type: Array,
      required: true,
      default: () => []
    },
    isEditMode: {
      type: Boolean,
      default: false
    },
    selectedTable: {
      type: Object,
      default: null
    }
  },

  emits: ['select-table', 'update-table-position'],

  data() {
    return {
      isDragging: false,
      draggedTable: null,
      dragStartX: 0,
      dragStartY: 0,
      tableStartX: 0,
      tableStartY: 0
    }
  },

  computed: {
    canvasStyle() {
      return {
        width: '100%',
        height: '100%',
        position: 'relative',
        background: this.floor?.background_color || '#f8f9fa',
        backgroundImage: this.isEditMode 
          ? 'linear-gradient(#dee2e6 1px, transparent 1px), linear-gradient(90deg, #dee2e6 1px, transparent 1px)'
          : 'none',
        backgroundSize: this.isEditMode ? '20px 20px' : 'auto'
      }
    }
  },

  mounted() {
    if (this.isEditMode) {
      document.addEventListener('mousemove', this.handleDrag)
      document.addEventListener('mouseup', this.endDrag)
    }
  },

  beforeUnmount() {
    document.removeEventListener('mousemove', this.handleDrag)
    document.removeEventListener('mouseup', this.endDrag)
  },

  methods: {
    selectTable(table) {
      if (!this.isDragging) {
        this.$emit('select-table', table)
      }
    },

    deselectTable(event) {
      if (event.target === event.currentTarget && !this.isDragging) {
        this.$emit('select-table', null)
      }
    },

    startDrag(table, event) {
      if (!this.isEditMode) return
      
      this.isDragging = true
      this.draggedTable = table
      this.dragStartX = event.clientX
      this.dragStartY = event.clientY
      this.tableStartX = table.position_x || 0
      this.tableStartY = table.position_y || 0
      
      event.preventDefault()
      event.stopPropagation()
    },

    handleDrag(event) {
      if (!this.isDragging || !this.draggedTable) return
      
      const deltaX = event.clientX - this.dragStartX
      const deltaY = event.clientY - this.dragStartY
      
      const newX = Math.max(0, this.tableStartX + deltaX)
      const newY = Math.max(0, this.tableStartY + deltaY)
      
      this.draggedTable.position_x = newX
      this.draggedTable.position_y = newY
    },

    endDrag() {
      if (this.isDragging && this.draggedTable) {
        this.$emit('update-table-position', 
          this.draggedTable.id, 
          this.draggedTable.position_x, 
          this.draggedTable.position_y
        )
      }
      
      this.isDragging = false
      this.draggedTable = null
    }
  }
}
</script>

<style scoped>
.floor-layout {
  flex: 1;
  overflow: auto;
  position: relative;
  background: #fff;
}

.floor-canvas {
  min-width: 100%;
  min-height: 100%;
}

.empty-floor {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.empty-message {
  text-align: center;
  color: #6c757d;
}

.empty-message i {
  font-size: 48px;
  margin-bottom: 16px;
  color: #dee2e6;
}

.empty-message p {
  margin: 0;
  font-size: 16px;
}

.empty-message .hint {
  font-size: 14px;
  color: #adb5bd;
  margin-top: 8px;
}

@media (max-width: 768px) {
  .floor-layout {
    overflow: auto;
  }
  
  .floor-canvas {
    min-height: 400px;
  }
}
</style>

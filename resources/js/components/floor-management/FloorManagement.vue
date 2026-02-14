<template>
  <div class="floor-management">
    <div class="floor-management-container">
      <!-- Floor Selector Sidebar -->
      <FloorSelector 
        :floors="floors" 
        :active-floor-id="activeFloorId"
        @select="selectFloor" />
      
      <!-- Main Content Area -->
      <div class="floor-content">
        <!-- Admin Toolbar -->
        <FloorToolbar 
          v-if="canManageFloors"
          :is-edit-mode="isEditMode"
          @toggle-edit="toggleEditMode"
          @add-table="showAddTableModal"
          @save-layout="saveLayout" />
        
        <!-- Floor Layout Canvas -->
        <FloorLayout 
          :floor="activeFloor"
          :tables="activeFloorTables"
          :is-edit-mode="isEditMode"
          :selected-table="selectedTable"
          @select-table="selectTable"
          @update-table-position="updateTablePosition" />
      </div>
      
      <!-- Table Details Sidebar -->
      <TableDetails 
        :table="selectedTable"
        :can-assign="canAssignOrders"
        @assign-order="showAssignOrderModal"
        @release-table="releaseTable"
        @update-status="updateTableStatus" />
    </div>
    
    <!-- Modals -->
    <OrderAssignmentModal 
      v-if="showAssignmentModal"
      :table="selectedTable"
      @assign="assignOrder"
      @close="showAssignmentModal = false" />
  </div>
</template>

<script>
export default {
  name: 'FloorManagement',
  
  props: {
    shopId: {
      type: Number,
      required: true
    },
    user: {
      type: Object,
      required: true
    }
  },
  
  data() {
    return {
      floors: [],
      activeFloorId: null,
      selectedTableId: null,
      isLoading: false,
      isEditMode: false,
      showAssignmentModal: false,
      syncStatus: 'synced'
    }
  },
  
  computed: {
    activeFloor() {
      return this.floors.find(f => f.id === this.activeFloorId) || null
    },
    
    activeFloorTables() {
      return this.activeFloor?.tables || []
    },
    
    selectedTable() {
      if (!this.activeFloor || !this.selectedTableId) return null
      return this.activeFloor.tables.find(t => t.id === this.selectedTableId) || null
    },
    
    canManageFloors() {
      return this.user.type === 'admin'
    },
    
    canAssignOrders() {
      return ['admin', 'manager', 'waiter', 'cashier'].includes(this.user.type)
    }
  },
  
  mounted() {
    this.loadFloors()
  },
  
  methods: {
    async loadFloors() {
      this.isLoading = true
      try {
        const response = await axios.get(`/api/v1/floors?shop_id=${this.shopId}`)
        this.floors = response.data.data.floors
        
        // Select first floor if none selected
        if (!this.activeFloorId && this.floors.length > 0) {
          this.activeFloorId = this.floors[0].id
        }
      } catch (error) {
        console.error('Failed to load floors:', error)
        this.$notify.error('Failed to load floor data')
      } finally {
        this.isLoading = false
      }
    },
    
    selectFloor(floorId) {
      this.activeFloorId = floorId
      this.selectedTableId = null
      this.isEditMode = false
    },
    
    selectTable(table) {
      this.selectedTableId = table ? table.id : null
    },
    
    toggleEditMode() {
      if (!this.canManageFloors) return
      this.isEditMode = !this.isEditMode
    },
    
    showAddTableModal() {
      // TODO: Implement add table modal
      console.log('Add table modal')
    },
    
    async saveLayout() {
      // TODO: Implement layout save
      this.$notify.success('Layout saved successfully')
      this.isEditMode = false
    },
    
    async updateTablePosition(tableId, x, y) {
      try {
        await axios.put(`/api/v1/tables/${tableId}`, {
          position_x: x,
          position_y: y
        })
      } catch (error) {
        console.error('Failed to update table position:', error)
      }
    },
    
    showAssignOrderModal() {
      if (!this.selectedTable || !this.canAssignOrders) return
      this.showAssignmentModal = true
    },
    
    async assignOrder(orderId) {
      try {
        await axios.post(`/api/v1/tables/${this.selectedTable.id}/assign-order`, {
          order_id: orderId
        })
        
        this.showAssignmentModal = false
        this.$notify.success('Order assigned successfully')
        await this.loadFloors()
      } catch (error) {
        console.error('Failed to assign order:', error)
        this.$notify.error('Failed to assign order')
      }
    },
    
    async releaseTable() {
      if (!this.selectedTable) return
      
      try {
        await axios.post(`/api/v1/tables/${this.selectedTable.id}/release`)
        
        this.$notify.success('Table released successfully')
        await this.loadFloors()
      } catch (error) {
        console.error('Failed to release table:', error)
        this.$notify.error('Failed to release table')
      }
    },
    
    async updateTableStatus(status) {
      if (!this.selectedTable) return
      
      try {
        await axios.patch(`/api/v1/tables/${this.selectedTable.id}/status`, {
          status: status
        })
        
        this.$notify.success('Table status updated')
        await this.loadFloors()
      } catch (error) {
        console.error('Failed to update table status:', error)
        this.$notify.error('Failed to update status')
      }
    }
  }
}
</script>

<style scoped>
.floor-management {
  height: 100vh;
  display: flex;
  flex-direction: column;
}

.floor-management-container {
  display: flex;
  flex: 1;
  overflow: hidden;
}

.floor-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}
</style>

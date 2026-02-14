<template>
  <div class="floor-selector">
    <h3 class="floor-selector-title">Floors</h3>
    <div class="floor-list">
      <div
        v-for="floor in floors"
        :key="floor.id"
        class="floor-item"
        :class="{ active: floor.id === activeFloorId }"
        @click="selectFloor(floor.id)"
      >
        <div class="floor-info">
          <span class="floor-name">{{ floor.name }}</span>
          <span class="floor-badge" :class="getBadgeClass(floor)">
            {{ getAvailableCount(floor) }}/{{ floor.tables?.length || 0 }}
          </span>
        </div>
        <div v-if="floor.description" class="floor-description">
          {{ floor.description }}
        </div>
      </div>
    </div>
    <div v-if="floors.length === 0" class="no-floors">
      No floors available
    </div>
  </div>
</template>

<script>
export default {
  name: 'FloorSelector',

  props: {
    floors: {
      type: Array,
      required: true,
      default: () => []
    },
    activeFloorId: {
      type: Number,
      default: null
    }
  },

  emits: ['select'],

  methods: {
    selectFloor(floorId) {
      if (floorId !== this.activeFloorId) {
        this.$emit('select', floorId)
      }
    },

    getAvailableCount(floor) {
      if (!floor.tables) return 0
      return floor.tables.filter(t => t.status === 'available').length
    },

    getBadgeClass(floor) {
      const available = this.getAvailableCount(floor)
      const total = floor.tables?.length || 0
      
      if (available === 0) return 'full'
      if (available === total) return 'all-available'
      return 'partial'
    }
  }
}
</script>

<style scoped>
.floor-selector {
  width: 250px;
  background: #f8f9fa;
  border-right: 1px solid #dee2e6;
  display: flex;
  flex-direction: column;
  overflow-y: auto;
}

.floor-selector-title {
  padding: 16px;
  margin: 0;
  font-size: 18px;
  font-weight: 600;
  color: #495057;
  border-bottom: 1px solid #dee2e6;
}

.floor-list {
  flex: 1;
  padding: 8px;
}

.floor-item {
  padding: 12px;
  margin-bottom: 8px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  background: white;
  border: 2px solid transparent;
}

.floor-item:hover {
  background: #e9ecef;
  transform: translateX(4px);
}

.floor-item.active {
  background: #007bff;
  border-color: #0056b3;
  color: white;
}

.floor-item.active .floor-name,
.floor-item.active .floor-description {
  color: white;
}

.floor-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 4px;
}

.floor-name {
  font-weight: 600;
  font-size: 14px;
  color: #212529;
}

.floor-description {
  font-size: 12px;
  color: #6c757d;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.floor-badge {
  font-size: 11px;
  padding: 2px 8px;
  border-radius: 12px;
  font-weight: 600;
  min-width: 50px;
  text-align: center;
}

.floor-badge.all-available {
  background: #28a745;
  color: white;
}

.floor-badge.partial {
  background: #ffc107;
  color: #212529;
}

.floor-badge.full {
  background: #dc3545;
  color: white;
}

.no-floors {
  padding: 20px;
  text-align: center;
  color: #6c757d;
  font-style: italic;
}

@media (max-width: 768px) {
  .floor-selector {
    width: 100%;
    border-right: none;
    border-bottom: 1px solid #dee2e6;
  }
  
  .floor-list {
    display: flex;
    overflow-x: auto;
    padding: 8px;
    gap: 8px;
  }
  
  .floor-item {
    min-width: 150px;
    margin-bottom: 0;
  }
}
</style>

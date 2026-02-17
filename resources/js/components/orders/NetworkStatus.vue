<template>
  <div class="network-status" :class="{ 'is-online': isOnline, 'is-offline': !isOnline }">
    <div class="status-indicator">
      <i :class="statusIcon"></i>
      <span class="status-text">{{ statusText }}</span>
    </div>
    <div v-if="!isOnline && pendingCount > 0" class="pending-sync">
      <span class="pending-badge">{{ pendingCount }} pending</span>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import offlineManager from '../../offline-manager'

export default {
  name: 'NetworkStatus',
  
  props: {
    pendingCount: {
      type: Number,
      default: 0
    }
  },
  
  setup() {
    const isOnline = ref(offlineManager.getNetworkStatus())
    let unsubscribe = null
    
    onMounted(() => {
      // Subscribe to network status changes
      unsubscribe = offlineManager.subscribe((online) => {
        isOnline.value = online
      })
    })
    
    onUnmounted(() => {
      if (unsubscribe) {
        unsubscribe()
      }
    })
    
    const statusText = computed(() => {
      return isOnline.value ? 'Online' : 'Offline'
    })
    
    const statusIcon = computed(() => {
      return isOnline.value ? 'fas fa-wifi' : 'fas fa-wifi-slash'
    })
    
    return {
      isOnline,
      statusText,
      statusIcon
    }
  }
}
</script>

<style scoped>
.network-status {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 8px 16px;
  border-radius: 6px;
  font-size: 13px;
  font-weight: 500;
  transition: all 0.3s ease;
}

.network-status.is-online {
  background: rgba(40, 167, 69, 0.1);
  color: #28a745;
}

.network-status.is-offline {
  background: rgba(220, 53, 69, 0.1);
  color: #dc3545;
}

.status-indicator {
  display: flex;
  align-items: center;
  gap: 6px;
}

.status-indicator i {
  font-size: 14px;
}

.pending-sync {
  display: flex;
  align-items: center;
}

.pending-badge {
  background: #ffc107;
  color: #856404;
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 600;
}
</style>
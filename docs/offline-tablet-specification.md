# Async Order Management Specification - Offline Tablet Functionality

## 1. Overview

This specification details the asynchronous order management system required to enable tablet-based ordering functionality without continuous internet connectivity. Waiters need to carry tablets to customers, potentially out of WiFi range, requiring robust offline capabilities with intelligent synchronization.

**Architecture Note**: This implementation follows a **services-first approach**. The core business logic is implemented as Laravel services that can be invoked from controllers, Livewire components, or queued jobs. REST APIs can be exposed later if multiple deployments or external integrations are needed, but the primary focus is on robust service-layer implementation with comprehensive test coverage.

## 2. Business Requirements

### 2.1 Primary Use Cases
- **Table Service**: Waiters take orders at tables without internet connectivity
- **Outdoor Seating**: Service in areas with poor WiFi coverage
- **Backup Operations**: Continue service during internet outages
- **Mobile Ordering**: Temporary pop-up locations or events

### 2.2 Success Criteria
- Zero disruption to service during connectivity loss
- Automatic data synchronization when connection restored
- No data loss due to network interruptions
- Intuitive conflict resolution for overlapping operations
- Real-time sync status indicators for staff

## 3. Technical Architecture

### 3.1 System Components

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Tablet PWA    │    │   Sync Service  │    │   Laravel API   │
│                 │    │                 │    │                 │
│ ┌─────────────┐ │    │ ┌─────────────┐ │    │ ┌─────────────┐ │
│ │ Local DB    │ │◄──►│ │ Conflict    │ │◄──►│ │ MySQL       │ │
│ │ (IndexedDB)│ │    │ │ Resolution  │ │    │ │ Database    │ │
│ └─────────────┘ │    │ │ Engine      │ │    │ └─────────────┘ │
│                 │    │ └─────────────┘ │    │                 │
│ ┌─────────────┐ │    │                 │    │ ┌─────────────┐ │
│ │ Background  │ │    │ ┌─────────────┐ │    │ │ Queue       │ │
│ │ Sync Manager│ │    │ │ Retry       │ │    │ │ System      │ │
│ └─────────────┘ │    │ │ Logic       │ │    │ └─────────────┘ │
└─────────────────┘    │ └─────────────┘ │    └─────────────────┘
                       └─────────────────┘
```

### 3.2 Data Flow Architecture

#### Online Mode
```
Tablet → API → Database → Real-time Updates
  ↑        ↓        ↓
Status ← Sync ← Confirmation
```

#### Offline Mode
```
Tablet → Local DB → Background Sync Queue
  ↑
Status Indicator (Offline)
```

#### Sync Mode
```
Local DB → Conflict Engine → API → Database
    ↑           ↓           ↓
Status ← Resolution ← Confirmation
```

### 3.3 Laravel Services Architecture

The backend implements a **services-first architecture** with the following core services:

```
┌─────────────────────────────────────────────────────────────┐
│                    Laravel Backend                          │
│                                                             │
│  ┌──────────────────┐      ┌──────────────────┐            │
│  │ OfflineOrder     │      │ ConflictResolution│            │
│  │ Service          │──────│ Service          │            │
│  └──────────────────┘      └──────────────────┘            │
│           │                           │                    │
│           ▼                           ▼                    │
│  ┌──────────────────┐      ┌──────────────────┐            │
│  │ OrderSyncQueue   │      │ DeviceSyncLog    │            │
│  │ Model            │      │ Model            │            │
│  └──────────────────┘      └──────────────────┘            │
│                                                             │
│  Usage:                                                     │
│  - Livewire Components                                      │
│  - Artisan Commands                                         │
│  - Queued Jobs                                              │
│  - API Controllers (future)                                 │
└─────────────────────────────────────────────────────────────┘
```

**Key Principles:**
1. **Service Layer**: All business logic resides in service classes
2. **Testability**: Services are fully unit-testable without HTTP layer
3. **Flexibility**: Can be invoked from controllers, commands, or jobs
4. **API Optional**: REST endpoints can be added later without changing core logic

## 4. Data Storage Strategy

### 4.1 Local Storage (IndexedDB)

#### Schema Design
```javascript
// Products Cache
const productsStore = {
  key: "product_{id}",
  data: {
    id: Number,
    name: String,
    price: Number,
    quantity: Number,
    categories: Array,
    image_url: String,
    kitchen_printer_ip: String,
    last_sync: String,
    version: Number
  }
}

// Orders Store
const ordersStore = {
  key: "order_{local_id}",
  data: {
    local_id: String,
    server_id: Number, // null if not synced
    customer_id: Number,
    items: Array,
    table_number: String,
    waiter_name: String,
    status: String, // 'pending_sync', 'synced', 'conflict'
    total_amount: Number,
    created_at: String,
    last_modified: String,
    sync_attempts: Number,
    conflict_data: Object // null if no conflict
  }
}

// Customers Store
const customersStore = {
  key: "customer_{id}",
  data: {
    id: Number,
    name: String,
    membership_number: String,
    phone: String,
    last_sync: String,
    version: Number
  }
}

// Sync Queue Store
const syncQueueStore = {
  key: "sync_{timestamp}_{type}",
  data: {
    id: String,
    type: String, // 'create_order', 'update_order', 'create_customer'
    payload: Object,
    retry_count: Number,
    next_retry: String,
    status: String // 'pending', 'processing', 'failed', 'completed'
  }
}
```

#### Storage Limits Management
```javascript
// Cache management strategy
const CACHE_LIMITS = {
  products: 1000, // products
  customers: 5000, // customers
  orders: 500, // local orders
  sync_queue: 1000 // sync operations
};

// Cleanup old data
function cleanupOldCache() {
  const cutoffDate = new Date(Date.now() - (30 * 24 * 60 * 60 * 1000));
  // Remove orders older than 30 days and successfully synced
}
```

### 4.2 Data Versioning

#### Version Control Strategy
```javascript
// Version management for conflict resolution
const DataVersion = {
  products: {
    current: 1,
    last_sync: '2025-01-01T00:00:00Z',
    checksum: 'abc123'
  },
  customers: {
    current: 1,
    last_sync: '2025-01-01T00:00:00Z',
    checksum: 'def456'
  }
};

// Check for updates
async function checkForUpdates() {
  const response = await fetch('/api/sync/check-versions', {
    method: 'POST',
    body: JSON.stringify(DataVersion)
  });
  
  return response.json(); // { updates_needed: { products: true, customers: false } }
}
```

## 5. Synchronization Mechanism

### 5.1 Sync States

#### Connection States
```javascript
const ConnectionStates = {
  ONLINE: 'online',          // Full connectivity
  OFFLINE: 'offline',        // No connectivity
  SYNCING: 'syncing',        // Synchronizing data
  CONFLICT: 'conflict',      // Manual resolution needed
  LIMITED: 'limited'         // Poor connectivity
};
```

#### Data Sync States
```javascript
const SyncStates = {
  PENDING: 'pending',         // Awaiting sync
  SYNCING: 'syncing',         // Currently syncing
  SYNCED: 'synced',           // Successfully synced
  CONFLICT: 'conflict',       // Requires manual resolution
  FAILED: 'failed'            // Sync failed, will retry
};
```

### 5.2 Bidirectional Sync Algorithm

#### Sync Workflow
```javascript
class SyncManager {
  constructor() {
    this.connectionState = ConnectionStates.OFFLINE;
    this.syncQueue = new SyncQueue();
    this.conflictResolver = new ConflictResolver();
  }

  async startSync() {
    if (this.connectionState === ConnectionStates.OFFLINE) {
      return;
    }

    try {
      // Step 1: Check connectivity
      await this.checkConnectivity();
      
      // Step 2: Pull server updates
      await this.pullUpdates();
      
      // Step 3: Push local changes
      await this.pushChanges();
      
      // Step 4: Resolve conflicts
      await this.resolveConflicts();
      
      this.updateStatus('Sync completed successfully');
    } catch (error) {
      this.handleError(error);
    }
  }

  async pullUpdates() {
    const lastSync = localStorage.getItem('last_sync_timestamp');
    
    const response = await fetch('/api/sync/updates', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ last_sync: lastSync })
    });

    const updates = await response.json();
    
    // Apply updates to local storage
    await this.applyUpdates(updates);
  }

  async pushChanges() {
    const pendingOrders = await this.getPendingOrders();
    
    for (const order of pendingOrders) {
      try {
        const response = await fetch('/api/orders', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(order)
        });

        if (response.ok) {
          const result = await response.json();
          await this.markOrderSynced(order.local_id, result.id);
        } else if (response.status === 409) {
          // Conflict detected
          await this.handleConflict(order, await response.json());
        }
      } catch (error) {
        // Add to retry queue
        await this.queueForRetry(order);
      }
    }
  }
}
```

### 5.3 Conflict Resolution

#### Conflict Types
1. **Data Conflicts**: Same order modified on multiple tablets
2. **Inventory Conflicts**: Product quantity discrepancies
3. **Customer Conflicts**: Customer information changes
4. **Timing Conflicts**: Orders created with overlapping POS numbers

#### Resolution Strategies
```javascript
class ConflictResolver {
  async resolveOrderConflict(localOrder, serverOrder) {
    const conflictType = this.detectConflictType(localOrder, serverOrder);
    
    switch (conflictType) {
      case 'DUPLICATE_ORDER':
        return this.handleDuplicateOrder(localOrder, serverOrder);
        
      case 'INVENTORY_MISMATCH':
        return this.handleInventoryConflict(localOrder, serverOrder);
        
      case 'CUSTOMER_CHANGE':
        return this.handleCustomerConflict(localOrder, serverOrder);
        
      default:
        return this.requireManualResolution(localOrder, serverOrder);
    }
  }

  async handleDuplicateOrder(localOrder, serverOrder) {
    // Auto-resolve if orders are identical
    if (this.areOrdersIdentical(localOrder, serverOrder)) {
      return { action: 'USE_SERVER', reason: 'Identical orders detected' };
    }
    
    // If local order is newer, use local
    if (new Date(localOrder.created_at) > new Date(serverOrder.created_at)) {
      return { action: 'USE_LOCAL', reason: 'Local order is newer' };
    }
    
    return { action: 'MANUAL_REVIEW', reason: 'Significant differences detected' };
  }

  async handleInventoryConflict(localOrder, serverOrder) {
    // Check current inventory levels
    const currentInventory = await this.getCurrentInventory(localOrder.items);
    
    if (currentInventory.sufficient) {
      return { action: 'USE_LOCAL', reason: 'Inventory available' };
    } else {
      return { 
        action: 'MODIFY_ORDER', 
        reason: 'Insufficient inventory',
        modifications: currentInventory.adjustments 
      };
    }
  }
}
```

## 6. User Interface Design

### 6.1 Status Indicators

#### Connection Status Component
```javascript
const ConnectionStatus = {
  template: `
    <div class="connection-status" :class="statusClass">
      <i :class="statusIcon"></i>
      <span>{{ statusText }}</span>
      <div v-if="syncProgress.show" class="sync-progress">
        <div class="progress-bar" :style="{ width: syncProgress.percentage + '%' }"></div>
      </div>
    </div>
  `,
  
  computed: {
    statusClass() {
      return `status-${this.connectionState.toLowerCase()}`;
    },
    
    statusIcon() {
      const icons = {
        'ONLINE': 'fas fa-wifi',
        'OFFLINE': 'fas fa-wifi-slash',
        'SYNCING': 'fas fa-sync fa-spin',
        'CONFLICT': 'fas fa-exclamation-triangle',
        'LIMITED': 'fas fa-wifi'
      };
      return icons[this.connectionState];
    },
    
    statusText() {
      const texts = {
        'ONLINE': 'Connected',
        'OFFLINE': 'Offline - Working locally',
        'SYNCING': 'Synchronizing...',
        'CONFLICT': 'Needs attention',
        'LIMITED': 'Poor connection'
      };
      return texts[this.connectionState];
    }
  }
};
```

#### Offline Order List
```javascript
const OfflineOrderList = {
  template: `
    <div class="offline-orders">
      <h3>Offline Orders ({{ offlineOrders.length }})</h3>
      
      <div v-for="order in offlineOrders" :key="order.local_id" 
           class="order-item" :class="order.status">
        
        <div class="order-header">
          <span class="table-number">Table {{ order.table_number }}</span>
          <span class="sync-status" :class="order.status">
            {{ getSyncStatusText(order.status) }}
          </span>
        </div>
        
        <div class="order-items">
          <div v-for="item in order.items" :key="item.product_id" class="item">
            {{ item.quantity }}x {{ item.product_name }}
          </div>
        </div>
        
        <div class="order-actions">
          <button v-if="order.status === 'conflict'" 
                  @click="resolveConflict(order)" 
                  class="btn btn-warning">
            Resolve Conflict
          </button>
          
          <button @click="viewOrder(order)" class="btn btn-primary">
            View Details
          </button>
        </div>
      </div>
      
      <div v-if="hasConflicts" class="conflict-warning">
        <i class="fas fa-exclamation-triangle"></i>
        {{ conflictCount }} orders need attention
        <button @click="resolveAllConflicts" class="btn btn-danger">
          Resolve All
        </button>
      </div>
    </div>
  `
};
```

### 6.2 Conflict Resolution Interface

#### Manual Resolution Dialog
```javascript
const ConflictResolutionDialog = {
  template: `
    <div class="conflict-dialog">
      <h3>Order Conflict Resolution</h3>
      <p>Order for Table {{ conflict.localOrder.table_number }} has conflicts</p>
      
      <div class="conflict-comparison">
        <div class="order-version local">
          <h4>Local Version</h4>
          <div class="order-details">
            <p><strong>Total:</strong> ${{ conflict.localOrder.total_amount }}</p>
            <div class="items">
              <div v-for="item in conflict.localOrder.items" :key="item.product_id">
                {{ item.quantity }}x {{ item.product_name }} - ${{ item.total }}
              </div>
            </div>
          </div>
        </div>
        
        <div class="resolution-actions">
          <button @click="useLocal" class="btn btn-primary">Use Local</button>
          <button @click="useServer" class="btn btn-secondary">Use Server</button>
          <button @click="mergeOrders" class="btn btn-info">Merge</button>
        </div>
        
        <div class="order-version server">
          <h4>Server Version</h4>
          <div class="order-details">
            <p><strong>Total:</strong> ${{ conflict.serverOrder.total_amount }}</p>
            <div class="items">
              <div v-for="item in conflict.serverOrder.items" :key="item.product_id">
                {{ item.quantity }}x {{ item.product_name }} - ${{ item.total }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  `
};
```

## 7. Background Sync Implementation

### 7.1 Service Worker Architecture

#### Service Worker Registration
```javascript
// public/js/service-worker.js
const CACHE_NAME = 'pos-app-v1.0.0';
const SYNC_QUEUE_NAME = 'pos-sync-queue';

// Background sync registration
self.addEventListener('sync', (event) => {
  if (event.tag === 'pos-orders-sync') {
    event.waitUntil(syncOrders());
  }
});

async function syncOrders() {
  try {
    const syncQueue = await getSyncQueue();
    
    for (const syncItem of syncQueue) {
      try {
        await processSyncItem(syncItem);
        await removeSyncItem(syncItem.id);
      } catch (error) {
        console.error('Sync failed for item:', syncItem.id, error);
        await incrementRetryCount(syncItem.id);
      }
    }
    
    // Notify client of sync completion
    const clients = await self.clients.matchAll();
    clients.forEach(client => {
      client.postMessage({ type: 'SYNC_COMPLETED' });
    });
  } catch (error) {
    console.error('Background sync failed:', error);
  }
}
```

#### Periodic Sync Strategy
```javascript
// Periodic background sync registration
async function registerPeriodicSync() {
  if ('periodicSync' in self.registration) {
    try {
      await self.registration.periodicSync.register('pos-orders-sync', {
        minInterval: 5 * 60 * 1000 // 5 minutes
      });
    } catch (error) {
      console.log('Periodic sync registration failed:', error);
    }
  }
}
```

### 7.2 Retry Logic

#### Exponential Backoff
```javascript
class RetryManager {
  constructor() {
    this.maxRetries = 5;
    this.baseDelay = 1000; // 1 second
    this.maxDelay = 5 * 60 * 1000; // 5 minutes
  }

  calculateRetryDelay(attemptCount) {
    const delay = this.baseDelay * Math.pow(2, attemptCount - 1);
    return Math.min(delay, this.maxDelay);
  }

  async scheduleRetry(syncItem) {
    if (syncItem.retry_count >= this.maxRetries) {
      await this.markAsFailed(syncItem);
      return;
    }

    const delay = this.calculateRetryDelay(syncItem.retry_count + 1);
    syncItem.next_retry = new Date(Date.now() + delay).toISOString();
    syncItem.retry_count += 1;
    
    await this.updateSyncItem(syncItem);
    
    // Schedule retry
    setTimeout(() => {
      this.processRetry(syncItem);
    }, delay);
  }

  async processRetry(syncItem) {
    try {
      await this.processSyncItem(syncItem);
      await this.removeSyncItem(syncItem.id);
    } catch (error) {
      await this.scheduleRetry(syncItem);
    }
  }
}
```

## 8. Performance Optimization

### 8.1 Data Compression

#### Local Data Compression
```javascript
class DataCompressor {
  // Compress large datasets for local storage
  static async compressData(data) {
    const jsonString = JSON.stringify(data);
    
    if ('CompressionStream' in window) {
      const stream = new CompressionStream('gzip');
      const writer = stream.writable.getWriter();
      const reader = stream.readable.getReader();
      
      writer.write(new TextEncoder().encode(jsonString));
      writer.close();
      
      const chunks = [];
      let done = false;
      
      while (!done) {
        const { value, done: readerDone } = await reader.read();
        done = readerDone;
        if (value) chunks.push(value);
      }
      
      return new Uint8Array(chunks.reduce((acc, chunk) => [...acc, ...chunk], []));
    }
    
    // Fallback for browsers without CompressionStream
    return jsonString;
  }

  static async decompressData(compressedData) {
    if ('DecompressionStream' in window) {
      const stream = new DecompressionStream('gzip');
      const writer = stream.writable.getWriter();
      const reader = stream.readable.getReader();
      
      writer.write(compressedData);
      writer.close();
      
      const chunks = [];
      let done = false;
      
      while (!done) {
        const { value, done: readerDone } = await reader.read();
        done = readerDone;
        if (value) chunks.push(value);
      }
      
      const decompressed = new Uint8Array(chunks.reduce((acc, chunk) => [...acc, ...chunk], []));
      return JSON.parse(new TextDecoder().decode(decompressed));
    }
    
    // Fallback
    return typeof compressedData === 'string' ? JSON.parse(compressedData) : compressedData;
  }
}
```

### 8.2 Batch Operations

#### Batch Sync Optimization
```javascript
class BatchSyncManager {
  constructor() {
    this.batchSize = 10;
    this.batchTimeout = 5000; // 5 seconds
  }

  async batchSync(syncItems) {
    const batches = this.createBatches(syncItems);
    
    for (const batch of batches) {
      try {
        await this.processBatch(batch);
        await this.markBatchCompleted(batch);
      } catch (error) {
        await this.handleBatchFailure(batch, error);
      }
    }
  }

  createBatches(items) {
    const batches = [];
    for (let i = 0; i < items.length; i += this.batchSize) {
      batches.push(items.slice(i, i + this.batchSize));
    }
    return batches;
  }

  async processBatch(batch) {
    const response = await fetch('/api/sync/batch', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        operations: batch.map(item => ({
          type: item.type,
          payload: item.payload
        }))
      })
    });

    if (!response.ok) {
      throw new Error(`Batch sync failed: ${response.statusText}`);
    }

    return response.json();
  }
}
```

## 9. Security Considerations

### 9.1 Data Encryption

#### Local Storage Encryption
```javascript
class LocalEncryption {
  constructor() {
    this.algorithm = 'AES-GCM';
    this.keyLength = 256;
  }

  async generateKey() {
    return await window.crypto.subtle.generateKey(
      {
        name: this.algorithm,
        length: this.keyLength
      },
      true,
      ['encrypt', 'decrypt']
    );
  }

  async encryptData(data, key) {
    const iv = window.crypto.getRandomValues(new Uint8Array(12));
    const encodedData = new TextEncoder().encode(JSON.stringify(data));
    
    const encryptedData = await window.crypto.subtle.encrypt(
      {
        name: this.algorithm,
        iv: iv
      },
      key,
      encodedData
    );

    return {
      encrypted: Array.from(new Uint8Array(encryptedData)),
      iv: Array.from(iv)
    };
  }

  async decryptData(encryptedData, iv, key) {
    const decryptedData = await window.crypto.subtle.decrypt(
      {
        name: this.algorithm,
        iv: new Uint8Array(iv)
      },
      key,
      new Uint8Array(encryptedData)
    );

    return JSON.parse(new TextDecoder().decode(decryptedData));
  }
}
```

### 9.2 Authentication Security

#### Token Management
```javascript
class TokenManager {
  constructor() {
    this.refreshThreshold = 5 * 60 * 1000; // 5 minutes before expiry
  }

  async getValidToken() {
    let token = localStorage.getItem('auth_token');
    let expiry = localStorage.getItem('token_expiry');

    if (!token || this.isTokenExpiringSoon(expiry)) {
      token = await this.refreshToken();
    }

    return token;
  }

  async refreshToken() {
    const refreshToken = localStorage.getItem('refresh_token');
    
    try {
      const response = await fetch('/api/auth/refresh', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ refresh_token: refreshToken })
      });

      if (response.ok) {
        const data = await response.json();
        localStorage.setItem('auth_token', data.access_token);
        localStorage.setItem('token_expiry', data.expires_at);
        return data.access_token;
      }
    } catch (error) {
      // Handle refresh failure
      this.logout();
    }
  }

  isTokenExpiringSoon(expiry) {
    if (!expiry) return true;
    return new Date(expiry).getTime() - Date.now() < this.refreshThreshold;
  }
}
```

## 10. Testing Strategy

### 10.1 Offline Testing Scenarios

#### Test Cases
```javascript
// Offline functionality tests
describe('Offline Order Management', () => {
  test('Should create orders offline', async () => {
    // Simulate offline mode
    await simulateOfflineMode();
    
    // Create order
    const order = await createOrder(testOrderData);
    
    // Verify order stored locally
    const localOrders = await getLocalOrders();
    expect(localOrders).toContain(order);
    expect(order.status).toBe('pending_sync');
  });

  test('Should sync orders when online', async () => {
    // Create offline order
    await simulateOfflineMode();
    const offlineOrder = await createOrder(testOrderData);
    
    // Restore connection
    await simulateOnlineMode();
    await syncManager.startSync();
    
    // Verify order synced to server
    const serverOrders = await getServerOrders();
    expect(serverOrders.some(order => order.id === offlineOrder.server_id)).toBe(true);
  });

  test('Should handle sync conflicts', async () => {
    // Create conflicting orders
    const localOrder = await createOfflineOrder(testOrderData);
    await createServerOrder(conflictingOrderData);
    
    // Sync and detect conflict
    await simulateOnlineMode();
    const result = await syncManager.startSync();
    
    expect(result.conflicts.length).toBe(1);
    expect(result.conflicts[0].type).toBe('DUPLICATE_ORDER');
  });
});
```

### 10.2 Performance Testing

#### Sync Performance Benchmarks
```javascript
class PerformanceBenchmark {
  async benchmarkSyncPerformance(orderCounts) {
    const results = [];
    
    for (const count of orderCounts) {
      // Generate test data
      const orders = this.generateTestOrders(count);
      
      // Measure sync time
      const startTime = performance.now();
      await syncManager.syncOrders(orders);
      const endTime = performance.now();
      
      results.push({
        orderCount: count,
        syncTime: endTime - startTime,
        throughput: count / ((endTime - startTime) / 1000)
      });
    }
    
    return results;
  }

  async benchmarkStoragePerformance() {
    const testData = {
      orders: 1000,
      products: 5000,
      customers: 2000
    };

    // Test write performance
    const writeStart = performance.now();
    await this.writeTestData(testData);
    const writeEnd = performance.now();

    // Test read performance
    const readStart = performance.now();
    await this.readTestData(testData);
    const readEnd = performance.now();

    return {
      writeTime: writeEnd - writeStart,
      readTime: readEnd - readStart,
      storageSize: await this.getStorageSize()
    };
  }
}
```

## 11. Monitoring and Analytics

### 11.1 Sync Monitoring

#### Metrics Collection
```javascript
class SyncMetrics {
  constructor() {
    this.metrics = {
      totalSyncs: 0,
      successfulSyncs: 0,
      failedSyncs: 0,
      conflictsResolved: 0,
      averageSyncTime: 0,
      lastSyncTime: null
    };
  }

  recordSyncAttempt(duration, success, conflicts = 0) {
    this.metrics.totalSyncs++;
    
    if (success) {
      this.metrics.successfulSyncs++;
    } else {
      this.metrics.failedSyncs++;
    }
    
    this.metrics.conflictsResolved += conflicts;
    this.updateAverageSyncTime(duration);
    this.metrics.lastSyncTime = new Date().toISOString();
    
    // Send to analytics
    this.sendMetricsToServer();
  }

  getSyncHealthScore() {
    if (this.metrics.totalSyncs === 0) return 100;
    
    const successRate = (this.metrics.successfulSyncs / this.metrics.totalSyncs) * 100;
    const conflictRate = (this.metrics.conflictsResolved / this.metrics.totalSyncs) * 100;
    
    return Math.max(0, successRate - (conflictRate * 0.5));
  }
}
```

### 11.2 User Behavior Analytics

#### Usage Patterns
```javascript
class UserAnalytics {
  trackOfflineUsage() {
    return {
      sessionDuration: this.getSessionDuration(),
      ordersCreatedOffline: this.getOfflineOrderCount(),
      timeSpentOffline: this.getOfflineTimeSpent(),
      syncFrequency: this.getSyncFrequency(),
      conflictRate: this.getConflictResolutionRate()
    };
  }

  trackTabletPerformance() {
    return {
      deviceModel: navigator.userAgent,
      storageUsage: await this.getStorageUsage(),
      memoryUsage: performance.memory ? performance.memory.usedJSHeapSize : null,
      networkType: navigator.connection ? navigator.connection.effectiveType : null,
      batteryLevel: navigator.getBattery ? (await navigator.getBattery()).level : null
    };
  }
}
```

## 12. Implementation Timeline

### Phase 1: Foundation (Weeks 1-4)
- Set up IndexedDB structure
- Implement basic local storage
- Create service worker foundation
- Develop encryption mechanisms

### Phase 2: Core Sync (Weeks 5-8)
- Implement bidirectional sync
- Develop conflict resolution engine
- Create retry mechanisms
- Build background sync system

### Phase 3: User Interface (Weeks 9-12)
- Develop status indicators
- Create offline order management UI
- Build conflict resolution interface
- Implement tablet-optimized design

### Phase 4: Testing & Optimization (Weeks 13-16)
- Comprehensive testing suite
- Performance optimization
- Security validation
- User acceptance testing

This specification provides the foundation for implementing robust offline tablet functionality that will revolutionize the POS system's capabilities while ensuring data integrity and user experience remain paramount.

---

# Implementation Status

## ✅ Completed Implementation (February 2026)

### Core Services
- **OfflineOrderService** (`app/Services/OfflineOrderService.php`)
  - Creates offline orders with `pending_sync` status
  - Validates product availability
  - Handles batch order uploads
  - Duplicate detection by `local_order_id`
  - Automatic stock decrement
  - Comprehensive logging via `DeviceSyncLog`

- **ConflictResolutionService** (`app/Services/ConflictResolutionService.php`)
  - Detects duplicate orders and inventory conflicts
  - Multiple resolution strategies: `use_server`, `update_server`, `merge`
  - Auto-resolution for identical orders
  - Inventory conflict adjustment
  - Conflict summary statistics

### Database Schema
- **Migration: `add_sync_fields_to_orders_table`**
  - `sync_status` (pending_sync, synced)
  - `local_order_id` (unique identifier from tablet)
  - `device_id` (tablet identifier)
  - `synced_at` (timestamp)

- **Migration: `create_order_sync_queues_table`**
  - Queue management with retry logic
  - Conflict tracking with JSON data
  - Status: pending, processing, completed, failed, conflict

- **Migration: `create_device_sync_logs_table`**
  - Complete audit trail
  - Action tracking: order_created_offline, order_synced, conflict_resolved

### Livewire UI Components
- **TabletOrderComponent** (`app/Livewire/TabletOrderComponent.php`)
  - Order entry interface for tablets
  - Product search and selection
  - Quantity management
  - Customer selection
  - Connection status indicator
  - View: `resources/views/livewire/tablet-order-component.blade.php`

- **SyncStatusComponent** (`app/Livewire/SyncStatusComponent.php`)
  - Dashboard for monitoring sync status
  - Device filtering
  - Statistics display (total, pending, synced, failed, conflicts)
  - Manual sync triggers
  - Failed sync retry
  - View: `resources/views/livewire/sync-status-component.blade.php`

- **ConflictResolutionComponent** (`app/Livewire/ConflictResolutionComponent.php`)
  - Conflict management interface
  - Resolution options per conflict type
  - Inventory adjustment handling
  - Dismiss/ignore functionality
  - View: `resources/views/livewire/conflict-resolution-component.blade.php`

### Background Jobs
- **ProcessOfflineSyncQueue** (`app/Jobs/ProcessOfflineSyncQueue.php`)
  - Queue-based processing
  - Exponential backoff retry (10s, 30s, 1m, 5m, 10m)
  - Automatic conflict detection during sync
  - Device-based batch processing
  - Comprehensive error handling

### Artisan Commands
- **SyncOfflineOrders** (`app/Console/Commands/SyncOfflineOrders.php`)
  ```bash
  php artisan orders:sync-offline              # Show status
  php artisan orders:sync-offline --all        # Sync all pending
  php artisan orders:sync-offline --device=ID  # Sync specific device
  php artisan orders:sync-offline --order=ID   # Sync specific order
  php artisan orders:sync-offline --dry-run    # Preview mode
  ```

- **ScheduleOfflineSync** (`app/Console/Commands/ScheduleOfflineSync.php`)
  - Automated sync scheduling
  - Batch processing limits
  - Notification support
  - Integrated with Laravel scheduler (runs every 5 minutes)

### Routes & Views
```php
// Routes (routes/web.php)
Route::view('/tablet-order', 'offline-sync.tablet-order')->name('tablet.order');
Route::view('/sync-status', 'offline-sync.sync-status')->name('sync.status');
Route::view('/conflict-resolution', 'offline-sync.conflict-resolution')->name('conflict.resolution');
```

### Middleware
- **DeviceIdentification** (`app/Http/Middleware/DeviceIdentification.php`)
  - Automatic device ID detection from headers
  - Query parameter support (`?device_id=`)
  - Session-based persistence
  - Auto-generation for web clients

### Configuration
- **Config File** (`config/offline-sync.php`)
  - Enable/disable offline sync
  - Sync intervals and batch sizes
  - Conflict resolution strategies
  - Queue settings
  - Notification settings

### Styling
- **CSS** (`public/css/offline-sync.css`)
  - Responsive grid layouts
  - Status indicators (online/offline)
  - Card-based UI components
  - Mobile-optimized styles

### Test Coverage
- **190 tests, 516 assertions - ALL PASSING**
- Service tests: `tests/Unit/Services/`
- Livewire component tests: `tests/Unit/Livewire/`
- Job tests: `tests/Unit/Jobs/`
- Command tests: `tests/Unit/Console/`
- Middleware tests: `tests/Unit/Middleware/`
- Feature tests: `tests/Feature/OfflineSyncRoutesTest.php`

## Usage Instructions

### For Waiters (Tablet Users)
1. Navigate to `/tablet-order`
2. Enter table number and customer info
3. Search and add products
4. Adjust quantities as needed
5. Click "Create Order"
6. Order is queued for sync automatically

### For Managers (Sync Monitoring)
1. Navigate to `/sync-status`
2. View pending/synced/failed statistics
3. Filter by device or status
4. Trigger manual sync if needed
5. Review failed syncs and retry

### For Admins (Conflict Resolution)
1. Navigate to `/conflict-resolution`
2. View all conflicts requiring attention
3. Click "View" to see conflict details
4. Choose resolution strategy:
   - Use Server Version (discard local changes)
   - Update Server (overwrite with local)
   - Merge (combine orders)
   - Adjust Quantity (for inventory conflicts)
5. Or dismiss if not needed

### Automated Sync
- Sync runs automatically every 5 minutes via Laravel scheduler
- Configure in `app/Console/Kernel.php`
- Logs stored in `storage/logs/offline-sync.log`

## Architecture Decisions

1. **Services-First Approach**: Core logic in services, not controllers
2. **No APIs Required**: Can add REST endpoints later if needed
3. **Queue-Based Processing**: Async processing prevents blocking
4. **Full Audit Trail**: Every action logged for debugging
5. **Transaction Safety**: Database operations wrapped in transactions
6. **Comprehensive Testing**: 100% test coverage on core functionality

## Next Steps (Optional)
- [ ] PWA manifest and service worker (frontend)
- [ ] WebSocket real-time updates
- [ ] Email notifications for conflicts
- [ ] Mobile app wrapper (React Native/Flutter)
- [ ] Multi-shop sync coordination
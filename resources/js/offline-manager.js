/**
 * Offline Storage Manager
 * 
 * Manages orders and data when offline using localStorage.
 * Provides queueing for operations that need to sync when back online.
 */

const STORAGE_KEY = 'pos_offline_orders';
const QUEUE_KEY = 'pos_offline_queue';
const NETWORK_STATUS_KEY = 'pos_network_status';

class OfflineManager {
    constructor() {
        // Check if we're in a browser environment
        this.isBrowser = typeof window !== 'undefined' && typeof navigator !== 'undefined';
        this.isOnline = this.isBrowser ? navigator.onLine : true;
        this.listeners = [];
        
        // Check if localStorage is available
        this.storageAvailable = this.checkStorageAvailable();
        
        if (this.isBrowser) {
            // Bind network event handlers
            this.handleOnline = this.handleOnline.bind(this);
            this.handleOffline = this.handleOffline.bind(this);
            
            // Setup event listeners
            window.addEventListener('online', this.handleOnline);
            window.addEventListener('offline', this.handleOffline);
            
            // Initialize network status
            this.updateNetworkStatus();
        }
    }
    
    /**
     * Check if localStorage is available and working
     */
    checkStorageAvailable() {
        if (typeof window === 'undefined' || !window.localStorage) {
            return false;
        }
        try {
            const test = '__storage_test__';
            window.localStorage.setItem(test, test);
            window.localStorage.removeItem(test);
            return true;
        } catch (e) {
            console.warn('[OfflineManager] localStorage not available:', e.message);
            return false;
        }
    }
    
    /**
     * Subscribe to network status changes
     */
    subscribe(callback) {
        this.listeners.push(callback);
        // Immediately notify of current status
        callback(this.isOnline);
        
        // Return unsubscribe function
        return () => {
            const index = this.listeners.indexOf(callback);
            if (index > -1) {
                this.listeners.splice(index, 1);
            }
        };
    }
    
    /**
     * Notify all subscribers of network status change
     */
    notifyListeners() {
        this.listeners.forEach(callback => callback(this.isOnline));
    }
    
    /**
     * Handle going online
     */
    handleOnline() {
        console.log('[OfflineManager] Connection restored');
        this.isOnline = true;
        this.updateNetworkStatus();
        this.notifyListeners();
        
        // Trigger sync
        this.syncPendingOrders();
    }
    
    /**
     * Handle going offline
     */
    handleOffline() {
        console.log('[OfflineManager] Connection lost');
        this.isOnline = false;
        this.updateNetworkStatus();
        this.notifyListeners();
    }
    
    /**
     * Update network status in storage
     */
    updateNetworkStatus() {
        if (!this.storageAvailable) return;
        try {
            localStorage.setItem(NETWORK_STATUS_KEY, JSON.stringify({
                isOnline: this.isOnline,
                lastChecked: new Date().toISOString()
            }));
        } catch (e) {
            console.error('[OfflineManager] Failed to update network status:', e);
        }
    }
    
    /**
     * Get current network status
     */
    getNetworkStatus() {
        return this.isOnline;
    }
    
    /**
     * Check if browser is online
     */
    checkOnline() {
        return this.isBrowser ? navigator.onLine : true;
    }
    
    // ==================== Order Storage ====================
    
    /**
     * Save orders to local storage
     */
    saveOrders(orders) {
        if (!this.storageAvailable) {
            console.warn('[OfflineManager] Cannot save orders - storage not available');
            return false;
        }
        try {
            const data = {
                orders: orders,
                lastUpdated: new Date().toISOString(),
                version: 1
            };
            localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
            return true;
        } catch (e) {
            console.error('[OfflineManager] Failed to save orders:', e);
            return false;
        }
    }
    
    /**
     * Load orders from local storage
     */
    loadOrders() {
        if (!this.storageAvailable) {
            return [];
        }
        try {
            const data = localStorage.getItem(STORAGE_KEY);
            if (data) {
                const parsed = JSON.parse(data);
                return parsed.orders || [];
            }
            return [];
        } catch (e) {
            console.error('[OfflineManager] Failed to load orders:', e);
            return [];
        }
    }
    
    /**
     * Clear stored orders
     */
    clearOrders() {
        if (!this.storageAvailable) return false;
        try {
            localStorage.removeItem(STORAGE_KEY);
            return true;
        } catch (e) {
            console.error('[OfflineManager] Failed to clear orders:', e);
            return false;
        }
    }
    
    // ==================== Operation Queue ====================
    
    /**
     * Add operation to queue for later sync
     */
    queueOperation(operation) {
        if (!this.storageAvailable) {
            console.warn('[OfflineManager] Cannot queue operation - storage not available');
            return false;
        }
        try {
            const queue = this.getQueue();
            queue.push({
                ...operation,
                id: `op-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`,
                timestamp: new Date().toISOString(),
                attempts: 0
            });
            localStorage.setItem(QUEUE_KEY, JSON.stringify(queue));
            return true;
        } catch (e) {
            console.error('[OfflineManager] Failed to queue operation:', e);
            return false;
        }
    }
    
    /**
     * Get all queued operations
     */
    getQueue() {
        if (!this.storageAvailable) {
            return [];
        }
        try {
            const data = localStorage.getItem(QUEUE_KEY);
            return data ? JSON.parse(data) : [];
        } catch (e) {
            console.error('[OfflineManager] Failed to get queue:', e);
            return [];
        }
    }
    
    /**
     * Remove operation from queue
     */
    removeFromQueue(operationId) {
        if (!this.storageAvailable) return false;
        try {
            const queue = this.getQueue();
            const filtered = queue.filter(op => op.id !== operationId);
            localStorage.setItem(QUEUE_KEY, JSON.stringify(filtered));
            return true;
        } catch (e) {
            console.error('[OfflineManager] Failed to remove from queue:', e);
            return false;
        }
    }
    
    /**
     * Clear all queued operations
     */
    clearQueue() {
        if (!this.storageAvailable) return false;
        try {
            localStorage.removeItem(QUEUE_KEY);
            return true;
        } catch (e) {
            console.error('[OfflineManager] Failed to clear queue:', e);
            return false;
        }
    }
    
    /**
     * Get count of pending operations
     */
    getPendingCount() {
        if (!this.storageAvailable) {
            return 0;
        }
        return this.getQueue().length;
    }
    
    // ==================== Sync Operations ====================
    
    /**
     * Sync all pending orders and operations
     */
    async syncPendingOrders() {
        if (!this.isOnline) {
            console.log('[OfflineManager] Cannot sync while offline');
            return { success: false, error: 'Offline' };
        }
        
        const queue = this.getQueue();
        if (queue.length === 0) {
            return { success: true, synced: 0 };
        }
        
        console.log(`[OfflineManager] Syncing ${queue.length} pending operations`);
        
        const results = {
            success: true,
            synced: 0,
            failed: 0,
            errors: []
        };
        
        for (const operation of queue) {
            try {
                const success = await this.executeOperation(operation);
                if (success) {
                    this.removeFromQueue(operation.id);
                    results.synced++;
                } else {
                    results.failed++;
                    operation.attempts++;
                }
            } catch (error) {
                console.error('[OfflineManager] Sync error:', error);
                results.failed++;
                results.errors.push({ operation: operation.id, error: error.message });
            }
        }
        
        return results;
    }
    
    /**
     * Execute a single queued operation
     */
    async executeOperation(operation) {
        try {
            const response = await fetch(operation.url, {
                method: operation.method || 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCsrfToken(),
                    'Accept': 'application/json'
                },
                body: operation.data ? JSON.stringify(operation.data) : null,
                credentials: 'same-origin'
            });
            
            return response.ok;
        } catch (error) {
            console.error('[OfflineManager] Operation failed:', error);
            return false;
        }
    }
    
    /**
     * Get CSRF token
     */
    getCsrfToken() {
        if (!this.isBrowser || typeof document === 'undefined') {
            return '';
        }
        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        return token || '';
    }
    
    // ==================== Cleanup ====================
    
    /**
     * Cleanup event listeners
     */
    destroy() {
        if (this.isBrowser) {
            window.removeEventListener('online', this.handleOnline);
            window.removeEventListener('offline', this.handleOffline);
        }
        this.listeners = [];
    }
}

// Create singleton instance
const offlineManager = new OfflineManager();

export default offlineManager;

// Also export the class for testing
export { OfflineManager };
/**
 * Service Worker for Offline Support
 * 
 * This service worker provides:
 * - Caching of static assets (JS, CSS, images)
 * - Network-first strategy for API calls with fallback to cache
 * - Offline page serving when network is unavailable
 */

const CACHE_NAME = 'pos-app-v1';
const STATIC_CACHE = 'pos-static-v1';
const API_CACHE = 'pos-api-v1';

// Static assets to cache on install
const STATIC_ASSETS = [
  '/',
  '/orders-workspace',
  '/css/app.css',
  '/js/app.js',
  '/images/logo.png',
  '/offline.html'
];

// API routes to cache
const API_ROUTES = [
  '/api/v1/orders',
  '/api/v1/products',
  '/api/v1/categories',
  '/api/v1/customers',
  '/api/v1/discounts'
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
  console.log('[Service Worker] Installing...');
  
  event.waitUntil(
    caches.open(STATIC_CACHE)
      .then((cache) => {
        console.log('[Service Worker] Caching static assets');
        return cache.addAll(STATIC_ASSETS);
      })
      .catch((error) => {
        console.error('[Service Worker] Failed to cache static assets:', error);
      })
  );
  
  // Skip waiting to activate immediately
  self.skipWaiting();
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  console.log('[Service Worker] Activating...');
  
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== STATIC_CACHE && cacheName !== API_CACHE) {
            console.log('[Service Worker] Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  
  // Claim clients immediately
  self.clients.claim();
});

// Fetch event - handle requests
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);
  
  // Skip non-GET requests and non-same-origin requests
  if (request.method !== 'GET' || !url.pathname.startsWith('/')) {
    return;
  }
  
  // Handle API requests
  if (url.pathname.startsWith('/api/')) {
    event.respondWith(handleApiRequest(request));
    return;
  }
  
  // Handle static assets
  event.respondWith(handleStaticRequest(request));
});

/**
 * Handle API requests with network-first strategy
 */
async function handleApiRequest(request) {
  try {
    // Try network first
    const networkResponse = await fetch(request);
    
    // If successful, clone and cache
    if (networkResponse.ok) {
      const cache = await caches.open(API_CACHE);
      cache.put(request, networkResponse.clone());
    }
    
    return networkResponse;
  } catch (error) {
    console.log('[Service Worker] API request failed, trying cache:', request.url);
    
    // Try to get from cache
    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
      return cachedResponse;
    }
    
    // Return offline response for API calls
    return new Response(
      JSON.stringify({
        error: 'Offline',
        message: 'You are currently offline. Changes will be synced when connection is restored.'
      }),
      {
        status: 503,
        headers: { 'Content-Type': 'application/json' }
      }
    );
  }
}

/**
 * Handle static assets with cache-first strategy
 */
async function handleStaticRequest(request) {
  // Try cache first
  const cachedResponse = await caches.match(request);
  
  if (cachedResponse) {
    // Return cached version but fetch update in background
    fetch(request)
      .then((networkResponse) => {
        if (networkResponse.ok) {
          caches.open(STATIC_CACHE).then((cache) => {
            cache.put(request, networkResponse);
          });
        }
      })
      .catch(() => {
        // Network failed, but we have cached version
      });
    
    return cachedResponse;
  }
  
  // If not in cache, fetch from network
  try {
    const networkResponse = await fetch(request);
    
    if (networkResponse.ok) {
      const cache = await caches.open(STATIC_CACHE);
      cache.put(request, networkResponse.clone());
    }
    
    return networkResponse;
  } catch (error) {
    console.log('[Service Worker] Failed to fetch:', request.url);
    
    // Return offline page for HTML requests
    if (request.headers.get('accept').includes('text/html')) {
      return caches.match('/offline.html');
    }
    
    throw error;
  }
}

// Background sync for offline operations
self.addEventListener('sync', (event) => {
  if (event.tag === 'sync-orders') {
    console.log('[Service Worker] Background sync triggered');
    event.waitUntil(syncPendingOrders());
  }
});

/**
 * Sync pending orders
 */
async function syncPendingOrders() {
  // This would be triggered by background sync
  // The actual sync logic is handled by the offline-manager.js in the main app
  console.log('[Service Worker] Notifying clients to sync orders');
  
  const clients = await self.clients.matchAll();
  clients.forEach((client) => {
    client.postMessage({
      type: 'SYNC_ORDERS'
    });
  });
}
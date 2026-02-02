<template>
  <div class="order-edit-container">
    <!-- Left Panel: Order Details & Items -->
    <div class="order-panel left-panel">
      <!-- Order Header -->
      <div class="order-header">
        <div class="order-meta">
          <h2 class="order-title">
            <i class="fas fa-receipt"></i>
            Order #{{ order.POS_number }}
          </h2>
          <StatusBadge 
            :status="order.state" 
            type="order" 
            size="small" 
          />
        </div>
        <div class="order-type-badge" :class="order.type">
          <i :class="typeIcon"></i>
          {{ formatOrderType(order.type) }}
        </div>
      </div>

      <!-- Order Data Section (Collapsible) -->
      <div class="order-data-section" v-if="canEditOrderData">
        <div class="section-header" @click="toggleOrderData">
          <span>Order Details</span>
          <i class="fas fa-chevron-down" :class="{ rotated: showOrderData }"></i>
        </div>
        <div class="section-body" v-show="showOrderData">
          <form @submit.prevent="updateOrderData" class="order-form">
            <div class="form-row">
              <label>Shop:</label>
              <select v-model="orderData.shop_id" class="form-control">
                <option v-for="shop in userShops" :key="shop.id" :value="shop.id">
                  {{ shop.name }}
                </option>
              </select>
            </div>

            <div class="form-row">
              <label>Type:</label>
              <select v-model="orderData.type" class="form-control" @change="handleTypeChange">
                <option value="dine-in">Dine-in</option>
                <option value="take-away">Take Away</option>
                <option value="delivery">Delivery</option>
              </select>
            </div>

            <div class="form-row" v-show="orderData.type === 'dine-in'">
              <label>Table #:</label>
              <input 
                v-model="orderData.table_number" 
                type="text" 
                class="form-control"
                placeholder="Table number"
              >
            </div>

            <div class="form-row" v-show="orderData.type === 'dine-in'">
              <label>Waiter:</label>
              <input 
                v-model="orderData.waiter_name" 
                type="text" 
                class="form-control"
                placeholder="Waiter name"
              >
            </div>

            <div class="form-row customer-row">
              <label>
                Customer:
                <a href="/customers/create" target="_blank" class="btn-add-customer">
                  <i class="fas fa-plus"></i> New
                </a>
              </label>
              <div class="customer-search">
                <input 
                  v-model="customerSearch"
                  type="text" 
                  class="form-control"
                  placeholder="Search by name or membership #"
                  @input="searchCustomers"
                  @focus="showCustomerDropdown = true"
                >
                <div v-if="showCustomerDropdown && filteredCustomers.length > 0" class="customer-dropdown">
                  <div 
                    v-for="customer in filteredCustomers" 
                    :key="customer.id"
                    class="customer-option"
                    @click="selectCustomer(customer)"
                  >
                    <span class="membership-number">{{ customer.membership_number }}</span>
                    <span class="customer-name">{{ customer.name }}</span>
                  </div>
                </div>
              </div>
            </div>

            <div class="form-row">
              <label>Notes:</label>
              <textarea 
                v-model="orderData.notes" 
                class="form-control"
                rows="2"
                placeholder="Special instructions..."
              ></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-block" :disabled="isLoading">
              <i class="fas fa-save"></i> 
              {{ isLoading ? 'Saving...' : 'Update Order' }}
            </button>
          </form>
        </div>
      </div>

      <!-- Order Items List -->
      <div class="order-items-section">
        <div class="section-header">
          <span>Order Items ({{ orderItems.length }})</span>
          <AmountDisplay :amount="itemsTotal" size="large" />
        </div>
        
        <div class="items-list" v-if="orderItems.length > 0">
          <OrderItemDisplay 
            v-for="item in orderItems" 
            :key="item.id"
            :item="item"
            :order-status="order.state"
            :user-type="userType"
            :compact="true"
            @update-quantity="handleQuantityUpdate"
            @delete-item="handleDeleteItem"
            @update-status="handleItemStatusUpdate"
            @add-note="handleItemNote"
          />
        </div>
        
        <div v-else class="empty-items">
          <i class="fas fa-shopping-basket"></i>
          <p>No items added yet</p>
          <span>Select products from the right panel</span>
        </div>

        <!-- Misc Product (Admin/Cashier only) -->
        <div v-if="canAddMiscProduct" class="misc-product-form">
          <div class="form-row">
            <input 
              v-model="miscProduct.name" 
              type="text" 
              placeholder="Miscellaneous item"
              class="form-control"
            >
            <input 
              v-model.number="miscProduct.price" 
              type="number" 
              step="0.01"
              placeholder="Price"
              class="form-control price-input"
            >
            <button 
              @click="addMiscProduct" 
              class="btn btn-primary"
              :disabled="!miscProduct.name || !miscProduct.price || isLoading"
            >
              <i class="fas fa-plus"></i> 
              {{ isLoading ? '...' : 'Add' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Discounts Section (Admin/Cashier only) -->
      <div v-if="canApplyDiscounts" class="discounts-section">
        <div class="section-header">
          <span>Discounts & Charges</span>
        </div>
        <div class="discounts-list">
          <label 
            v-for="discount in availableDiscounts" 
            :key="discount.id"
            class="discount-option"
            :class="{ 'is-charge': discount.type === 'CHARGES' }"
          >
            <input 
              type="checkbox" 
              :value="discount.id"
              v-model="selectedDiscounts"
              @change="toggleDiscount(discount.id)"
              :disabled="isLoading"
            >
            <span class="discount-name">{{ discount.name }}</span>
            <span class="discount-value">{{ discount.percentage }}%</span>
          </label>
        </div>
      </div>

      <!-- Order Totals -->
      <div class="order-totals">
        <div class="total-row">
          <span>Subtotal:</span>
          <AmountDisplay :amount="itemsTotal" />
        </div>
        <div class="total-row discount" v-if="discountAmount > 0">
          <span>Discount:</span>
          <AmountDisplay :amount="-discountAmount" color="negative" />
        </div>
        <div class="total-row discount" v-if="chargesAmount > 0">
          <span>Charges:</span>
          <AmountDisplay :amount="chargesAmount" />
        </div>
        <div class="total-row grand-total">
          <span>Net Payable:</span>
          <AmountDisplay :amount="netTotal" size="large" />
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="order-actions">
        <button 
          v-if="canPrint" 
          @click="printOrder"
          class="btn btn-secondary"
          :disabled="isLoading"
        >
          <i class="fas fa-print"></i> Print
        </button>
        <button 
          v-if="canProcessPayment && order.state === 'served'" 
          @click="processPayment"
          class="btn btn-success"
          :disabled="isLoading"
        >
          <i class="fas fa-credit-card"></i> Pay
        </button>
        <button 
          v-if="canCancel && order.state !== 'closed'" 
          @click="cancelOrder"
          class="btn btn-danger"
          :disabled="isLoading"
        >
          <i class="fas fa-times"></i> Cancel
        </button>
      </div>
    </div>

    <!-- Right Panel: Product Selection -->
    <div class="order-panel right-panel">
      <div class="products-header">
        <h3><i class="fas fa-utensils"></i> Products</h3>
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input 
            v-model="productSearch" 
            type="text" 
            placeholder="Search products..."
            class="form-control"
          >
        </div>
      </div>

      <!-- Categories Accordion -->
      <div class="categories-list">
        <div v-if="filteredCategories.length === 0" class="no-categories">
          <i class="fas fa-folder-open"></i>
          <p>No categories available</p>
        </div>
        <div 
          v-for="category in filteredCategories" 
          :key="category.id"
          class="category-section"
        >
          <div 
            class="category-header"
            @click="toggleCategory(category.id)"
            :class="{ active: expandedCategories.includes(category.id) }"
          >
            <span>{{ category.name }} ({{ category.products.length }})</span>
            <i class="fas fa-chevron-down"></i>
          </div>
          
          <div 
            v-show="expandedCategories.includes(category.id)"
            class="category-products"
          >
            <div v-if="category.products.length === 0" class="no-products">
              <p>No products in this category</p>
            </div>
            <div 
              v-for="product in category.products" 
              :key="product.id"
              class="product-card"
              :class="{ 'out-of-stock': product.is_available === false || product.is_available === 0 || product.quantity <= 0 }"
              @click="addProductToOrder(product)"
            >
              <div class="product-info">
                <span class="product-name">{{ product.name }}</span>
                <span class="product-price">
                  <AmountDisplay :amount="product.price" />
                </span>
              </div>
              <div class="product-stock" v-if="product.quantity <= product.low_stock_threshold">
                <span :class="{ 'low-stock': product.quantity > 0, 'out-of-stock': product.quantity <= 0 }">
                  {{ product.quantity > 0 ? product.quantity + ' left' : 'Out of stock' }}
                </span>
              </div>
              <button class="btn-add" :disabled="product.is_available === false || product.is_available === 0 || product.quantity <= 0 || isLoading">
                <i class="fas fa-plus"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import StatusBadge from '../business/StatusBadge.vue'
import AmountDisplay from '../business/AmountDisplay.vue'
import OrderItemDisplay from '../business/OrderItemDisplay.vue'

export default {
  name: 'OrderEdit',

  components: {
    StatusBadge,
    AmountDisplay,
    OrderItemDisplay
  },

  props: {
    order: {
      type: Object,
      required: true
    },
    user: {
      type: Object,
      required: true
    },
    userShops: {
      type: Array,
      default: () => []
    },
    categories: {
      type: Array,
      default: () => []
    },
    discounts: {
      type: Array,
      default: () => []
    },
    customers: {
      type: Array,
      default: () => []
    }
  },

  emits: ['order-updated', 'print-order', 'process-payment', 'cancel-order'],

  setup(props, { emit }) {
    // Loading state
    const isLoading = ref(false)
    
    // Reactive state - order data section collapsed by default for compact view
    const showOrderData = ref(false)
    const customerSearch = ref('')
    const showCustomerDropdown = ref(false)
    const productSearch = ref('')
    const expandedCategories = ref([])
    const selectedDiscounts = ref([])
    const orderData = ref({
      shop_id: props.order.shop_id,
      type: props.order.type,
      table_number: props.order.table_number,
      waiter_name: props.order.waiter_name,
      notes: props.order.notes,
      customer_id: props.order.customer_id
    })
    const miscProduct = ref({ name: '', price: null })
    const orderItems = ref([...props.order.items])

    // CSRF token for API calls
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || ''

    // Computed properties
    const userType = computed(() => props.user.type)

    const canEditOrderData = computed(() => {
      return ['admin', 'manager', 'waiter'].includes(userType.value)
    })

    const canAddMiscProduct = computed(() => {
      return ['admin', 'manager', 'cashier'].includes(userType.value)
    })

    const canApplyDiscounts = computed(() => {
      return ['admin', 'manager', 'cashier'].includes(userType.value)
    })

    const canPrint = computed(() => true)

    const canProcessPayment = computed(() => {
      return ['admin', 'manager', 'cashier'].includes(userType.value)
    })

    const canCancel = computed(() => {
      return ['admin', 'manager'].includes(userType.value)
    })

    const typeIcon = computed(() => {
      const icons = {
        'dine-in': 'fas fa-utensils',
        'take-away': 'fas fa-shopping-bag',
        'delivery': 'fas fa-motorcycle'
      }
      return icons[props.order.type] || 'fas fa-question'
    })

    const itemsTotal = computed(() => {
      return orderItems.value.reduce((sum, item) => sum + (parseFloat(item.total_price) || 0), 0)
    })

    const discountAmount = computed(() => {
      return selectedDiscounts.value.reduce((sum, discountId) => {
        const discount = props.discounts.find(d => d.id === discountId)
        if (discount && discount.type !== 'CHARGES') {
          return sum + (itemsTotal.value * discount.percentage / 100)
        }
        return sum
      }, 0)
    })

    const chargesAmount = computed(() => {
      return selectedDiscounts.value.reduce((sum, discountId) => {
        const discount = props.discounts.find(d => d.id === discountId)
        if (discount && discount.type === 'CHARGES') {
          return sum + (itemsTotal.value * discount.percentage / 100)
        }
        return sum
      }, 0)
    })

    const netTotal = computed(() => {
      return itemsTotal.value - discountAmount.value + chargesAmount.value
    })

    const filteredCustomers = computed(() => {
      if (!customerSearch.value) return []
      const search = customerSearch.value.toLowerCase()
      return props.customers.filter(c => 
        c.name.toLowerCase().includes(search) || 
        c.membership_number.toLowerCase().includes(search)
      ).slice(0, 10)
    })

    const filteredCategories = computed(() => {
      if (!productSearch.value) return props.categories
      const search = productSearch.value.toLowerCase()
      return props.categories.map(cat => ({
        ...cat,
        products: cat.products.filter(p => 
          p.name.toLowerCase().includes(search)
        )
      })).filter(cat => cat.products.length > 0)
    })

    const availableDiscounts = computed(() => props.discounts)

    // API Helper
    const apiCall = async (url, options = {}) => {
      const response = await fetch(url, {
        ...options,
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
          ...options.headers
        }
      })
      return response.json()
    }

    // Methods
    const toggleOrderData = () => {
      showOrderData.value = !showOrderData.value
    }

    const formatOrderType = (type) => {
      const types = {
        'dine-in': 'Dine-in',
        'take-away': 'Take Away',
        'delivery': 'Delivery'
      }
      return types[type] || type
    }

    const handleTypeChange = () => {
      if (orderData.value.type !== 'dine-in') {
        orderData.value.table_number = null
        orderData.value.waiter_name = null
      }
    }

    const searchCustomers = () => {
      showCustomerDropdown.value = true
    }

    const selectCustomer = (customer) => {
      orderData.value.customer_id = customer.id
      customerSearch.value = `${customer.membership_number} - ${customer.name}`
      showCustomerDropdown.value = false
    }

    const updateOrderData = async () => {
      isLoading.value = true
      try {
        const data = await apiCall(`/api/v1/orders/${props.order.id}`, {
          method: 'PUT',
          body: JSON.stringify(orderData.value)
        })
        
        if (data.success) {
          toastr.success('Order updated successfully')
          emit('order-updated', data.data)
        } else {
          toastr.error(data.error?.message || 'Failed to update order')
        }
      } catch (error) {
        console.error('Error:', error)
        toastr.error('An error occurred while updating the order')
      } finally {
        isLoading.value = false
      }
    }

    const handleQuantityUpdate = async ({ itemId, quantity }) => {
      isLoading.value = true
      try {
        const data = await apiCall(`/api/v1/orders/${props.order.id}/items/${itemId}`, {
          method: 'PUT',
          body: JSON.stringify({ quantity })
        })
        
        if (data.success) {
          // Update local item
          const item = orderItems.value.find(i => i.id === itemId)
          if (item) {
            item.quantity = quantity
            item.total_price = quantity * item.unit_price
          }
          toastr.success('Quantity updated')
        } else {
          toastr.error(data.error?.message || 'Failed to update quantity')
        }
      } catch (error) {
        console.error('Error:', error)
        toastr.error('An error occurred')
      } finally {
        isLoading.value = false
      }
    }

    const handleDeleteItem = async (itemId) => {
      if (!confirm('Remove this item from the order?')) return
      
      isLoading.value = true
      try {
        const data = await apiCall(`/api/v1/orders/${props.order.id}/items/${itemId}`, {
          method: 'DELETE'
        })
        
        if (data.success) {
          // Remove from local array
          orderItems.value = orderItems.value.filter(i => i.id !== itemId)
          toastr.success('Item removed')
        } else {
          toastr.error(data.error?.message || 'Failed to remove item')
        }
      } catch (error) {
        console.error('Error:', error)
        toastr.error('An error occurred')
      } finally {
        isLoading.value = false
      }
    }

    const handleItemStatusUpdate = async ({ itemId, status }) => {
      isLoading.value = true
      try {
        const data = await apiCall(`/api/v1/orders/${props.order.id}/items/${itemId}`, {
          method: 'PUT',
          body: JSON.stringify({ status })
        })
        
        if (data.success) {
          const item = orderItems.value.find(i => i.id === itemId)
          if (item) item.status = status
          toastr.success('Status updated')
        } else {
          toastr.error(data.error?.message || 'Failed to update status')
        }
      } catch (error) {
        console.error('Error:', error)
        toastr.error('An error occurred')
      } finally {
        isLoading.value = false
      }
    }

    const handleItemNote = async ({ itemId, note }) => {
      isLoading.value = true
      try {
        const data = await apiCall(`/api/v1/orders/${props.order.id}/items/${itemId}`, {
          method: 'PUT',
          body: JSON.stringify({ notes: note })
        })
        
        if (data.success) {
          const item = orderItems.value.find(i => i.id === itemId)
          if (item) item.notes = note
          toastr.success('Note added')
        } else {
          toastr.error(data.error?.message || 'Failed to add note')
        }
      } catch (error) {
        console.error('Error:', error)
        toastr.error('An error occurred')
      } finally {
        isLoading.value = false
      }
    }

    const addMiscProduct = async () => {
      if (!miscProduct.value.name || !miscProduct.value.price) return
      
      isLoading.value = true
      try {
        const data = await apiCall(`/api/v1/orders/${props.order.id}/items`, {
          method: 'POST',
          body: JSON.stringify({
            product_name: miscProduct.value.name,
            unit_price: miscProduct.value.price,
            quantity: 1,
            is_misc: true
          })
        })
        
        if (data.success) {
          orderItems.value.push(data.data)
          miscProduct.value = { name: '', price: null }
          toastr.success('Item added')
        } else {
          toastr.error(data.error?.message || 'Failed to add item')
        }
      } catch (error) {
        console.error('Error:', error)
        toastr.error('An error occurred')
      } finally {
        isLoading.value = false
      }
    }

    const toggleDiscount = async (discountId) => {
      // Use Livewire for discounts if available
      if (typeof Livewire !== 'undefined') {
        Livewire.emit('toggleDiscount', discountId)
      }
    }

    const addProductToOrder = async (product) => {
      if (product.is_available === false || product.is_available === 0 || product.quantity <= 0) return
      
      isLoading.value = true
      try {
        const data = await apiCall(`/api/v1/orders/${props.order.id}/items`, {
          method: 'POST',
          body: JSON.stringify({
            product_id: product.id,
            unit_price: product.price,
            quantity: 1
          })
        })
        
        if (data.success) {
          orderItems.value.push(data.data)
          toastr.success(`${product.name} added`)
        } else {
          toastr.error(data.error?.message || 'Failed to add item')
        }
      } catch (error) {
        console.error('Error:', error)
        toastr.error('An error occurred')
      } finally {
        isLoading.value = false
      }
    }

    const toggleCategory = (categoryId) => {
      const index = expandedCategories.value.indexOf(categoryId)
      if (index > -1) {
        expandedCategories.value.splice(index, 1)
      } else {
        expandedCategories.value.push(categoryId)
      }
    }

    const printOrder = () => {
      emit('print-order', props.order.id)
    }

    const processPayment = () => {
      if (confirm('Process payment for this order?')) {
        emit('process-payment', props.order.id)
      }
    }

    const cancelOrder = async () => {
      if (!confirm('Are you sure you want to cancel this order?')) return
      
      isLoading.value = true
      try {
        const data = await apiCall(`/api/v1/orders/${props.order.id}`, {
          method: 'DELETE'
        })
        
        if (data.success) {
          toastr.success('Order cancelled')
          emit('cancel-order', props.order.id)
        } else {
          toastr.error(data.error?.message || 'Failed to cancel order')
        }
      } catch (error) {
        console.error('Error:', error)
        toastr.error('An error occurred')
      } finally {
        isLoading.value = false
      }
    }

    // Initialize expanded categories
    onMounted(() => {
      if (props.categories.length > 0) {
        expandedCategories.value = [props.categories[0].id]
      }
      
      // Initialize selected discounts
      selectedDiscounts.value = props.order.discounts?.map(d => d.id) || []
      
      // Initialize customer search if customer exists
      if (props.order.customer) {
        customerSearch.value = `${props.order.customer.membership_number} - ${props.order.customer.name}`
      }
    })

    // Close customer dropdown when clicking outside
    const handleClickOutside = (event) => {
      if (!event.target.closest('.customer-search')) {
        showCustomerDropdown.value = false
      }
    }

    onMounted(() => {
      document.addEventListener('click', handleClickOutside)
    })

    onUnmounted(() => {
      document.removeEventListener('click', handleClickOutside)
    })

    return {
      isLoading,
      showOrderData,
      customerSearch,
      showCustomerDropdown,
      productSearch,
      expandedCategories,
      selectedDiscounts,
      orderData,
      miscProduct,
      orderItems,
      userType,
      canEditOrderData,
      canAddMiscProduct,
      canApplyDiscounts,
      canPrint,
      canProcessPayment,
      canCancel,
      typeIcon,
      itemsTotal,
      discountAmount,
      chargesAmount,
      netTotal,
      filteredCustomers,
      filteredCategories,
      availableDiscounts,
      toggleOrderData,
      formatOrderType,
      handleTypeChange,
      searchCustomers,
      selectCustomer,
      updateOrderData,
      handleQuantityUpdate,
      handleDeleteItem,
      handleItemStatusUpdate,
      handleItemNote,
      addMiscProduct,
      toggleDiscount,
      addProductToOrder,
      toggleCategory,
      printOrder,
      processPayment,
      cancelOrder
    }
  }
}
</script>

<style scoped>
/* Optimized for 1920x1080 HD resolution */
.order-edit-container {
  display: flex;
  flex-direction: row;
  gap: 12px;
  height: calc(100vh - 180px);
  min-height: 700px;
  max-width: 1920px;
  margin: 0 auto;
}

.order-panel {
  background: #fff;
  border-radius: 6px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.left-panel {
  flex: 0 0 40%;
  max-width: 600px;
}

.right-panel {
  flex: 1;
}

/* Order Header - Compact */
.order-header {
  padding: 10px 14px;
  background: #f8f9fa;
  border-bottom: 1px solid #dee2e6;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-shrink: 0;
}

.order-meta {
  display: flex;
  align-items: center;
  gap: 10px;
}

.order-title {
  margin: 0;
  font-size: 16px;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 6px;
}

.order-title i {
  color: #007bff;
  font-size: 14px;
}

.order-type-badge {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}

.order-type-badge.dine-in {
  background: #d4edda;
  color: #155724;
}

.order-type-badge.take-away {
  background: #fff3cd;
  color: #856404;
}

.order-type-badge.delivery {
  background: #d1ecf1;
  color: #0c5460;
}

/* Section Headers - Compact */
.section-header {
  padding: 8px 12px;
  background: #e9ecef;
  border-bottom: 1px solid #dee2e6;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-weight: 600;
  font-size: 13px;
  cursor: pointer;
  user-select: none;
  flex-shrink: 0;
}

.section-header i {
  transition: transform 0.3s;
  font-size: 12px;
}

.section-header i.rotated {
  transform: rotate(180deg);
}

/* Order Form - Compact */
.order-form {
  padding: 12px;
}

.form-row {
  margin-bottom: 8px;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.form-row label {
  font-size: 12px;
  font-weight: 600;
  color: #495057;
}

.form-control {
  padding: 6px 10px;
  border: 1px solid #ced4da;
  border-radius: 4px;
  font-size: 13px;
  height: 32px;
}

.form-control:focus {
  outline: none;
  border-color: #007bff;
  box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
}

.customer-row {
  position: relative;
}

.btn-add-customer {
  margin-left: 8px;
  font-size: 12px;
  color: #17a2b8;
  text-decoration: none;
}

.customer-search {
  position: relative;
}

.customer-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: #fff;
  border: 1px solid #ced4da;
  border-radius: 4px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  z-index: 100;
  max-height: 200px;
  overflow-y: auto;
}

.customer-option {
  padding: 10px 12px;
  cursor: pointer;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #f1f3f5;
}

.customer-option:hover {
  background: #f8f9fa;
}

.membership-number {
  font-weight: 600;
  color: #007bff;
  font-size: 12px;
}

.customer-name {
  color: #212529;
  font-size: 13px;
}

/* Order Items - Compact for 10-15 items visibility */
.order-items-section {
  flex: 1;
  overflow-y: auto;
  min-height: 0;
}

.items-list {
  padding: 4px;
}

.items-list > * {
  margin-bottom: 4px;
}

.empty-items {
  padding: 30px 20px;
  text-align: center;
  color: #6c757d;
}

.empty-items i {
  font-size: 36px;
  margin-bottom: 12px;
  color: #dee2e6;
}

.empty-items p {
  font-weight: 600;
  margin-bottom: 4px;
  font-size: 14px;
}

.empty-items span {
  font-size: 12px;
}

/* Misc Product Form - Compact */
.misc-product-form {
  padding: 8px 12px;
  border-top: 1px solid #dee2e6;
  background: #f8f9fa;
  flex-shrink: 0;
}

.misc-product-form .form-row {
  flex-direction: row;
  gap: 6px;
  margin-bottom: 0;
}

.misc-product-form .form-row input {
  flex: 1;
  height: 28px;
  padding: 4px 8px;
  font-size: 12px;
}

.misc-product-form .price-input {
  flex: 0 0 80px;
}

/* Discounts */
.discounts-section {
  border-top: 1px solid #dee2e6;
  flex-shrink: 0;
}

.discounts-list {
  padding: 8px 12px;
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.discount-option {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 4px 8px;
  background: #d4edda;
  border-radius: 4px;
  cursor: pointer;
  font-size: 11px;
}

.discount-option.is-charge {
  background: #fff3cd;
}

.discount-option input {
  margin: 0;
}

.discount-name {
  font-weight: 600;
}

.discount-value {
  color: #6c757d;
  font-size: 10px;
}

/* Order Totals */
.order-totals {
  padding: 10px 12px;
  background: #f8f9fa;
  border-top: 2px solid #dee2e6;
  flex-shrink: 0;
}

.total-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 4px;
  font-size: 13px;
}

.total-row.discount {
  color: #28a745;
}

.total-row.grand-total {
  font-size: 16px;
  font-weight: 700;
  padding-top: 6px;
  border-top: 1px solid #dee2e6;
  margin-top: 6px;
}

/* Order Actions */
.order-actions {
  padding: 10px 12px;
  display: flex;
  gap: 6px;
  border-top: 1px solid #dee2e6;
  flex-shrink: 0;
}

.order-actions .btn {
  flex: 1;
  padding: 8px 12px;
  font-size: 13px;
}

/* Right Panel - Products */
.products-header {
  padding: 12px;
  background: #f8f9fa;
  border-bottom: 1px solid #dee2e6;
  flex-shrink: 0;
}

.products-header h3 {
  margin: 0 0 10px 0;
  font-size: 15px;
  display: flex;
  align-items: center;
  gap: 6px;
}

.search-box {
  position: relative;
}

.search-box i {
  position: absolute;
  left: 10px;
  top: 50%;
  transform: translateY(-50%);
  color: #6c757d;
  font-size: 12px;
}

.search-box input {
  width: 100%;
  padding: 6px 10px 6px 30px;
  height: 30px;
  font-size: 13px;
}

/* Categories */
.categories-list {
  flex: 1;
  overflow-y: auto;
  padding: 6px;
}

.category-section {
  margin-bottom: 6px;
  border: 1px solid #dee2e6;
  border-radius: 4px;
  overflow: hidden;
}

.category-header {
  padding: 8px 12px;
  background: #e9ecef;
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  font-weight: 600;
  font-size: 13px;
  transition: background 0.2s;
}

.category-header:hover {
  background: #dee2e6;
}

.category-header.active {
  background: #007bff;
  color: #fff;
}

.category-header i {
  transition: transform 0.3s;
  font-size: 12px;
}

.category-header.active i {
  transform: rotate(180deg);
}

.category-products {
  padding: 6px;
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 6px;
}

.no-categories,
.no-products {
  padding: 30px 20px;
  text-align: center;
  color: #6c757d;
}

.no-categories i {
  font-size: 36px;
  margin-bottom: 12px;
  display: block;
}

.no-categories p,
.no-products p {
  font-size: 13px;
  margin: 0;
}

/* Product Cards */
.product-card {
  padding: 10px;
  background: #fff;
  border: 1px solid #dee2e6;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  justify-content: space-between;
  align-items: center;
  transition: all 0.2s;
  min-height: 50px;
}

.product-card:hover {
  border-color: #007bff;
  box-shadow: 0 2px 4px rgba(0, 123, 255, 0.1);
}

.product-card.out-of-stock {
  opacity: 0.5;
  cursor: not-allowed;
  background: #f8f9fa;
}

.product-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
  flex: 1;
  min-width: 0;
}

.product-name {
  font-weight: 600;
  font-size: 13px;
  color: #212529;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.product-price {
  font-size: 12px;
  color: #28a745;
  font-weight: 600;
}

.product-stock {
  font-size: 10px;
  text-align: right;
}

.product-stock .low-stock {
  color: #ffc107;
}

.product-stock .out-of-stock {
  color: #dc3545;
}

.btn-add {
  width: 26px;
  height: 26px;
  border: none;
  background: #28a745;
  color: #fff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background 0.2s;
  font-size: 12px;
  margin-left: 8px;
  flex-shrink: 0;
}

.btn-add:hover:not(:disabled) {
  background: #218838;
}

.btn-add:disabled {
  background: #6c757d;
  cursor: not-allowed;
}

/* Buttons */
.btn {
  padding: 8px 12px;
  border: none;
  border-radius: 4px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
  transition: all 0.2s;
}

.btn:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  background: #007bff;
  color: #fff;
}

.btn-primary:hover {
  background: #0056b3;
}

.btn-secondary {
  background: #6c757d;
  color: #fff;
}

.btn-secondary:hover {
  background: #545b62;
}

.btn-success {
  background: #28a745;
  color: #fff;
}

.btn-success:hover {
  background: #218838;
}

.btn-danger {
  background: #dc3545;
  color: #fff;
}

.btn-danger:hover {
  background: #c82333;
}

.btn-block {
  width: 100%;
}

.btn-sm {
  padding: 4px 8px;
  font-size: 11px;
}

/* Responsive */
@media (max-width: 768px) {
  .order-edit-container {
    flex-direction: column;
    height: auto;
  }

  .left-panel,
  .right-panel {
    flex: 1;
    max-width: 100%;
  }

  .category-products {
    grid-template-columns: 1fr;
  }
}
</style>
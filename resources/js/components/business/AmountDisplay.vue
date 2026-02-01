<template>
  <span 
    class="amount-display"
    :class="[`size-${size}`, `color-${effectiveColor}`]"
  >
    <span v-if="showSign" class="currency-symbol">{{ currencySymbol }}</span>
    <span class="amount-value">{{ formattedAmount }}</span>
  </span>
</template>

<script>
export default {
  name: 'AmountDisplay',

  props: {
    amount: {
      type: Number,
      default: 0
    },
    currency: {
      type: String,
      default: 'USD'
    },
    showSign: {
      type: Boolean,
      default: true
    },
    size: {
      type: String,
      default: 'medium',
      validator: (value) => ['small', 'medium', 'large'].includes(value)
    },
    color: {
      type: String,
      default: 'auto',
      validator: (value) => ['auto', 'positive', 'negative', 'neutral'].includes(value)
    }
  },

  computed: {
    currencySymbol() {
      const symbols = {
        USD: '$',
        EUR: '€',
        GBP: '£',
        JPY: '¥',
        CAD: 'C$',
        AUD: 'A$'
      }
      return symbols[this.currency] || '$'
    },

    formattedAmount() {
      if (this.amount === null || this.amount === undefined) {
        return '0.00'
      }
      
      const num = parseFloat(this.amount)
      
      // Format with commas and 2 decimal places
      return num.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      })
    },

    effectiveColor() {
      if (this.color !== 'auto') {
        return this.color
      }

      const num = parseFloat(this.amount) || 0
      if (num > 0) return 'positive'
      if (num < 0) return 'negative'
      return 'neutral'
    }
  }
}
</script>

<style scoped>
.amount-display {
  display: inline-flex;
  align-items: center;
  font-weight: 600;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Size variants */
.size-small {
  font-size: 14px;
}

.size-medium {
  font-size: 16px;
}

.size-large {
  font-size: 24px;
}

/* Color variants */
.color-positive {
  color: #28a745;
}

.color-negative {
  color: #dc3545;
}

.color-neutral {
  color: #6c757d;
}

.currency-symbol {
  margin-right: 2px;
  opacity: 0.8;
}

.amount-value {
  font-variant-numeric: tabular-nums;
}
</style>
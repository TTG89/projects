<!-- SizeQuantityChart.vue -->
<template>
  <div class="productSizeQuantityChartContainer" v-if="currentProduct && currentProduct.variants">
    <div class="productSizeQuantityTitleBlock">
      <p id="productSizeQuantityTitle">SIZE & QUANTITY</p>
      <p id="productSizeQuantityDescription">Available inventory below size input.</p>
    </div>
    <div class="productSizeChart">
      <button id="productSizeChartbtn" @click="$emit('toggleSizeChartModal')">Size Chart</button>
      <div v-if="showSizeChartModal" class="modal">
        <div class="modal-content">
          <span class="close" @click="$emit('toggleSizeChartModal')">&times;</span>
          <SizeChart :vendor="currentProduct.vendor" />
        </div>
      </div>
    </div>
    <div class="productSizeContainer">
      <div v-for="variant in discountedVariantPrices" :key="variant.id" class="productSizeQuantity">
        <span class="sizeTitle">{{ variant.title }}</span>
        <span class="sizePrice">${{ variant.discountedPrice }}</span>
        <input type="number" :value="localSelectedVariants[variant.id] === 0 ? '' : localSelectedVariants[variant.id]"
          min="0" class="sizeInput" :disabled="variant.inventoryQuantity === 0"
          @input="updateQuantity(variant.id, $event.target.value)" placeholder="0" />
        <span class="sizeQuantity">{{ formatInventoryQuantity(variant.inventoryQuantity) }}</span>
      </div>
    </div>
  </div>
</template>

<script>
import SizeChart from './SizeChart.vue';

export default {
  props: {
    currentProduct: Object,
    discountedVariantPrices: Array,
    showSizeChartModal: Boolean,
    selectedVariants: Object,
  },
  components: {
    SizeChart,
  },
  data() {
    return {
      localSelectedVariants: { ...this.selectedVariants },
    };
  },
  watch: {
    selectedVariants: {
      handler(newValue) {
        this.localSelectedVariants = { ...newValue };
      },
      deep: true,
    },
  },
  methods: {
    formatInventoryQuantity(inventory) {
      if (inventory === 0) {
        return 'Out of stock';
      }
      if (inventory >= 1000) {
        return '999+';
      }
      if (inventory < 100) {
        return 'Low stock';
      }
      return inventory.toString();
    },
    updateQuantity(variantId, quantity) {
      const parsedQuantity = Number(quantity);
      this.localSelectedVariants[variantId] = isNaN(parsedQuantity) ? 0 : parsedQuantity;
      this.$emit('updateQuantity', variantId, isNaN(parsedQuantity) ? 0 : parsedQuantity);
    },
  },
};
</script>

<style scoped>
.productSizeQuantityTitleBlock {
  display: inline-block;
}

#productSizeQuantityTitle {
  font-size: 18px;
  color: #000000;
  font-weight: 700;
  margin-top: 0px;
  margin-bottom: 5px;
}

#productSizeQuantityDescription {
  font-size: 14px;
  font-style: italic;
  line-height: 0;
  margin: 0;
}

.productSizeChart {
  float: right;
}

#productSizeChartbtn {
  border: none;
  background: none;
  font-size: 16px;
  color: #253646;
  text-decoration: underline;
  font-weight: 400;
}

.productSizeContainer {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  max-width: 100%;
  margin-top: 16px;
  margin-bottom: 10px;
  gap: 5px;
}

.productSizeQuantity {
  max-width: 80px;
}

.sizeTitle {
  display: block;
  text-align: center;
  color: #000000;
  font-size: 16px;
  font-weight: bold;
}

.sizePrice {
  display: block;
  text-align: center;
  font-size: 14px;
  font-weight: 400;
  color: #515E6B;
  line-height: 1;
  margin-bottom: 5px;
}

.sizeQuantity {
  display: block;
  font-size: 12px;
  color: #515E6B;
  font-style: italic;
  text-align: center;
  margin: 5px;
}

.sizeInput {
  max-width: 100%;
  height: 40px;
  text-align: center;
}

.sizeInput:disabled {
  background-color: #f0f0f0;
  color: #ccc;
}
</style>

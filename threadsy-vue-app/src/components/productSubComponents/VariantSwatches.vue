<!-- VariantSwatches.vue -->
<template>
  <div class="swatchContainerBlock">
    <div class="swatchContainer">
      <div v-for="variantProduct in variantProducts" :key="variantProduct.id" class="productSwatchDisplay">
        <span 
          v-for="color in getColorCodes(variantProduct)" 
          :key="color.id" 
          class="productSwatch"
          :class="{ 'out-of-stock': color.inventoryQuantity === 0, 'selected-swatch': selectedColor === color.colorName }"
          @click="color.inventoryQuantity !== 0 && loadProductData(variantProduct, color)" 
          @mouseover="handleMouseOver(color.colorName)" 
          @mouseleave="handleMouseLeave()" 
          :style="{ backgroundColor: '#' + color.colorCode }"
        >
          <div class="tooltip" v-if="hoveredColor === color.colorName">{{ color.colorName }}</div>
        </span>
      </div>
    </div>
    <div class="bulkLoading" v-if="isLoadingMoreSwatches">Loading...</div>
    <div v-if="moreSwatchesAvailable && !isLoadingMoreSwatches" class="loadMoreSwatches">
      <button @click="handleLoadMoreSwatches">Load More Swatches</button>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    variantProducts: {
      type: Array,
      default: () => [],
    },
    moreSwatchesAvailable: {
      type: Boolean,
      required: true,
    },
    isLoadingMoreSwatches: {
      type: Boolean,
      required: true,
    },
    initialColor: {
      type: String,
      default: ''
    }
  },
  data() {
    return {
      hoveredColor: '',
      selectedColor: this.initialColor, // Set the initial selected color
    };
  },
  watch: {
    initialColor(newColor) {
      this.selectedColor = newColor;
    }
  },
  methods: {
    getColorCodes(variantProduct) {      
      const colorCodes = [{
        id: variantProduct.id,
        colorCode: variantProduct.swatchColorCodes,
        colorName: variantProduct.productColor,
        inventoryQuantity: (variantProduct.variants || []).reduce((sum, variant) => sum + variant.inventoryQuantity, 0)
      }];
      return colorCodes;
    },
    loadProductData(product, color) {
      this.selectedColor = color.colorName; // Set the selected color
      this.$emit('loadProductData', product, color);
    },
    handleMouseOver(colorName) {
      this.hoveredColor = colorName;
    },
    handleMouseLeave() {
      this.hoveredColor = '';
    },
    handleLoadMoreSwatches() {
      this.$emit('loadMoreSwatches');
    },
  },
};
</script>

<style scoped>
.swatchContainerBlock {
  margin-bottom: 24px;
  padding-bottom: 15px;
  border-bottom: 1px solid #DCDFE1;
}

.swatchContainer {
  display: flex;
  flex-wrap: wrap;
}

.productSwatch {
  position: relative;
  border-radius: 20px;
  width: 25px;
  height: 25px;
  display: inline-block !important;
  cursor: pointer;
  margin: 3px;
  transition: opacity 0.3s ease;
}

.productSwatch.selected-swatch {
  border: 2px solid #000; 
  box-shadow: 2px 2px 10px 0px rgba(0, 0, 0, 0.50);
}

.productSwatch:not(.out-of-stock) {
  opacity: 1;
}

.productSwatch.out-of-stock {
  cursor: not-allowed;
  opacity: 0.5;
}

.productSwatch.out-of-stock::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 20px;
  height: 2px;
  background-color: red;
  transform: translate(-50%, -50%) rotate(45deg);
}

.tooltip {
  position: absolute;
  bottom: 100%;
  left: 50%;
  transform: translateX(-50%);
  padding: 5px;
  color: #fff;
  background: #333;
  border-radius: 6px;
  white-space: nowrap;
  opacity: 1;
  visibility: visible;
  margin-bottom: 5px;
}

.loadMoreSwatches button {
  background: none;
  border: none;
  font-size: 16px;
  text-decoration: underline;
  padding: 0px;
  text-transform: capitalize;
  margin-bottom: 15px;
}
</style>

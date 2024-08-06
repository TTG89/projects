<!-- BulkOrdering.vue -->
<template>
  <div class="bulkOrderingContainer" v-if="product">
    <h2>Bulk Ordering</h2>
    <p>1. Select Color(s)</p>
    <p>2. Enter Quantity Amount</p>
    <div class="colorsContainer">
      <div class="colorBlock" v-for="colorVariant in localBulkOrderingProducts" :key="colorVariant.color">
        <div class="colorDisplay" @click="toggleSizeInput(colorVariant)" @mouseover="handleMouseOver(colorVariant)"
          @mouseleave="handleMouseLeave">
          <span class="bulkColor" :style="{ backgroundColor: colorVariant.colorCode }"
            :class="{ 'selected': isActive(colorVariant), 'out-of-stock': isOutOfStock(colorVariant) }">
          </span>
          <div class="bulkToolTip" v-show="hoveredColor === colorVariant.color">{{ hoveredColor }}</div>
        </div>
      </div>
    </div>
    <div class="detailsContainer">
      <div v-for="colorVariant in localBulkOrderingProducts" :key="colorVariant.color" class="sizeDetailsBlock"
        v-show="isActive(colorVariant)">
        <span class="selectedBulkColor" :style="{ backgroundColor: colorVariant.colorCode }"></span>
        <div v-if="colorVariant.active" class="selectedColorName">
          {{ colorVariant.color }}
        </div>
        <div class="sizeDetails">
          <div class="sizeBlock" v-for="sizeVariant in colorVariant.sizes" :key="sizeVariant.size">
            <div class="sizeLabel">{{ sizeVariant.size }}</div>
            <div class="bulkPrice">${{ sizeVariant.price }}</div>
            <input type="number" :value="sizeVariant.quantity === 0 ? '' : sizeVariant.quantity"
              :disabled="sizeVariant.inventoryQuantity === 0" min="0" class="sizeInput bulkColorInput" placeholder="0"
              @input="updateQuantity(sizeVariant.variantId, $event.target.value)" />
            <div class="inventoryInfo">
              <div class="bulkPriceInventory">
                {{ formatInventoryQuantity(sizeVariant.inventoryQuantity) }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="productBtns">
      <div class="bulkLoading" v-if="isLoadingMoreProducts">Loading...</div>
      <button class="btn btnPrimary" @click="handleBulkAddToCart">Add All to Cart</button>
      <button class="btn btnReset" @click="resetAll">Clear</button>
      <button @click="handleBuyNow" class="btn btnBuyNow">
        <span v-if="itemCount > 0">Checkout</span>
        <span v-else>Buy Now</span>
      </button>
      <button class="btn load-more-btn btnSecondary" @click="loadMoreProducts" v-if="moreProductsAvailable">
        Load More
      </button>
    </div>
    <div v-if="showSuccessMessage && lastAction === 'addAll'" class="success-message">
      {{ successMessage }}
    </div>
    <div v-if="!isQuantityEntered && attemptedBuyNow" class="error-message">Please enter a quantity before buying.
    </div>
  </div>
</template>

<script>
import { addToCart } from "@/utils/shopifyCart";
import { fetchBulkOrderProducts } from "@/utils/shopifyGraphQLFetchBulk";
import { fetchCartTotal } from '@/utils/shopifyFetchCartTotal';

export default {
  name: "BulkOrdering",
  props: {
    bulkOrderingProducts: Array,
    sizes: Array,
    product: Object,
    handle: String,
    tierLevel: String,
    cartTotal: Number,
    itemCount: Number,
    originalTotal: Number,
  },
  data() {
    return {
      localBulkOrderingProducts: [],
      lastAction: "",
      showSuccessMessage: false,
      successMessage: "",
      nextPageInfo: null,
      moreProductsAvailable: true,
      isLoadingMoreProducts: false,
      bulkError: "",
      hoveredColor: "",
      attemptedAddToCart: false,
      attemptedBuyNow: false,
    };
  },
  watch: {
    handle: {
      handler(newVal, oldVal) {
        if (newVal && newVal !== oldVal) {
          this.loadBulkOrderProducts();
        }
      },
      immediate: true
    },
    product: {
      handler(newVal, oldVal) {
        if (newVal && newVal !== oldVal) {
          this.loadBulkOrderProducts();
        }
      },
      immediate: true,
      deep: true
    },
  },
  computed: {
    isQuantityEntered() {
      return this.hasQuantityEntered();
    },
  },
  methods: {
    async loadBulkOrderProducts() {
      try {
        const fetchedData = await fetchBulkOrderProducts(this.handle);
        if (fetchedData && fetchedData.products) {
          this.localBulkOrderingProducts = this.processBulkData(fetchedData);
          this.moreProductsAvailable = !!fetchedData.nextPageInfo;
          this.nextPageInfo = fetchedData.nextPageInfo;
          //console.log('Next page info after loadBulkOrderProducts:', this.nextPageInfo);
        } else {
          this.moreProductsAvailable = false;
        }
      } catch (error) {
        //console.error("Failed to load bulk order products:", error.message);
        this.moreProductsAvailable = false;
      }
    },
    processBulkData(fetchedData) {
      if (!fetchedData || !Array.isArray(fetchedData.products)) {
        //console.error('Invalid or no product data:', fetchedData);
        return [];
      }

      const processedData = fetchedData.products.map(product => {
        const colorName = product.productColor || "Unknown Color";
        const colorCode = `#${product.swatchColorCodes || "000"}`;

        const sizes = product.variants.map(variant => ({
          size: variant.title,
          price: variant.price,
          quantity: 0,
          inventoryQuantity: variant.inventoryQuantity,
          variantId: variant.id,
        }));

        return {
          color: colorName,
          colorCode: colorCode,
          sizes: sizes,
        };
      });

      return processedData;
    },
    async loadMoreProducts() {
      if (!this.nextPageInfo || this.isLoadingMoreProducts) {
        return;
      }
      this.isLoadingMoreProducts = true;
      try {
        const fetchedData = await fetchBulkOrderProducts(this.handle, this.nextPageInfo);
        if (fetchedData && fetchedData.products) {
          this.localBulkOrderingProducts.push(...this.processBulkData(fetchedData));
          this.moreProductsAvailable = !!fetchedData.nextPageInfo;
          this.nextPageInfo = fetchedData.nextPageInfo;
          //console.log('Next page info after loadMoreProducts:', this.nextPageInfo);
        } else {
          this.moreProductsAvailable = false;
        }
      } catch (error) {
        console.error("Failed to load more bulk order products:", error.message);
        this.moreProductsAvailable = false;
      } finally {
        this.isLoadingMoreProducts = false;
      }
    },
    updateQuantity(variantId, newQuantity) {
      const product = this.localBulkOrderingProducts.find(p => p.sizes.some(s => s.variantId === variantId));
      if (product) {
        const size = product.sizes.find(s => s.variantId === variantId);
        if (size) {
          size.quantity = isNaN(newQuantity) || newQuantity === '' ? 0 : Number(newQuantity);
        } else {
          console.log(`No size found for variantId ${variantId}`);
        }
      } else {
        console.log(`No product found for variantId ${variantId}`);
      }
    },
    resetAll() {
      this.localBulkOrderingProducts.forEach(colorVariant => {
        colorVariant.active = false;
        colorVariant.sizes.forEach(sizeVariant => {
          sizeVariant.quantity = 0;
        });
      });
      this.hoveredColor = '';
      this.successMessage = '';
      this.showSuccessMessage = false;
    },
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
    isOutOfStock(colorVariant) {
      return colorVariant.sizes.every(sizeVariant => sizeVariant.inventoryQuantity === 0);
    },
    toggleSizeInput(colorVariant) {
      colorVariant.active = !colorVariant.active;
    },
    isActive(colorVariant) {
      return colorVariant.active;
    },
    handleMouseOver(colorVariant) {
      this.hoveredColor = colorVariant.color;
    },
    handleMouseLeave() {
      this.hoveredColor = '';
    },
    async handleBulkAddToCart() {
      this.addingToCart = true;
      this.lastAction = 'addAll';
      this.showSuccessMessage = false;
      let allAddedSuccessfully = true;
      try {
        for (const colorVariant of this.localBulkOrderingProducts) {
          if (colorVariant.active) {
            for (const sizeVariant of colorVariant.sizes) {
              if (sizeVariant.quantity > 0) {
                const result = await addToCart(sizeVariant.variantId, sizeVariant.quantity);
                if (!result) {
                  allAddedSuccessfully = false;
                } else {
                  await this.updateCartTotal();
                }
              }
            }
          }
        }
      } catch (error) {
        this.successMessage = "Failed to add items to the cart due to an error.";
        this.showSuccessMessage = true;
        allAddedSuccessfully = false;
      } finally {
        this.addingToCart = false;
        if (allAddedSuccessfully) {
          this.$emit('updatecart');
          this.successMessage = "All items added to cart successfully!";
          this.showSuccessMessage = true;
        } else {
          this.successMessage = "Some items could not be added to the cart.";
          this.showSuccessMessage = true;
        }
        setTimeout(() => {
          this.showSuccessMessage = false;
          this.resetAll();
        }, 800);
      }
    },
    async handleBuyNow() {
      if (this.itemCount > 0 || this.hasQuantityEntered()) {
        if (!this.itemCount && this.hasQuantityEntered()) {
          this.addingToCart = true;
          try {
            for (const colorVariant of this.localBulkOrderingProducts) {
              if (colorVariant.active) {
                for (const sizeVariant of colorVariant.sizes) {
                  if (sizeVariant.quantity > 0) {
                    await addToCart(sizeVariant.variantId, sizeVariant.quantity);
                  }
                }
              }
            }
            await this.updateCartTotal();
          } finally {
            this.addingToCart = false;
          }
        }
        window.location.href = '/checkout';
      } else {
        this.errorMessage = "Please enter a quantity before proceeding to checkout.";
        this.attemptedBuyNow = true;
        setTimeout(() => {
          this.attemptedBuyNow = false;
          this.errorMessage = "";
        }, 2500);
      }
    },
    async updateCartTotal() {
      try {
        const { cartTotalInDollars, itemCount, originalTotalInDollars } = await fetchCartTotal();
        this.$emit('updatecarttotal', { cartTotalInDollars, itemCount, originalTotalInDollars });
      } catch (error) {
        console.error('Error fetching cart total:', error);
      }
    },
    hasQuantityEntered() {
      return this.localBulkOrderingProducts.some(colorVariant =>
        colorVariant.sizes.some(sizeVariant => sizeVariant.quantity > 0)
      );
    },
  },
  mounted() {
    this.loadBulkOrderProducts();
  }
};
</script>

<style scoped>
h2 {
  font-size: 30px;
  font-style: normal;
  font-weight: 700;
  text-transform: uppercase;
  margin-bottom: 5px;
}

.bulkOrderingContainer {
  width: 100%;
}

.bulkOrderingContainer p {
  font-size: 16px;
  color: #000;
  margin: 0px;
  font-style: italic;
  line-height: 1.2;
}

.colorsContainer {
  display: flex;
  justify-content: flex-start;
  flex-wrap: wrap;
  margin: 20px 0;
  gap: 15px;
}

.detailsContainer {
  margin: 20px 0px;
}

.sizeDetailsBlock {
  background: #fff;
  border: 1px solid #DCDFE1;
  padding: 10px;
  box-shadow: 2px 2px 10px 0px rgba(0, 0, 0, 0.17);
  margin-bottom: 10px;
  text-align: center
}

.sizeDetails {
  display: flex;
  flex-wrap: wrap;
  padding: 10px 0px;
}

.sizeBlock {
  flex: 1;
  text-align: center;
}

.colorDisplay {
  position: relative;
  border: 1px solid #fff;
  border-radius: 50px;
}

.bulkColor.selected {
  border: 2px solid #222;
  box-shadow: 2px 2px 10px 0px rgba(0, 0, 0, 0.50);
  border-radius: 50px;
}

.bulkColor.out-of-stock {
  position: relative;
  background-color: red;
}

.bulkColor.out-of-stock::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 100%;
  height: 1px;
  background: #fff;
  transform: translate(-50%, -50%) rotate(-45deg);
}

.selectedBulkColor {
  display: inline-block;
  position: relative;
  height: 30px;
  width: 30px;
  border-radius: 50%;
  border: 1px solid #ddd;
  margin-right: 5px;
}

.selectedColorName {
  display: inline-block;
  vertical-align: top;
  font-size: 16px;
  color: #000000;
  font-weight: bold;
}

.sizeLabel {
  font-size: 16px;
  font-weight: bold;
  color: #000000;
}

.bulkPrice {
  font-size: 14px;
  color: #253646;
}

.bulkPriceInput {
  text-align: center;
}

.bulkPriceInventory {
  font-size: 14px;
  color: #515E6B;
  font-style: italic;
}

.bulkColor {
  position: relative;
  height: 30px;
  width: 30px;
  border-radius: 50%;
  border: 1px solid #ddd;
  display: block;
}

.bulkColorInput {
  text-align: center;
  height: 40px;
  width: 80px;
}

.active .sizeDetails {
  display: block;
}

.productBtns {
  margin-top: 20px;
}

.btnBuyNow {
  color: #253646;
  background-color: #f9e4a3;
  border: 1px solid #f9e4a3;
}

.bulkToolTip {
  position: absolute;
  bottom: 100%;
  left: 50%;
  transform: translateX(-50%);
  padding: 5px;
  color: #fff;
  background: #333;
  border-radius: 6px;
  white-space: nowrap;
  opacity: 0;
  transition: opacity 0.3s, visibility 0.3s;
}

.colorDisplay:hover .bulkToolTip {
  opacity: 1;
  visibility: visible;
}

.bulkLoading {
  display: block;
  width: 100%;
  text-align: center;
  font-size: 18px;
  color: #000000;
  font-weight: 700;
  padding-bottom: 25px;
}

.productBtns {
  display: flex;
  flex-direction: row;
  flex-wrap: wrap;
  column-gap: 10px;
  margin-bottom: 20px;
}

.productBtns button {
  flex: 1;
  gap: 10px;
  padding: 14.5px 30px 13.5px;
}

@media (max-width:460px) {

  .colorsContainer {
    justify-content: flex-start;
  }

  .colorDisplay:hover .bulkToolTip {
    opacity: 0;
    visibility: hidden;
  }
}
</style>

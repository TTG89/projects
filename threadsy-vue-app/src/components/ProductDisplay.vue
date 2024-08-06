<!-- ProductDisplay.vue -->
<template>
  <div class="product" v-if="product">
    <div class="container">
      <div class="column">
        <ProductImage :images="currentVariantImages" />
        <ProductTabs :productDescription="product.description" :isDescriptionExpanded="isDescriptionExpanded"
          @toggleDescription="toggleDescription" />
        <SocialShare :getSocialShareLink="getSocialShareLink" />
      </div>
      <div class="column">
        <ProductDetails :product="product" :currentVariant="currentVariant" :tierLevel="currentTierLevel"
          :price="currentPrice" :cartTotal="cartTotal" :reviewsAverage="reviewsAverage" :reviewsCount="reviewsCount"
          :starDisplay="starDisplay" :lowestPrice="lowestPrice" :selectedColorText="selectedColorText"
          :productStyle="productStyle" :displayProductTitle="displayProductTitle" />
        <VariantSwatches :variantProducts="variantProducts" :hoveredColor="hoveredColor"
          :moreSwatchesAvailable="moreSwatchesAvailable" :isLoadingMoreSwatches="isLoadingMoreSwatches"
          :initialColor="selectedColorText" @loadProductData="loadProductData" @updateHoveredColor="updateHoveredColor"
          @loadMoreSwatches="handleLoadMoreSwatches" />
        <SizeQuantityChart :currentProduct="currentProduct" :discountedVariantPrices="discountedVariantPrices"
          :showSizeChartModal="showSizeChartModal" @toggleSizeChartModal="toggleSizeChartModal"
          @updateQuantity="updateQuantity" :selectedVariants="selectedVariants" />
        <ProductCartTotal :cartTotal="cartTotal" :itemCount="itemCount" :originalTotal="originalTotal" />
        <ProductButtons :isQuantityEntered="isQuantityEntered" :addingToCart="addingToCart" :isInCart="isInCart"
          :itemCount="itemCount" @handleAddToCart="handleAddToCart" @handleBuyNow="handleBuyNow" />
        <ProductStatusMessages :showSuccessMessage="showSuccessMessage" :successMessage="successMessage"
          :errorMessage="errorMessage" :isQuantityEntered="isQuantityEntered" :attemptedAddToCart="attemptedAddToCart"
          :attemptedBuyNow="attemptedBuyNow" :lastAction="lastAction" />
        <div class="estimatedDelivery">
          <font-awesome-icon :icon="['fas', 'truck']" /> Expected Delivery <span>{{ estimatedDeliveryDate }}</span>
        </div>
        <div class="shopPay">
          <p>Pay in 4 interest-free installments for orders over $50.00 with (need image) <a href="#">Learn more</a></p>
        </div>
        <div class="productBulkOrder">
          <p>
            Buying Multiple Styles?
            <span>Use the bulk ordering tool!</span>
          </p>
          <button @click="toggleBulkOrdering" class="btn btnBulkOrder">
            Bulk Ordering
          </button>
        </div>
        <div class="productShippingReturnsCopy">
          <div id="freeShipping">
            <img id="freeShippingIcon" :src="require('@/assets/icons/shipping-icon.svg')" alt="Free Shipping Icon" />
            <span>Free Shipping Over $50</span>
          </div>
          <div id="freeReturn">
            <img id="freeReturnsIcon" :src="require('@/assets/icons/returns-icon.svg')" alt="Free Returns Icon" />
            <span>Free Returns</span>
          </div>
        </div>
      </div>
    </div>
    <BulkOrderingContainer :showBulkOrdering="showBulkOrdering" :bulkProducts="bulkOrderingProducts" :sizes="sizes"
      :product="product" :handle="product.handle" :tier-level="currentTierLevel" :cart-total="cartTotal"
      :item-count="itemCount" :original-total="originalTotal" @updatecarttotal="updateCartTotal" />
    <div>
      <!-- Currently Unused Related/Bestselling product 
      <related-products></related-products>-->

    </div>
  </div>
  <div v-else>
    <pageLoader :loading="isLoading" />
  </div>
</template>

<script>
import { fetchProductData as fetchProductDataFromAPI } from "@/utils/shopifyGraphQLFetchProduct";
import { addToCart } from "@/utils/shopifyCart";
import { fetchCartTotal } from '@/utils/shopifyFetchCartTotal';
import ProductImage from './productSubComponents/ProductImage.vue';
import ProductTabs from './productSubComponents/ProductTabs.vue';
import SocialShare from './productSubComponents/SocialShare.vue';
import ProductDetails from './productSubComponents/ProductDetails.vue';
import VariantSwatches from './productSubComponents/VariantSwatches.vue';
import SizeQuantityChart from './productSubComponents/SizeQuantityChart.vue';
import ProductButtons from './productSubComponents/ProductButtons.vue';
import ProductStatusMessages from './productSubComponents/ProductStatusMessages.vue';
import BulkOrderingContainer from './productSubComponents/BulkOrderingContainer.vue';
//import RelatedProducts from "./productSubComponents/RelatedProducts.vue";
import ProductCartTotal from './productSubComponents/ProductCartTotal.vue';
import pageLoader from './common/Loader.vue';
import { library } from '@fortawesome/fontawesome-svg-core';
import { fas, faStar as fasFaStar, faStarHalfAlt, faArrowUp, faArrowDown } from '@fortawesome/free-solid-svg-icons';
import { far, faStar as farFaStar } from '@fortawesome/free-regular-svg-icons';
import { fab } from '@fortawesome/free-brands-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

library.add(fab, fas, far, fasFaStar, farFaStar, faStarHalfAlt, faArrowUp, faArrowDown);

export default {
  name: "ProductDisplay",
  components: {
    pageLoader,
    FontAwesomeIcon,
    ProductImage,
    ProductTabs,
    SocialShare,
    ProductDetails,
    VariantSwatches,
    SizeQuantityChart,
    ProductButtons,
    ProductStatusMessages,
    BulkOrderingContainer,
    // RelatedProducts,
    ProductCartTotal
  },
  data() {
    return {
      product: null,
      isDescriptionExpanded: false,
      variantProducts: [],
      discountRules: {
        Tier2: [
          { threshold: 250, discount: 7 },
          { threshold: 110, discount: 5 }
        ],
        Tier3: [
          { threshold: 500, discount: 10 },
          { threshold: 250, discount: 7 },
          { threshold: 110, discount: 5 }
        ],
        Tier4: [
          { threshold: 750, discount: 15 },
          { threshold: 500, discount: 10 },
          { threshold: 250, discount: 7 },
          { threshold: 110, discount: 5 }
        ]
      },
      selectedColorId: null,
      selectedVariant: {},
      currentProduct: null,
      currentVariant: null,
      nextPageInfo: null,
      hoveredColor: '',
      isLoading: false,
      isLoadingMoreProducts: false,
      moreSwatchesAvailable: true,
      quantity: 1,
      addingToCart: false,
      lastAction: "",
      successMessage: "",
      showSuccessMessage: false,
      errorMessage: "",
      isSuccess: false,
      selectedVariants: {},
      selectedColorText: "",
      isLoadingMoreSwatches: false,
      showBulkOrdering: false,
      bulkOrderingLoaded: false,
      bulkOrderingProducts: [],
      sizes: ["XS", "S", "M", "L", "XL", "2XL", "3XL"],
      productDescription: true,
      showSizeChartModal: false,
      reviewsAverage: 0,
      reviewsCount: 0,
      tierLevel: '',
      estimatedDeliveryDays: 4,
      cartTotal: 0,
      originalTotal: 0,
      itemCount: 0,
      attemptedAddToCart: false,
      attemptedBuyNow: false,
    };
  },
  mounted() {
    this.updateCartTotal();
  },
  async created() {
    await this.fetchProductFromURL();
  },
  computed: {
    displayProductTitle() {
      return this.product?.displayProductTitle || '';
    },
    productStyle() {
      return this.product?.productStyle || 'N/A';
    },
    currentTierLevel() {
      return this.currentVariant?.tierLevel || this.product.tierLevel;
    },
    currentPrice() {
      return this.currentVariant?.price || this.lowestPrice;
    },
    filteredVariantProducts() {
      return this.variantProducts.filter(product => {
        return product.variants && product.variants.some(variant => variant.swatchColorCodes);
      });
    },
    currentDiscount() {
      const rules = this.discountRules[this.tierLevel] || [];
      for (let i = 0; i < rules.length; i++) {
        if (this.cartTotal >= rules[i].threshold) {
          return rules[i].discount;
        }
      }
      return 0;
    },
    discountedVariantPrices() {
      if (!this.currentProduct || !this.currentProduct.variants) {
        return [];
      }
      return (this.currentProduct.variants || []).map(variant => {
        const originalPrice = parseFloat(variant.price);
        const discountFactor = 1 - (this.currentDiscount / 100);
        const discountedPrice = originalPrice * discountFactor;
        return {
          ...variant,
          originalPrice: originalPrice.toFixed(2),
          discountedPrice: discountedPrice.toFixed(2)
        };
      });
    },
    isQuantityEntered() {
      return this.hasQuantityEntered();
    },
    lowestPrice() {
      const prices = this.discountedVariantPrices.map(variant => parseFloat(variant.discountedPrice));
      return prices.length ? Math.min(...prices).toFixed(2) : "0.00";
    },
    starDisplay() {
      const stars = [];
      const fullStars = Math.floor(this.reviewsAverage);
      const remainder = this.reviewsAverage % 1;
      const emptyStars = 5 - fullStars - (remainder >= 0.5 ? 1 : 0);

      for (let i = 0; i < fullStars; i++) {
        stars.push(['fas', 'star']);
      }
      if (remainder >= 0.5) {
        stars.push(['fas', 'star-half-alt']);
      }
      for (let i = 0; i < emptyStars; i++) {
        stars.push(['far', 'star']);
      }
      return stars;
    },
    reviewCountText() {
      return this.reviewsCount === 0 ? 'No Reviews' : `${this.reviewsCount} ${this.reviewsCount === 1 ? 'review' : 'reviews'}`;
    },
    estimatedDeliveryDate() {
      let resultDate = new Date();
      resultDate.setDate(resultDate.getDate() + this.estimatedDeliveryDays);
      return resultDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    },
    isInCart() {
      return this.cartTotal > 0;
    },
    currentVariantImages() {
      const variantImages = this.currentVariant?.images || [];
      const productImages = this.currentProduct?.images || [];
      return variantImages.length > 0 ? variantImages : productImages;
    }
  },
  methods: {
    async fetchProductFromURL() {
      const pathSegments = window.location.pathname.split("/");
      const productIndex = pathSegments.findIndex(segment => segment.toLowerCase() === "products");
      if (productIndex !== -1 && pathSegments.length > productIndex + 1) {
        const productHandleSegment = pathSegments[productIndex + 1];
        const fetchedData = await this.fetchProductData(productHandleSegment);
        if (fetchedData) {
          this.product = fetchedData.mainProduct;
          this.variantProducts = fetchedData.variantProducts || [];
          this.nextPageInfo = fetchedData.nextPageInfo;

          // Get color from URL
          const productColorFromURL = this.getProductColorFromURL();
          let initialProduct = null;

          if (productColorFromURL) {
            // Find the product matching the color from the URL
            initialProduct = this.variantProducts.find(product => product.productColor === productColorFromURL);
          }

          if (!initialProduct) {
            // Load the initial product if no specific product color in URL
            initialProduct = this.variantProducts[0];
          }

          if (initialProduct) {
            this.loadProductData(initialProduct, initialProduct.variants[0], false);
          }
        }
      }
    },
    getProductColorFromURL() {
      const urlParams = new URLSearchParams(window.location.search);
      const color = urlParams.get("color");
      return color ? color.replace(/-/g, ' ') : null;
    },
    updateURLForVariant(color) {
      const formattedColor = color.replace(/\s+/g, '-');
      const baseUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
      const newUrl = `${baseUrl}?color=${encodeURIComponent(formattedColor)}`;
      history.pushState({ path: newUrl }, "", newUrl);
    },
    getProductGidFromURL() {
      const urlParams = new URLSearchParams(window.location.search);
      const variantId = urlParams.get("variant");
      return variantId ? `gid://shopify/Product/${variantId}` : null;
    },
    loadProductData(product, variant = null, shouldUpdateURL = true) {
      const selectedVariant = variant || (product.variants && product.variants.length > 0 ? product.variants[0] : null);

      if (selectedVariant && shouldUpdateURL) {
        this.updateURLForVariant(product.productColor);
      }
      this.currentProduct = product;
      this.currentVariant = selectedVariant;
      this.selectedColorText = product.productColor || "Default Color";
      this.reviewsAverage = Number(selectedVariant?.reviewsAverage || product.reviewsAverage);
      this.reviewsCount = Number(selectedVariant?.reviewsCount || product.reviewsCount);
    },
    async fetchProductData(handle, pageInfo = null) {
      return await fetchProductDataFromAPI(handle, pageInfo);
    },
    async handleLoadMoreSwatches() {
      if (!this.nextPageInfo || this.isLoadingMoreSwatches) {
        return;
      }
      this.isLoadingMoreSwatches = true;
      try {
        const handle = this.product.handle;
        const response = await this.fetchProductData(handle, this.nextPageInfo);
        if (response) {
          this.variantProducts = [...this.variantProducts, ...response.variantProducts];
          this.nextPageInfo = response.nextPageInfo;
          this.moreSwatchesAvailable = !!this.nextPageInfo;
        } else {
          console.log('No response received');
        }
      } catch (error) {
        console.error('Error loading more swatches:', error);
      } finally {
        this.isLoadingMoreSwatches = false;
      }
    },
    formatInventoryQuantity(inventory) {
      if (inventory >= 1000) {
        return '999+';
      } else if (inventory < 100) {
        return 'Low stock';
      }
      return inventory.toString();
    },
    updateHoveredColor(colorName) {
      this.hoveredColor = colorName;
    },
    async handleAddToCart() {
      this.lastAction = 'addSingle';
      this.addingToCart = true;
      this.showSuccessMessage = false;
      this.errorMessage = "";
      this.attemptedAddToCart = false;

      if (!this.isQuantityEntered) {
        this.errorMessage = "Please enter a quantity before adding to cart.";
        this.attemptedAddToCart = true;
        setTimeout(() => {
          this.attemptedAddToCart = false;
          this.errorMessage = "";
        }, 5000);
        this.addingToCart = false;
        return;
      }

      let itemsAddedSuccessfully = false;
      for (const [variantId, quantity] of Object.entries(this.selectedVariants)) {
        if (quantity > 0) {
          const result = await addToCart(variantId, quantity);
          if (result) {
            this.successMessage = "Items added to cart successfully!";
            this.showSuccessMessage = true;
            itemsAddedSuccessfully = true;
            await this.updateCartTotal();
          } else {
            this.errorMessage = "Failed to add some items to the cart.";
            break;
          }
        }
      }

      if (!itemsAddedSuccessfully && !this.errorMessage) {
        this.errorMessage = "No items were added to the cart.";
      }

      setTimeout(() => {
        this.showSuccessMessage = false;
        this.successMessage = "";
        this.errorMessage = "";
      }, 2500);

      this.addingToCart = false;
      this.clearQuantities();
    },
    clearQuantities() {
      this.selectedVariants = (this.currentProduct.variants || []).reduce((acc, variant) => {
        acc[variant.id] = 0;
        return acc;
      }, {});
    },
    async updateCartTotal() {
      try {
        const { cartTotalInDollars, itemCount, originalTotalInDollars } = await fetchCartTotal();
        this.cartTotal = cartTotalInDollars;
        this.itemCount = itemCount;
        this.originalTotal = originalTotalInDollars;
      } catch (error) {
        console.error('Error fetching cart total:', error);
      }
    },
    async addToCartAndUpdateTotal(variantId, quantity) {
      const result = await addToCart(variantId, quantity);
      if (result) {
        await this.updateCartTotal();
      }
    },
    hasQuantityEntered() {
      return Object.values(this.selectedVariants).some(quantity => quantity > 0);
    },
    async handleBuyNow() {
      if (this.itemCount > 0 || this.hasQuantityEntered()) {
        this.addingToCart = true;

        try {
          for (const [variantId, quantity] of Object.entries(this.selectedVariants)) {
            if (quantity > 0) {
              await addToCart(variantId, quantity);
            }
          }
          await this.updateCartTotal();
          window.location.href = '/checkout';
        } finally {
          this.addingToCart = false;
        }
      } else {
        this.attemptedBuyNow = true;
        this.errorMessage = "Please enter a quantity before buying.";
        setTimeout(() => {
          this.attemptedBuyNow = false;
          this.errorMessage = "";
        }, 2500);
      }
    },
    updateQuantity(variantId, quantity) {
      this.selectedVariants = { ...this.selectedVariants, [variantId]: quantity };
    },
    scrollToElement(selector) {
      this.$nextTick(() => {
        const element = document.querySelector(selector);
        if (element) {
          element.scrollIntoView({ behavior: "smooth" });
        }
      });
    },
    async toggleBulkOrdering() {
      this.showBulkOrdering = !this.showBulkOrdering;
      if (this.showBulkOrdering) {
        this.scrollToElement(".bulkOrderingContainer");
      }
    },
    toggleSizeChartModal() {
      this.showSizeChartModal = !this.showSizeChartModal;
    },
    toggleDescription() {
      this.isDescriptionExpanded = !this.isDescriptionExpanded;
    },
    getSocialShareLink(network) {
      const currentPageUrl = window.location.href;
      const encodedUrl = encodeURIComponent(currentPageUrl);
      let url = '';

      switch (network) {
        case 'twitter':
          url = `https://twitter.com/intent/tweet?url=${encodedUrl}`;
          break;
        case 'facebook':
          url = `https://www.facebook.com/sharer.php?u=${encodedUrl}`;
          break;
        case 'pinterest':
          url = `https://pinterest.com/pin/create/button/?url=${encodedUrl}`;
          break;
      }
      return url;
    },
  },
};
</script>

<style scoped>
.estimatedDelivery {
  color: #000000;
  margin-bottom: 8px;
  font-size: 16px;
}

.estimatedDelivery span {
  font-weight: bold;
}

.shopPay p {
  font-size: 14px;
  font-weight: bold;
  color: #000;
}

.shopPay a {
  text-decoration: underline;
  font-weight: bold;
}

.bulk-ordering-container {
  margin: 0 50px;
}

.productBulkOrder {
  display: flex;
  justify-content: space-between;
  background-color: #F2F2F2;
  border-top: 1px solid #A8AFB5;
  border-bottom: 1px solid #A8AFB5;
  padding: 25px;
  margin-bottom: 25px;
}

.productBulkOrder p {
  font-size: 16px;
  color: #253646;
  font-weight: bold;
  margin: 0px;
}

.productBulkOrder span {
  display: block;
  font-size: 16px;
  color: #253646;
  font-weight: 400;
  line-height: 1;
}

.btnBulkOrder {
  font-size: 14px;
  color: #253646;
  padding: 15px 48px;
  border: 1px solid #253646;
}

.productShippingReturnsCopy {
  display: flex;
  color: #253646;
}

.productShippingReturnsCopy span {
  font-size: 16px;
  font-weight: bold;
}

#freeShipping:after {
  content: '';
  border-right: 2px solid #253646;
  margin: 0 15px;
}

#freeShippingIcon,
#freeReturnsIcon {
  padding-right: 5px;
  width: 32px;
  height: auto;
  vertical-align: top;
}

@media (max-width: 768px) {
  .productImage {
    width: 300px;
  }

  .swatchContainer {
    margin-bottom: 15px;
  }
}

@media (max-width: 500px) {
  .productBulkOrder {
    display: block;
    text-align: center;
  }

  .productBulkOrder p {
    margin-bottom: 20px;
  }

  .productSwatch:hover .tooltip {
    opacity: 0;
    visibility: hidden;
  }

  .productBtns button {
    flex: none;
    width: 100%;
    margin-bottom: 10px;
  }
}
</style>

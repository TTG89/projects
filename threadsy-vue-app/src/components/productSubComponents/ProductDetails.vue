<!-- ProductDetails.vue -->
<template>
  <div>
    <p class="productVenderStyle">{{ product.vendor }} | Style #: {{ productStyle }} </p>
    <h1>{{ displayProductTitle }}</h1>
    <div class="reviews">
      <font-awesome-icon v-for="(icon, index) in starDisplay" :key="index" :icon="icon" class="star" />
      <span class="reviewAverage">{{ reviewsAverage }}</span>
      <span class="reviewCount">{{ reviewCountText }}</span>
    </div>
    <p class="priceDisclaimer">Pricing may vary by color and size</p>
    <div class="productPrice">
      <p>Starting at <span>${{ lowestPrice }}</span>/unit</p>
    </div>
    <BulkPricing v-if="currentVariant" :tier-level="tierLevel" :price="price" :cart-total="cartTotal" />
    <div v-if="selectedColorText" class="selectedColorText">
      <span>color:</span> {{ selectedColorText }}
    </div>
  </div>
</template>

<script>
import BulkPricing from './BulkPricing.vue';

export default {
  props: {
    product: Object,
    currentVariant: Object,
    tierLevel: String,
    price: String,
    cartTotal: Number,
    reviewsAverage: Number,
    reviewsCount: Number,
    lowestPrice: String,
    selectedColorText: String,
    productStyle: String,
    displayProductTitle: String,
    starDisplay: Array
  },
  components: {
    BulkPricing
  },
  computed: {
    reviewCountText() {
      return this.reviewsCount === 0 ? 'No Reviews' : `${this.reviewsCount} ${this.reviewsCount === 1 ? 'review' : 'reviews'}`;
    },
  },
};
</script>

<style scoped>
h1 {
  color: #253646;
  font-size: 32px;
  font-weight: 800;
  margin-top: 0px;
  margin-bottom: 0px;
  text-transform: uppercase;
}

.productVenderStyle {
  color: #000;
  font-weight: 400;
  font-size: 14px;
  margin-bottom: 0px;
}

.selectedColorText span {
  text-transform: uppercase;
  font-size: 18px;
  color: #000000;
  font-weight: 700;
  margin-bottom: 10px;
}

.reviews {
  font-size: 16px;
  font-weight: 700;
  line-height: 1;
}

.star {
  color: #31708f;
}

.reviewAverage {
  color: #31708f;
  margin-right: 5px;
}

.reviewCount {
  color: #31708f;
}

.productPrice {
  line-height: 1;
  margin-bottom: 25px;
}

.productPrice span {
  color: #000;
  font-size: 26px;
  font-weight: bold;
}

.priceDisclaimer {
  font-size: 8px;
  margin: 0 0 10px 0;
}
</style>

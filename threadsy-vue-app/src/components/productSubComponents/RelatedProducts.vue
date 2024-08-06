<template>
  <div class="related-products">
    <h2>You Might Also Like</h2>
    <div class="product-list">
      <div v-for="product in bestSellingProducts" :key="product.id">
        <p>Style: {{ product.productStyle }}</p>
        <h3>{{ product.title }}</h3>
        <img :src="product.src" :alt="product.altText">
        <p>{{ product.price }}</p>
      </div>
    </div>
  </div>
</template>
  
  <script>
  import { fetchBestSellingProducts } from "@/utils/shopifyFetchBestSellingProducts";
  
  export default {
    name: 'RelatedProducts',
    data() {
      return {
        bestSellingProducts: [],
      };
    },
    methods: {
      async loadBestSellingProducts() {
        this.bestSellingProducts = await fetchBestSellingProducts();
        console.log("Products loaded into Vue component:", this.bestSellingProducts); // Log to confirm data
      }
    },
    mounted() {
      this.loadBestSellingProducts();
    }
  };
  </script>
  
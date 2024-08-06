<template>
  <div id="app">
    <ProductDisplay :handle="productHandle" />
  </div>
</template>

<script>
import ProductDisplay from '../src/components/ProductDisplay.vue';

export default {
  name: 'App',
  components: {
    ProductDisplay
  },
  data() {
    return {
      productHandle: null
    };
  },
  mounted() {
    // Extract the handle from the Shopify URL
    const pathParts = window.location.pathname.split("/");
    this.productHandle = pathParts[pathParts.length - 1];
    //console.log('Extracted productHandle:', this.productHandle); // Log the extracted handle
  }
};
</script>
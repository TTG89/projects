<template>
  <div class="container bulk-ordering-container" v-show="showBulkOrdering">
    <BulkOrdering :bulk-ordering-products="bulkProducts" :sizes="sizes" :product="product" :handle="handle"
      :tier-level="tierLevel" :cart-total="cartTotal" :item-count="itemCount" :original-total="originalTotal"
      @updatecarttotal="updateCartTotal" />
  </div>
</template>

<script>
  import BulkOrdering from './BulkOrdering.vue';

  export default {
    props: {
      showBulkOrdering: Boolean,
      bulkProducts: Array,
      sizes: Array,
      product: Object,
      tierLevel: String,
      cartTotal: Number,
      itemCount: Number,
      originalTotal: Number,
      handle: String,
    },
    components: {
      BulkOrdering,
    },
    methods: {
      updateCartTotal(total) {
        this.$emit('updatecarttotal', total);
      },
      mounted() {
        console.log('BulkOrderingContainer handle:', this.handle); // Add log statement
      },
    },
  };
</script>

<style scoped>
  .bulk-ordering-container {
    margin: 0 50px;
  }
</style>
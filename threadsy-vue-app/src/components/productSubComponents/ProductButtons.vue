<!-- ProductButtons.vue -->
<template>
  <div class="productBtns">
    <button @click="$emit('handleAddToCart')" class="btn btnPrimary">
      <span v-if="addingToCart">Adding...</span>
      <span v-else>Add to Cart</span>
    </button>
    <button @click="$emit('handleBuyNow')" class="btn btnBuyNow">
      <span v-if="itemCount > 0">Checkout</span>
      <span v-else>Buy Now</span>
    </button>
  </div>
</template>

<script>
export default {
  props: {
    isQuantityEntered: Boolean,
    addingToCart: Boolean,
    isInCart: Boolean,
    itemCount: Number,
  },
};
</script>

<style scoped>
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

.btnBuyNow {
  color: #253646;
  background-color: #f9e4a3;
  border: 1px solid #f9e4a3;
}
</style>

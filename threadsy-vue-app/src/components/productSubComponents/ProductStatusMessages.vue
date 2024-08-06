<template>
  <div>
    <div v-if="showSuccessMessage && lastAction === 'addSingle'" class="success-message">
      {{ successMessage }}
    </div>
    <div v-if="!isQuantityEntered && attemptedAddToCart" class="error-message">Please enter a quantity before adding to cart.</div>
    <div v-if="!isQuantityEntered && attemptedBuyNow" class="error-message">Please enter a quantity before buying.</div>
    <div v-if="errorMessage" class="error-message">{{ errorMessage }}</div>
  </div>
</template>

<script>
export default {
  props: {
    showSuccessMessage: Boolean,
    successMessage: String,
    errorMessage: String,
    isQuantityEntered: Boolean,
    attemptedAddToCart: Boolean,
    attemptedBuyNow: Boolean,
    lastAction: String,
  },
};
</script>

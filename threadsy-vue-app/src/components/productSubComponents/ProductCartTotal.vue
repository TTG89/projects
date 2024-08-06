<!-- ProductCartTotal.vue -->
<template>
  <div class="productCarttotal">
    <span id="subTotal">Subtotal</span>
    <span id="itemCount">({{ itemCount }}) items:</span>
    <span id="subTotalAmount">${{ cartTotal.toFixed(2) }}</span>
    <span id="originalTotal" v-if="cartTotal > 110">${{ originalTotal.toFixed(2) }}</span>
  </div>
</template>

<script>
export default {
  props: {
    cartTotal: {
      type: Number,
      required: true
    },
    originalTotal: {
      type: Number,
      required: true
    },
    itemCount: {
      type: Number,
      required: true
    }
  }
};
</script>

<style scoped>
.productCarttotal {
  margin-bottom: 16px;
}
.productCarttotal span {
  display: inline-block;
}
#subTotal {
  font-size: 20px;
  color: #000000;
  margin-right: 3px;
}
#itemCount {
  font-size: 14px;
  color: #3C5370;
  margin-right: 5px;
}
#subTotalAmount {
  font-size: 20px;
  color: #000000;
  font-weight: bold;
  margin-right: 5px;
}
#originalTotal {
  font-size: 14px;
  color: #3C5370;
  text-decoration: line-through;
}
</style>

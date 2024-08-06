<!-- BulkPricing.vue -->
<template>
  <div class="bulk-pricing">
    <div v-if="tierLevel === 'Tier2'">
      <table class="buyInBulkBlockTable">
        <thead>
          <tr>
            <th colspan="3"><span class="BuyInBulkSave">Buy More Save More!</span></th>
          </tr>
        </thead>
        <tbody>
          <tr class="buyInBulkBlockTR">
            <td class="buyInBulkBlockTD">
              <div class="buyInBulkBlockTDInner">
                <span>Everyday</span>
                <span class="buyInBulkPrice">${{ numericPrice }}</span>
              </div>
            </td>
            <td class="buyInBulkBlockTD" :class="{ 'highlight': highlightTier110 && !highlightTier250 }">
              <div class="buyInBulkBlockTDInner">
                <span>$110+</span>
                <span class="buyInBulkPrice">${{ calculateDiscountedPrice(5) }}</span>
              </div>
            </td>
            <td class="buyInBulkBlockTD" :class="{ 'highlight': highlightTier250 }">
              <div class="buyInBulkBlockTDInner">
                <span>$250+</span>
                <span class="buyInBulkPrice">${{ calculateDiscountedPrice(7) }}</span>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-else-if="tierLevel === 'Tier3'">
      <table class="buyInBulkBlockTable">
        <thead>
          <tr>
            <th colspan="4"><span class="BuyInBulkSave">Buy More Save More!</span></th>
          </tr>
        </thead>
        <tbody>
          <tr class="buyInBulkBlockTR">
            <td class="buyInBulkBlockTD">
              <div class="buyInBulkBlockTDInner">
                <span>Everyday</span>
                <span class="buyInBulkPrice">${{ numericPrice }}</span>
              </div>
            </td>
            <td class="buyInBulkBlockTD" :class="{ 'highlight': highlightTier110 && !highlightTier250 }">
              <div class="buyInBulkBlockTDInner">
                <span>$110+</span>
                <span class="buyInBulkPrice">${{ calculateDiscountedPrice(5) }}</span>
              </div>
            </td>
            <td class="buyInBulkBlockTD" :class="{ 'highlight': highlightTier250 && !highlightTier500 }">
              <div class="buyInBulkBlockTDInner">
                <span>$250+</span>
                <span class="buyInBulkPrice">${{ calculateDiscountedPrice(7) }}</span>
              </div>
            </td>
            <td class="buyInBulkBlockTD" :class="{ 'highlight': highlightTier500 }">
              <div class="buyInBulkBlockTDInner">
                <span>$500+</span>
                <span class="buyInBulkPrice">${{ calculateDiscountedPrice(10) }}</span>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-else-if="tierLevel === 'Tier4'">
      <table class="buyInBulkBlockTable">
        <thead>
          <tr>
            <th colspan="5"><span class="BuyInBulkSave">Buy More Save More!</span></th>
          </tr>
        </thead>
        <tbody>
          <tr class="buyInBulkBlockTR">
            <td class="buyInBulkBlockTD">
              <div class="buyInBulkBlockTDInner">
                <span>Everyday</span>
                <span class="buyInBulkPrice">${{ numericPrice }}</span>
              </div>
            </td>
            <td class="buyInBulkBlockTD" :class="{ 'highlight': highlightTier110 && !highlightTier250 }">
              <div class="buyInBulkBlockTDInner">
                <span>$110+</span>
                <span class="buyInBulkPrice">${{ calculateDiscountedPrice(5) }}</span>
              </div>
            </td>
            <td class="buyInBulkBlockTD" :class="{ 'highlight': highlightTier250 && !highlightTier500 }">
              <div class="buyInBulkBlockTDInner">
                <span>$250+</span>
                <span class="buyInBulkPrice">${{ calculateDiscountedPrice(7) }}</span>
              </div>
            </td>
            <td class="buyInBulkBlockTD" :class="{ 'highlight': highlightTier500 && !highlightTier750 }">
              <div class="buyInBulkBlockTDInner">
                <span>$500+</span>
                <span class="buyInBulkPrice">${{ calculateDiscountedPrice(10) }}</span>
              </div>
            </td>
            <td class="buyInBulkBlockTD" :class="{ 'highlight': highlightTier750 }">
              <div class="buyInBulkBlockTDInner">
                <span>$750+</span>
                <span class="buyInBulkPrice">${{ calculateDiscountedPrice(15) }}</span>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-else>
      <p>Please contact sales for bulk pricing information.</p>
    </div>
    <div class="buyInBulkPriceDisclaimer">
      <p>Pricing may vary by color and size</p>
    </div>
  </div>
</template>

<script>
  export default {
    name: 'BulkPricing',
    props: {
      tierLevel: String,
      price: String,
      cartTotal: Number
    },
    computed: {
      numericPrice() {
        const parsedPrice = parseFloat(this.price);
        return isNaN(parsedPrice) ? 0 : parsedPrice;
      },
      highlightTier110() {
        return this.cartTotal >= 110 && this.cartTotal < 250;
      },
      highlightTier250() {
        return this.cartTotal >= 250 && this.cartTotal < 500;
      },
      highlightTier500() {
        return this.cartTotal >= 500 && this.cartTotal < 750;
      },
      highlightTier750() {
        return this.cartTotal >= 750;
      }
    },
    methods: {
      calculateDiscountedPrice(discountPercentage) {
        const discountFactor = 1 - (discountPercentage / 100);
        return (this.numericPrice * discountFactor).toFixed(2);
      }
    }
  };
</script>

  
  <style scoped>
  .buyInBulkBlockTable {
    border-collapse: collapse;
    width: 100%;
  }
  
  .buyInBulkBlockTable thead tr th {
    text-align: center;
    background-color: #253646;
    border: 1px solid #000000b3;
    text-transform: uppercase;
    font-weight: 500;
    padding: 5px 10px;
    font-size: 14px;
    color: #fff;
  }
  
  .buyInBulkBlockTable td {
    border: 1px solid #000000b3;
    text-align: center;
    padding: 5px 15px;
    line-height: 1.5;
  }

  
  .buyInBulkBlockTDInner span {
    display: block;
  }

  .BuyInBulkSave {
    font-size: 16px;
    font-weight: bold;
  }
  
  .buyInBulkBlockTDInner span:first-child {
    font-size: 16px;
    color: #515E6B;
    font-weight: 400;
  }
  
  td.buyInBulkBlockTD.highlight {
    position: relative;
    border-left: 2px solid #1d624e;
    border-right: 2px solid #1d624e;
    border-bottom: 5px solid #1d624e;
    box-sizing: border-box;
  }
  
  td.buyInBulkBlockTD.highlight::after {
    content: '';
    position: absolute;
    bottom: 0px;
    left: 50%;
    margin-left: -4px;
    border-width: 4px;
    border-style: solid;
    border-color: transparent transparent #1d624e transparent;
  }
  
  .buyInBulkPrice {
    font-size: 20px;
    color: #515E6B;
    font-weight: 700;
  }
  
  .buyInBulkPriceDisclaimer {
    margin-bottom: 24px;
    padding-bottom: 15px;
    border-bottom: 1px solid #DCDFE1;
  }
  
  .buyInBulkPriceDisclaimer p {
    text-align: center;
    font-size: 14px;
    font-style: italic;
    color: #515E6B;
    margin-top: 8px;
    margin-bottom: 0px;
  }
  </style>
  
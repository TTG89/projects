<!-- ProductTabs.vue -->
<template>
  <div class="productTabs">
    <div class="tabHeader" @click="$emit('toggleDescription')">
      <span>Description</span>
      <font-awesome-icon class="tabIcon" :icon="isDescriptionExpanded ? 'chevron-up' : 'chevron-down'" />
    </div>
    <div class="productDescription" v-show="!isDescriptionExpanded">
      {{ productDescription }}
    </div>
  </div>
</template>

<script>
export default {
  props: {
    productDescription: {
      type: String,
      required: true
    },
    isDescriptionExpanded: {
      type: Boolean,
      required: true
    }
  }
};
</script>

<style scoped>
.productTabs {
  padding: 24px 0;
  border-top: 1px solid #DCDFE1;
  border-bottom: 1px solid #DCDFE1;
  margin-bottom: 20px;
}

.tabHeader {
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  font-size: 16px;
  color: #000000;
  font-style: normal;
  font-weight: 700;
  line-height: 16px;
  text-transform: uppercase;
}

.tabIcon {
  color: #000;
}

.productDescription {
  padding-top: 10px;
  font-size: 14px;
  color: #333;
}
</style>

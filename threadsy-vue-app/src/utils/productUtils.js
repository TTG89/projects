// src/utils/productUtils.js
export function getMetafieldValue(metafields, key) {
  if (!Array.isArray(metafields) || !metafields) {
    return '';
  }
  const metafield = metafields.find(m => m.key === key);
  return metafield ? metafield.value : '';
}
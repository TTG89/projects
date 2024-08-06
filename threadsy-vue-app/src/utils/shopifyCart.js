// src/utils/shopifyCart.js
export async function addToCart(variantId, quantity) {
  variantId = String(variantId);

  let decodedId;
  if (variantId.includes("gid://")) {
    const matches = variantId.match(/ProductVariant\/(\d+)/);
    if (matches && matches[1]) {
      decodedId = matches[1];
    } else {
      return false;
    }
  } else {
    decodedId = variantId;
  }
  const formData = new FormData();
  formData.append("id", decodedId);
  formData.append("quantity", quantity);

  try {
    const response = await fetch("/cart/add.js", {
      method: "POST",
      body: formData,
    });
    if (response.ok) {
      return true;
    } else {
      const errorData = await response.text();
      console.error("Failed to add item to cart", errorData);
      return false;
    }
  } catch (error) {
    console.error("Error adding item to cart:", error);
    return false;
  }
}

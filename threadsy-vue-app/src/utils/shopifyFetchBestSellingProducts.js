// utils/shopifyFetchBestSellingProducts.js
const ngrokURL = "https://localthreadsy.ngrok.app";

const fetchBestSellingProducts = async () => {
  try {
    const url = `${ngrokURL}/api/best-selling-products`;
    const response = await fetch(url);
    if (!response.ok) {
      throw new Error(`HTTP error! Status: ${response.status}`);
    }
    const data = await response.json();
    return data.data.products.edges.map(edge => ({
      id: edge.node.id,
      title: edge.node.title,
      src: edge.node.images.edges[0]?.node.src, // Safely access the image src
      altText: edge.node.images.edges[0]?.node.altText || '', // Provide default for altText
      price: edge.node.priceRange.minVariantPrice.amount,
      currencyCode: edge.node.priceRange.minVariantPrice.currencyCode,
      productStyle: edge.node.metafield ? edge.node.metafield.value : 'Default Style' // Use 'Default Style' if metafield is null
    }));
  } catch (error) {
    console.error("Failed to fetch best selling products:", error);
    return [];  // Return an empty array to avoid further errors in the application
  }
}

export { fetchBestSellingProducts };

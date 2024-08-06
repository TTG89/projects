// utils/shopifyGraphqlFetchBulk.js
import axios from 'axios';

const ngrokURL = "https://localthreadsy.ngrok.app";

const fetchBulkOrderProducts = async (handle, pageInfo = null) => {
  try {
    let url = `${ngrokURL}/api/bulk-variant-products/${handle}`;
    if (pageInfo) {
      const encodedPageInfo = encodeURIComponent(pageInfo);
      url += `?page_info=${encodedPageInfo}`;
    }

    console.log('Fetching bulk order products with URL:', url); // Log the URL being fetched

    const response = await axios.get(url, {
      headers: {
        'Content-Type': 'application/json',
      },
    });

    const data = response.data;
    console.log('Response data:', data); // Log the response data

    if (data && data.data && data.data.collection && data.data.collection.products) {
      const products = data.data.collection.products.edges.map(edge => ({
        id: edge.node.id,
        title: edge.node.title,
        handle: edge.node.handle,
        vendor: edge.node.vendor,
        productColor: edge.node.productcolor ? edge.node.productcolor.value : 'Unknown Color',
        productStyle: edge.node.productstyle ? edge.node.productstyle.value : 'Default Style',
        reviewsAverage: edge.node.reviews_average ? edge.node.reviews_average.value : '0',
        reviewsCount: edge.node.reviews_count ? edge.node.reviews_count.value : '0',
        swatchColorCodes: edge.node.swatchcolorcodes ? edge.node.swatchcolorcodes.value : '000',
        tierLevel: edge.node.tierLevel ? edge.node.tierLevel.value : 'Unknown',
        images: edge.node.images.edges.map(imageEdge => imageEdge.node),
        variants: edge.node.variants.edges.map(variantEdge => ({
          id: variantEdge.node.id,
          title: variantEdge.node.title,
          price: variantEdge.node.price,
          inventoryQuantity: variantEdge.node.inventoryQuantity,
        }))
      }));

      const nextPageInfo = data.data.collection.products.pageInfo.endCursor || null;
      console.log('Next page info:', nextPageInfo); // Log the next page info

      return { products, nextPageInfo };
    }

    return null;
  } catch (error) {
    console.error('Fetch error:', error.message);
    console.error('Response status:', error.response?.status);
    console.error('Response data:', error.response?.data);
    return null;
  }
};

export { fetchBulkOrderProducts };

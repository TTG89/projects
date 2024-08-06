// utils/shopifyGraphqlFetchProduct.js
import axios from 'axios';

const ngrokURL = "https://localthreadsy.ngrok.app";

const fetchProductData = async (handle, pageInfo = null) => {
  try {
    const url = pageInfo
      ? `${ngrokURL}/api/variant-product/${handle}?page_info=${pageInfo}`
      : `${ngrokURL}/api/variant-product/${handle}`;
    
    const response = await axios.get(url, {
      headers: {
        'Content-Type': 'application/json',
      },
    });

    const data = response.data;

    if (data && data.mainProduct) {

      const customMetafields = data.mainProduct.customMetafields || [];
      const yotpoMetafields = data.mainProduct.yotpoMetafields || [];
      const variants = data.mainProduct.variants || [];
      const variantProducts = data.variantProducts || [];

      const result = {
        mainProduct: {
          id: data.mainProduct.id,
          title: data.mainProduct.title,
          handle: data.mainProduct.handle,
          vendor: data.mainProduct.vendor,
          description: data.mainProduct.description,
          displayProductTitle: data.mainProduct.displayProductTitle,
          productStyle: data.mainProduct.productStyle,
          tierLevel: data.mainProduct.tierLevel,
          reviewsAverage: data.mainProduct.reviewsAverage,
          reviewsCount: data.mainProduct.reviewsCount,
          customMetafields: customMetafields,
          yotpoMetafields: yotpoMetafields,
          variants: variants,
          images: data.mainProduct.images || []
        },
        variantProducts: variantProducts.map(product => ({
          ...product,
          variants: product.variants.map(variant => ({
            ...variant,
            productColor: variant.productColor,
            productStyle: variant.productStyle,
            swatchColorCodes: variant.swatchColorCodes,
            tierLevel: variant.tierLevel,
            reviewsAverage: variant.reviewsAverage,
            reviewsCount: variant.reviewsCount,
          })),
        })),
        nextPageInfo: data.nextPageInfo || null,
      };
      //console.log('Processed product and collection products data:', result);
      return result;
    }

    return null;
  } catch (error) {
    console.error('Fetch error:', error.message);
    console.error('Response status:', error.response?.status);
    console.error('Response data:', error.response?.data);
    return null;
  }
};

export { fetchProductData };

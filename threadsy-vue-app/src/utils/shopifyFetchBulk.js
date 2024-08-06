// utils/shopifyFetchBulk.js

const ngrokURL = "https://localthreadsy.ngrok.app";
const fetchBulkOrderProducts = async (productHandle, pageInfo = null) => {
  try {
    let url = `${ngrokURL}/api/bulk-variant-products/${productHandle}`;
    if (pageInfo) {
      url += `?page_info=${pageInfo}`; 
    }
    
    const response = await fetch(url);
    if (!response.ok) {
      return null; 
    }
    const bulkOrderData = await response.json();
    return bulkOrderData; 
  } catch (error) {  
    return null; 
  }
};

export { fetchBulkOrderProducts };

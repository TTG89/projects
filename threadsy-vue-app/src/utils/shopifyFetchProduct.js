// utils/shopifyFetchProduct.js

const ngrokURL = "https://localthreadsy.ngrok.app";
const fetchProductData = async (handle, pageInfo = null) => {
  try {
    let url = `${ngrokURL}/api/variant-product/${handle}`;
    if (pageInfo) {
      url += `?page_info=${pageInfo}`; 
    }
    
    const response = await fetch(url);
    if (!response.ok) {
      console.error("Server responded with an error:", response.status, response.statusText);
      return null;
    }

    const jsonData = await response.json(); 
    
    console.log("Fetched main and Variant product data:", jsonData);
    return jsonData;
  } catch (error) {
    console.error("Fetch error:", error.message);
    return null;
  }
};

export { fetchProductData };

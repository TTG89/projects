// utils/shopifyFetchCartTotal.js

import axios from 'axios';

const baseURL = `https://${process.env.VUE_APP_SHOPIFY_STORE_DOMAIN}`;

export const fetchCartTotal = async () => {
  try {
    const response = await axios.get(`${baseURL}/cart.js`);
    const cartTotalInDollars = response.data.total_price / 100;
    const itemCount = response.data.item_count;
    const originalTotalInDollars = response.data.items.reduce((total, item) => {
      return total + (item.original_price * item.quantity) / 100;
    }, 0);

    return { 
      cartTotalInDollars, 
      itemCount, 
      originalTotalInDollars 
    };
  } catch (error) {
    if (error.response) {
      console.log(error.response.data);
      console.log(error.response.status);
      console.log(error.response.headers);
    } else if (error.request) {
      console.log(error.request);
    } else {
      console.log('Error', error.message);
    }
    throw error;
  }
};

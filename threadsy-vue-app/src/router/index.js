// src/router/index.js

import { createRouter, createWebHistory } from 'vue-router';
import ProductDisplay from '@/components/ProductDisplay.vue';

const routes = [
  {
    path: '/products/:handle',
    name: 'Product',
    component: ProductDisplay,
  },
];

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes,
});

export default router;

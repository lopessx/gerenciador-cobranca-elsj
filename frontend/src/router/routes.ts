import type { RouteRecordRaw } from 'vue-router';

const routes: RouteRecordRaw[] = [
  {
    path: '/login',
    component: () => import('@/pages/LoginPage.vue'),
  },
  {
    path: '/',
    component: () => import('@/layouts/MainLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      { path: '', component: () => import('@/pages/IndexPage.vue') },
      { path: 'users', component: () => import('@/pages/users/UsersPage.vue') },
      { path: 'payment-methods', component: () => import('@/pages/payment-methods/PaymentMethodsPage.vue') },
      { path: 'orders', component: () => import('@/pages/orders/OrdersPage.vue') },
    ],
  },

  {
    path: '/:catchAll(.*)*',
    component: () => import('@/pages/ErrorNotFound.vue'),
  },
];

export default routes;

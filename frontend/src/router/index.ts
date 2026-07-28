import { defineRouter } from '#q-app';
import {
  createMemoryHistory,
  createRouter,
  createWebHashHistory,
  createWebHistory,
} from 'vue-router';

import routes from './routes';

export default defineRouter((/* { store, ssrContext } */) => {
  const createHistory = import.meta.env.QUASAR_SERVER
    ? createMemoryHistory
    : import.meta.env.QUASAR_VUE_ROUTER_MODE === 'history'
      ? createWebHistory
      : createWebHashHistory;

  const Router = createRouter({
    scrollBehavior: () => ({ left: 0, top: 0 }),
    routes,
    history: createHistory(import.meta.env.QUASAR_VUE_ROUTER_BASE),
  });

  Router.beforeEach(async (to) => {
    const requiresAuth = to.matched.some((record) => record.meta.requiresAuth);

    if (requiresAuth) {
      try {
        const { data } = await import('@/services/api').then((m) => m.default.get('/me'));
        if (to.path === '/login' && data) {
          return '/';
        }
      } catch {
        return '/login';
      }
    }

    if (to.path === '/login') {
      try {
        const { data } = await import('@/services/api').then((m) => m.default.get('/me'));
        if (data) {
          return '/';
        }
      } catch {
        // Permite ficar na tela de login
      }
    }
  });

  return Router;
});

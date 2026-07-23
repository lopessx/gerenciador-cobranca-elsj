import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import api from '@/services/api';
import type { User } from '@/types';

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(localStorage.getItem('jwt_token'));
  const user = ref<User | null>(null);

  const isAuthenticated = computed(() => !!token.value);

  function setToken(newToken: string) {
    token.value = newToken;
    localStorage.setItem('jwt_token', newToken);
  }

  function clearAuth() {
    token.value = null;
    user.value = null;
    localStorage.removeItem('jwt_token');
  }

  async function login(email: string, password: string) {
    const response = await api.post('/login', { email, password });
    setToken(response.data.token);
    await fetchUser();
    return response.data;
  }

  async function fetchUser() {
    try {
      const response = await api.get('/me');
      user.value = response.data;
      return user.value;
    } catch {
      clearAuth();
      return null;
    }
  }

  function logout() {
    clearAuth();
  }

  return {
    token,
    user,
    isAuthenticated,
    setToken,
    clearAuth,
    login,
    fetchUser,
    logout,
  };
});

import api from './api';
import type { User } from '@/types';

export const userService = {
  list: () => api.get<User[]>('/users').then((r) => r.data),
  get: (id: number) => api.get<User>(`/users/${id}`).then((r) => r.data),
  create: (data: Omit<User, 'user_id'>) => api.post<User>('/users', data).then((r) => r.data),
  update: (id: number, data: Partial<Omit<User, 'user_id'>>) =>
    api.put<User>(`/users/${id}`, data).then((r) => r.data),
  remove: (id: number) => api.delete(`/users/${id}`),
};

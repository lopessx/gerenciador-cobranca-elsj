import api from './api';
import type { Order } from '@/types';

export const orderService = {
  list: () => api.get<Order[]>('/orders').then((r) => r.data),
  get: (id: number) => api.get<Order>(`/orders/${id}`).then((r) => r.data),
  create: (data: Omit<Order, 'order_id'>) => api.post<Order>('/orders', data).then((r) => r.data),
  update: (id: number, data: Partial<Omit<Order, 'order_id'>>) =>
    api.put<Order>(`/orders/${id}`, data).then((r) => r.data),
  remove: (id: number) => api.delete(`/orders/${id}`),
};

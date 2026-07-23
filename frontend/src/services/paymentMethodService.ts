import api from './api';
import type { PaymentMethod } from '@/types';

export const paymentMethodService = {
  list: () => api.get<PaymentMethod[]>('/payment-methods').then((r) => r.data),
  get: (id: number) => api.get<PaymentMethod>(`/payment-methods/${id}`).then((r) => r.data),
  create: (data: Omit<PaymentMethod, 'paymentmethod_id'>) =>
    api.post<PaymentMethod>('/payment-methods', data).then((r) => r.data),
  update: (id: number, data: Partial<Omit<PaymentMethod, 'paymentmethod_id'>>) =>
    api.put<PaymentMethod>(`/payment-methods/${id}`, data).then((r) => r.data),
  remove: (id: number) => api.delete(`/payment-methods/${id}`),
};

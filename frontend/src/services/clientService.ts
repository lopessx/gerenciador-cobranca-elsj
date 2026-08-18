import api from './api';
import type { Client } from '@/types';

export const clientService = {
  list: () => api.get<Client[]>('/clients').then((r) => r.data),
  search: (query: string) =>
    api.get<Client[]>('/clients', { params: { q: query } }).then((r) => r.data),
  get: (id: number) => api.get<Client>(`/clients/${id}`).then((r) => r.data),
  create: (data: Omit<Client, 'client_id'>) =>
    api.post<Client>('/clients', data).then((r) => r.data),
  update: (id: number, data: Partial<Omit<Client, 'client_id'>>) =>
    api.put<Client>(`/clients/${id}`, data).then((r) => r.data),
  remove: (id: number) => api.delete(`/clients/${id}`),
};

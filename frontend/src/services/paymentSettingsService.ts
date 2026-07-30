import api from './api';
import type { PaymentSettingsResponse, PaymentSettingsSavePayload } from '@/types';

export const paymentSettingsService = {
  async list(): Promise<PaymentSettingsResponse> {
    const response = await api.get<PaymentSettingsResponse>('/payment-settings');
    return response.data;
  },

  async save(payload: PaymentSettingsSavePayload): Promise<void> {
    await api.post('/payment-settings', payload);
  },
};
export interface User {
  user_id: number;
  email: string;
  name: string;
  cpf: string | null;
  role: 'admin' | 'operator';
}

export interface Client {
  client_id: number;
  name: string;
  cpf: string;
  email: string | null;
  phone: string | null;
  address: string | null;
}

export interface Order {
  order_id: number;
  amount: string;
  installments: number;
  payment_method: string;
  client_id: number;
  client_name: string;
  client_cpf: string;
}

export interface PaymentGatewaySchema {
  name: string;
  label: string;
  fields: PaymentGatewayField[];
  supports_installments: boolean;
  payment_methods: string[];
}

export interface PaymentGatewayField {
  name: string;
  label: string;
  type: 'text' | 'password' | 'number' | 'checkbox' | 'select';
  required: boolean;
  options?: { label: string; value: string }[];
}

export interface PaymentSettingValue {
  id: number;
  payment_gateway: string;
  option_name: string;
  value: string | null;
}

export interface PaymentSettingsResponse {
  gateways: PaymentGatewaySchema[];
  settings: Record<string, Record<string, PaymentSettingValue>>;
}

export interface PaymentSettingsSavePayload {
  settings: Record<string, Record<string, string>>;
}
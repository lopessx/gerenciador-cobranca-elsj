export interface User {
  user_id: number;
  email: string;
  name: string;
  cpf: string | null;
  role: 'admin' | 'operator';
}

export interface PaymentMethod {
  paymentmethod_id: number;
  name: string;
  api_key: string;
  secret: string;
}

export interface Order {
  order_id: number;
  amount: string;
  installments: number;
  payment_method_id: number;
  user_id: number;
}

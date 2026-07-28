export interface User {
  user_id: number;
  email: string;
  name: string;
  cpf: string | null;
  role: 'admin' | 'operator';
}

export interface Order {
  order_id: number;
  amount: string;
  installments: number;
  payment_method: string;
  user_id: number;
}

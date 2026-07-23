<template>
  <q-page padding>
    <div class="row items-center q-mb-md">
      <div class="text-h6 col">Pedidos</div>
      <q-btn color="primary" icon="add" label="Novo" @click="openDialog()" />
    </div>

    <q-table
      :rows="orders"
      :columns="columns"
      row-key="order_id"
      :loading="loading"
      flat
      bordered
    >
      <template v-slot:body-cell-actions="props">
        <q-td :props="props" class="q-gutter-sm">
          <q-btn flat round color="primary" icon="edit" size="sm" @click="openDialog(props.row)">
            <q-tooltip>Editar</q-tooltip>
          </q-btn>
          <q-btn flat round color="negative" icon="delete" size="sm" @click="confirmDelete(props.row)">
            <q-tooltip>Excluir</q-tooltip>
          </q-btn>
        </q-td>
      </template>
    </q-table>

    <q-dialog v-model="dialogVisible" persistent>
      <q-card style="width: 500px; max-width: 90vw">
        <q-card-section class="row items-center">
          <div class="text-h6">{{ editing ? 'Editar Pedido' : 'Novo Pedido' }}</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section>
          <q-form @submit="saveOrder" class="q-gutter-md">
            <q-input
              v-model="form.amount"
              label="Valor"
              type="number"
              step="0.01"
              outlined
              :rules="[val => !!val || 'Valor é obrigatório']"
            />
            <q-input
              v-model.number="form.installments"
              label="Parcelas"
              type="number"
              outlined
              :rules="[val => val > 0 || 'Parcelas deve ser maior que 0']"
            />
            <q-select
              v-model="form.payment_method_id"
              label="Método de Pagamento"
              :options="paymentMethodOptions"
              option-value="paymentmethod_id"
              option-label="name"
              outlined
              emit-value
              map-options
              :rules="[val => !!val || 'Método de pagamento é obrigatório']"
            />
            <q-select
              v-model="form.user_id"
              label="Usuário"
              :options="userOptions"
              option-value="user_id"
              option-label="name"
              outlined
              emit-value
              map-options
              :rules="[val => !!val || 'Usuário é obrigatório']"
            />

            <q-card-actions align="right" class="q-px-none">
              <q-btn flat label="Cancelar" color="grey" v-close-popup />
              <q-btn type="submit" label="Salvar" color="primary" :loading="saving" />
            </q-card-actions>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useQuasar } from 'quasar';
import { orderService } from '@/services/orderService';
import { paymentMethodService } from '@/services/paymentMethodService';
import { userService } from '@/services/userService';
import type { Order, PaymentMethod, User } from '@/types';

const $q = useQuasar();

const orders = ref<Order[]>([]);
const paymentMethodOptions = ref<PaymentMethod[]>([]);
const userOptions = ref<User[]>([]);
const loading = ref(false);
const dialogVisible = ref(false);
const saving = ref(false);
const editing = ref(false);

const form = ref({
  order_id: 0,
  amount: '',
  installments: 1,
  payment_method_id: 0,
  user_id: 0,
});

const columns: { name: string; label: string; field: string; sortable?: boolean; align: 'left' | 'right' | 'center' }[] = [
  { name: 'order_id', label: 'ID', field: 'order_id', sortable: true, align: 'left' },
  { name: 'amount', label: 'Valor', field: 'amount', sortable: true, align: 'left' },
  { name: 'installments', label: 'Parcelas', field: 'installments', sortable: true, align: 'left' },
  { name: 'payment_method_id', label: 'Método de Pagamento', field: 'payment_method_id', sortable: true, align: 'left' },
  { name: 'user_id', label: 'Usuário', field: 'user_id', sortable: true, align: 'left' },
  { name: 'actions', label: 'Ações', field: 'actions', align: 'center' },
];

async function loadOrders() {
  loading.value = true;
  try {
    orders.value = await orderService.list();
  } catch {
    $q.notify({ type: 'negative', message: 'Erro ao carregar pedidos' });
  } finally {
    loading.value = false;
  }
}

async function loadOptions() {
  try {
    const [methods, users] = await Promise.all([
      paymentMethodService.list(),
      userService.list(),
    ]);
    paymentMethodOptions.value = methods;
    userOptions.value = users;
  } catch {
    $q.notify({ type: 'negative', message: 'Erro ao carregar opções' });
  }
}

function openDialog(order?: Order) {
  if (order) {
    editing.value = true;
    form.value = { ...order };
  } else {
    editing.value = false;
    form.value = {
      order_id: 0,
      amount: '',
      installments: 1,
      payment_method_id: 0,
      user_id: 0,
    };
  }
  dialogVisible.value = true;
}

async function saveOrder() {
  saving.value = true;
  try {
    const data = { ...form.value };
    if (editing.value) {
      const { order_id, ...updateData } = data;
      await orderService.update(order_id, updateData);
      $q.notify({ type: 'positive', message: 'Pedido atualizado com sucesso!' });
    } else {
      await orderService.create(data);
      $q.notify({ type: 'positive', message: 'Pedido criado com sucesso!' });
    }
    dialogVisible.value = false;
    await loadOrders();
  } catch {
    $q.notify({ type: 'negative', message: 'Erro ao salvar pedido' });
  } finally {
    saving.value = false;
  }
}

function confirmDelete(order: Order) {
  $q.dialog({
    title: 'Confirmar exclusão',
    message: `Deseja excluir o pedido #${order.order_id}?`,
    cancel: true,
    persistent: true,
  }).onOk(async () => {
    try {
      await orderService.remove(order.order_id);
      $q.notify({ type: 'positive', message: 'Pedido excluído com sucesso!' });
      await loadOrders();
    } catch {
      $q.notify({ type: 'negative', message: 'Erro ao excluir pedido' });
    }
  });
}

onMounted(async () => {
  await loadOptions();
  await loadOrders();
});
</script>

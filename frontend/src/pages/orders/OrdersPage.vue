<template>
  <q-page padding>
    <div class="row items-center q-mb-md">
      <div class="text-h6 col">Pedidos</div>
      <q-btn color="primary" icon="add" label="Novo" @click="$router.push('/orders/new')" />
    </div>

    <q-table
      :rows="orders"
      :columns="columns"
      no-data-label="Nenhum pedido criado ainda"
      no-results-label="Pesquisa não encontrou nenhum resultado"
      row-key="order_id"
      :loading="loading"
      flat
      bordered
    >
      <template v-slot:body-cell-actions="props">
        <q-td :props="props" class="q-gutter-sm">
          <q-btn
            flat
            round
            color="primary"
            icon="edit"
            size="sm"
            @click="$router.push(`/orders/${props.row.order_id}/edit`)"
          >
            <q-tooltip>Editar</q-tooltip>
          </q-btn>
          <q-btn
            flat
            round
            color="negative"
            icon="delete"
            size="sm"
            @click="confirmDelete(props.row)"
          >
            <q-tooltip>Excluir</q-tooltip>
          </q-btn>
        </q-td>
      </template>
    </q-table>
  </q-page>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useQuasar } from 'quasar';
import { orderService } from '@/services/orderService';
import type { Order } from '@/types';
import type { ExecException } from 'child_process';

const $q = useQuasar();

const orders = ref<Order[]>([]);
const loading = ref(false);

const columns = [
  { name: 'order_id', label: 'ID', field: 'order_id', sortable: true, align: 'left' as const },
  { name: 'client_name', label: 'Paciente', field: 'client_name', sortable: true, align: 'left' as const },
  { name: 'client_cpf', label: 'CPF', field: 'client_cpf', sortable: true, align: 'left' as const },
  { name: 'amount', label: 'Valor', field: 'amount', sortable: true, align: 'left' as const },
  { name: 'installments', label: 'Parcelas', field: 'installments', sortable: true, align: 'left' as const },
  { name: 'payment_method', label: 'Método de Pagamento', field: 'payment_method', sortable: true, align: 'left' as const },
  { name: 'actions', label: 'Ações', field: 'actions', align: 'center' as const },
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

function confirmDelete(order: Order) {
  $q.dialog({
    title: 'Confirmar exclusão',
    message: `Deseja excluir o pedido #${order.order_id}?`,
    cancel: true,
    persistent: true,
  }).onOk(() => {
    void (async () => {
      try {
        await orderService.remove(order.order_id);
        $q.notify({ type: 'positive', message: 'Pedido excluído com sucesso!' });
        await loadOrders();
      } catch {
        $q.notify({ type: 'negative', message: 'Erro ao excluir pedido' });
      }
    })();
  });
}

onMounted(() => {
  loadOrders().catch((error: ExecException) => {
    $q.notify({ type: 'negative', message: 'Erro ao carregar pedidos ' + error.message });
  });
});
</script>
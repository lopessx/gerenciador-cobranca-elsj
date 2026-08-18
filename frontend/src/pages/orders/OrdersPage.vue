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
      <template v-slot:body="props">
        <q-tr :props="props">
          <q-td auto-width>
            <q-btn
              v-if="props.row.installments_data && props.row.installments_data.length > 0"
              size="sm"
              flat
              round
              dense
              :icon="props.expand ? 'expand_less' : 'expand_more'"
              @click="props.expand = !props.expand"
            />
          </q-td>
          <q-td
            v-for="col in props.cols.filter((c: { name: string }) => c.name !== 'actions')"
            :key="col.name"
            :props="props"
          >
            {{ col.value }}
          </q-td>
          <q-td auto-width>
            <div class="q-gutter-sm">
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
            </div>
          </q-td>
        </q-tr>
        <q-tr v-show="props.expand" :props="props">
          <q-td colspan="100%" class="q-pa-md">
            <div class="text-subtitle2 text-bold q-mb-sm">Boletos do Carnê</div>
            <div
              v-if="!props.row.installments_data || props.row.installments_data.length === 0"
              class="text-grey"
            >
              Nenhum boleto gerado ainda.
              <q-btn
                flat
                color="primary"
                label="Gerar boletos"
                size="sm"
                :loading="generatingId === props.row.order_id"
                @click="generateBankSlips(props.row)"
              />
            </div>
            <q-list v-else dense separator>
              <q-item v-for="inst in props.row.installments_data" :key="inst.id">
                <q-item-section avatar>
                  <q-icon
                    :name="inst.gateway_status === 'PAID' ? 'check_circle' : 'pending'"
                    :color="
                      inst.gateway_status === 'PAID'
                        ? 'positive'
                        : inst.gateway_status === 'ERROR'
                          ? 'negative'
                          : 'warning'
                    "
                  />
                </q-item-section>
                <q-item-section>
                  <q-item-label>
                    Parcela {{ inst.installment_number }}/{{ props.row.installments }} — R$
                    {{ inst.amount }} — Venc: {{ inst.due_date }}
                  </q-item-label>
                  <q-item-label caption v-if="inst.bank_slip_url">
                    <a :href="inst.bank_slip_url" target="_blank" class="text-primary"
                      >Abrir boleto</a
                    >
                    <span v-if="inst.bank_slip_barcode">
                      | Cód. Barras: {{ inst.bank_slip_barcode }}</span
                    >
                  </q-item-label>
                  <q-item-label caption v-else class="text-negative">
                    {{
                      inst.gateway_status === 'ERROR' ? 'Erro ao gerar boleto' : 'Boleto não gerado'
                    }}
                  </q-item-label>
                </q-item-section>
              </q-item>
            </q-list>
          </q-td>
        </q-tr>
      </template>
    </q-table>
  </q-page>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useQuasar } from 'quasar';
import { orderService } from '@/services/orderService';
import type { Order } from '@/types';

const $q = useQuasar();

const orders = ref<Order[]>([]);
const loading = ref(false);
const generatingId = ref<number | null>(null);

const columns = [
  { name: 'order_id', label: 'ID', field: 'order_id', sortable: true, align: 'left' as const },
  {
    name: 'client_name',
    label: 'Paciente',
    field: 'client_name',
    sortable: true,
    align: 'left' as const,
  },
  { name: 'client_cpf', label: 'CPF', field: 'client_cpf', sortable: true, align: 'left' as const },
  { name: 'amount', label: 'Valor', field: 'amount', sortable: true, align: 'left' as const },
  {
    name: 'installments',
    label: 'Parcelas',
    field: 'installments',
    sortable: true,
    align: 'left' as const,
  },
  {
    name: 'payment_method',
    label: 'Método de Pagamento',
    field: 'payment_method',
    sortable: true,
    align: 'left' as const,
  },
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

async function generateBankSlips(order: Order) {
  generatingId.value = order.order_id;
  try {
    await orderService.generateBankSlips(order.order_id);
    $q.notify({ type: 'positive', message: 'Boletos gerados com sucesso!' });
    await loadOrders();
  } catch {
    $q.notify({ type: 'negative', message: 'Erro ao gerar boletos' });
  } finally {
    generatingId.value = null;
  }
}

onMounted(() => {
  void loadOrders();
});
</script>

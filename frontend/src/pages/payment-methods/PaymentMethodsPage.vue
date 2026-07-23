<template>
  <q-page padding>
    <div class="row items-center q-mb-md">
      <div class="text-h6 col">Métodos de Pagamento</div>
      <q-btn color="primary" icon="add" label="Novo" @click="openDialog()" />
    </div>

    <q-table
      :rows="paymentMethods"
      :columns="columns"
      row-key="paymentmethod_id"
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
          <div class="text-h6">{{ editing ? 'Editar Método de Pagamento' : 'Novo Método de Pagamento' }}</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section>
          <q-form @submit="savePaymentMethod" class="q-gutter-md">
            <q-input v-model="form.name" label="Nome" outlined :rules="[val => !!val || 'Nome é obrigatório']" />
            <q-input v-model="form.api_key" label="API Key" outlined :rules="[val => !!val || 'API Key é obrigatória']" />
            <q-input v-model="form.secret" label="Secret" outlined :rules="[val => !!val || 'Secret é obrigatório']" />

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
import { paymentMethodService } from '@/services/paymentMethodService';
import type { PaymentMethod } from '@/types';

const $q = useQuasar();

const paymentMethods = ref<PaymentMethod[]>([]);
const loading = ref(false);
const dialogVisible = ref(false);
const saving = ref(false);
const editing = ref(false);

const form = ref({
  paymentmethod_id: 0,
  name: '',
  api_key: '',
  secret: '',
});

const columns: { name: string; label: string; field: string; sortable?: boolean; align: 'left' | 'right' | 'center' }[] = [
  { name: 'paymentmethod_id', label: 'ID', field: 'paymentmethod_id', sortable: true, align: 'left' },
  { name: 'name', label: 'Nome', field: 'name', sortable: true, align: 'left' },
  { name: 'api_key', label: 'API Key', field: 'api_key', sortable: true, align: 'left' },
  { name: 'actions', label: 'Ações', field: 'actions', align: 'center' },
];

async function loadPaymentMethods() {
  loading.value = true;
  try {
    paymentMethods.value = await paymentMethodService.list();
  } catch {
    $q.notify({ type: 'negative', message: 'Erro ao carregar métodos de pagamento' });
  } finally {
    loading.value = false;
  }
}

function openDialog(method?: PaymentMethod) {
  if (method) {
    editing.value = true;
    form.value = { ...method };
  } else {
    editing.value = false;
    form.value = {
      paymentmethod_id: 0,
      name: '',
      api_key: '',
      secret: '',
    };
  }
  dialogVisible.value = true;
}

async function savePaymentMethod() {
  saving.value = true;
  try {
    const data = { ...form.value };
    if (editing.value) {
      const { paymentmethod_id, ...updateData } = data;
      await paymentMethodService.update(paymentmethod_id, updateData);
      $q.notify({ type: 'positive', message: 'Método de pagamento atualizado com sucesso!' });
    } else {
      await paymentMethodService.create(data);
      $q.notify({ type: 'positive', message: 'Método de pagamento criado com sucesso!' });
    }
    dialogVisible.value = false;
    await loadPaymentMethods();
  } catch {
    $q.notify({ type: 'negative', message: 'Erro ao salvar método de pagamento' });
  } finally {
    saving.value = false;
  }
}

function confirmDelete(method: PaymentMethod) {
  $q.dialog({
    title: 'Confirmar exclusão',
    message: `Deseja excluir o método de pagamento "${method.name}"?`,
    cancel: true,
    persistent: true,
  }).onOk(async () => {
    try {
      await paymentMethodService.remove(method.paymentmethod_id);
      $q.notify({ type: 'positive', message: 'Método de pagamento excluído com sucesso!' });
      await loadPaymentMethods();
    } catch {
      $q.notify({ type: 'negative', message: 'Erro ao excluir método de pagamento' });
    }
  });
}

onMounted(loadPaymentMethods);
</script>

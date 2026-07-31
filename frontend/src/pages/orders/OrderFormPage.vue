<template>
  <q-page padding>
    <div class="row items-center q-mb-md">
      <q-btn flat round icon="arrow_back" @click="$router.push('/orders')" />
      <div class="text-h6 col">{{ isEditing ? 'Editar Pedido' : 'Novo Pedido' }}</div>
    </div>

    <q-card flat bordered>
      <q-card-section>
        <q-form @submit="saveOrder" class="q-gutter-md">
          <!-- Dados do Pedido -->
          <div class="text-subtitle1 text-bold">Dados do Pedido</div>

          <q-input
            v-model="form.amount"
            label="Valor"
            type="number"
            step="0.01"
            outlined
            :rules="[(val: string) => !!val || 'Valor é obrigatório']"
          />
          <q-input
            v-model.number="form.installments"
            label="Parcelas"
            type="number"
            outlined
            :rules="[(val: number) => val > 0 || 'Parcelas deve ser maior que 0']"
          />
          <q-select
            v-model="form.payment_method"
            label="Método de Pagamento"
            :options="paymentMethodOptions"
            outlined
            emit-value
            map-options
            :rules="[(val: string) => !!val || 'Método de pagamento é obrigatório']"
          />

          <q-separator />

          <!-- Dados do Cliente -->
          <div class="text-subtitle1 text-bold">Dados do Cliente (Paciente)</div>

          <q-select
            v-model="selectedExistingClient"
            label="Selecionar cliente existente"
            :options="clientOptions"
            option-value="client_id"
            option-label="name"
            outlined
            emit-value
            map-options
            clearable
            @update:model-value="onSelectExistingClient"
          />

          <div class="text-center text-grey-6">ou cadastre um novo cliente</div>

          <q-input
            v-model="clientForm.name"
            label="Nome completo"
            outlined
            :rules="[(val: string) => !!val || 'Nome é obrigatório']"
          />
          <q-input
            v-model="clientForm.cpf"
            label="CPF"
            outlined
            mask="###.###.###-##"
            unmasked-value
            :rules="[(val: string) => !!val || 'CPF é obrigatório']"
          />
          <q-input
            v-model="clientForm.email"
            label="E-mail"
            type="email"
            outlined
          />
          <q-input
            v-model="clientForm.phone"
            label="Telefone"
            outlined
            mask="(##) #####-####"
            unmasked-value
          />
          <q-input
            v-model="clientForm.address"
            label="Endereço"
            outlined
            type="textarea"
          />

          <q-card-actions align="right" class="q-px-none">
            <q-btn flat label="Cancelar" color="grey" @click="$router.push('/orders')" />
            <q-btn type="submit" label="Salvar" color="primary" :loading="saving" />
          </q-card-actions>
        </q-form>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useQuasar } from 'quasar';
import { orderService } from '@/services/orderService';
import { clientService } from '@/services/clientService';
import type { Client } from '@/types';

const $q = useQuasar();
const route = useRoute();
const router = useRouter();

const orderId = computed(() => Number(route.params.id));
const isEditing = computed(() => route.params.id !== undefined);

const clientOptions = ref<Client[]>([]);
const selectedExistingClient = ref<number | null>(null);
const saving = ref(false);

const paymentMethodOptions = ['paghiper_boleto'];

const form = ref({
  amount: '',
  installments: 1,
  payment_method: 'paghiper_boleto',
});

const clientForm = ref({
  name: '',
  cpf: '',
  email: '',
  phone: '',
  address: '',
});

async function loadClients() {
  try {
    clientOptions.value = await clientService.list();
  } catch {
    $q.notify({ type: 'negative', message: 'Erro ao carregar clientes' });
  }
}

function onSelectExistingClient(clientId: number | null) {
  if (clientId) {
    const client = clientOptions.value.find((c) => c.client_id === clientId);
    if (client) {
      clientForm.value = {
        name: client.name,
        cpf: client.cpf,
        email: client.email ?? '',
        phone: client.phone ?? '',
        address: client.address ?? '',
      };
    }
  } else {
    clientForm.value = { name: '', cpf: '', email: '', phone: '', address: '' };
  }
}

async function loadOrder() {
  if (!isEditing.value) return;
  try {
    const order = await orderService.get(orderId.value);
    form.value = {
      amount: order.amount,
      installments: order.installments,
      payment_method: order.payment_method,
    };
    selectedExistingClient.value = order.client_id;
    clientForm.value = {
      name: order.client_name,
      cpf: order.client_cpf,
      email: '',
      phone: '',
      address: '',
    };
  } catch {
    $q.notify({ type: 'negative', message: 'Erro ao carregar pedido' });
  }
}

async function saveOrder() {
  saving.value = true;
  try {
    let clientId: number | null = selectedExistingClient.value;

    if (!clientId) {
      const client = await clientService.create({
        name: clientForm.value.name,
        cpf: clientForm.value.cpf,
        email: clientForm.value.email || null,
        phone: clientForm.value.phone || null,
        address: clientForm.value.address || null,
      });
      clientId = client.client_id;
    }

    const orderData = {
      amount: form.value.amount,
      installments: form.value.installments,
      payment_method: form.value.payment_method,
      client_id: clientId,
    };

    if (isEditing.value) {
      await orderService.update(orderId.value, orderData);
      $q.notify({ type: 'positive', message: 'Pedido atualizado com sucesso!' });
    } else {
      await orderService.create(orderData);
      $q.notify({ type: 'positive', message: 'Pedido criado com sucesso!' });
    }

    router.push('/orders');
  } catch {
    $q.notify({ type: 'negative', message: 'Erro ao salvar pedido' });
  } finally {
    saving.value = false;
  }
}

onMounted(async () => {
  await loadClients();
  await loadOrder();
});
</script>
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
            @update:model-value="clampAmount"
            :rules="[
              (val: string) => (!!val && Number(val) > 0) || 'Valor é obrigatório',
              (val: string) => !val || Number(val) >= 3 || 'Valor mínimo R$ 3,00',
            ]"
          />
          <q-input
            v-model.number="form.installments"
            label="Parcelas"
            type="number"
            mask="##"
            min="1"
            max="12"
            outlined
            :rules="[
              (val: number) => !!val || 'Parcelas é obrigatório',
              (val: number) => (val >= 1 && val <= 12) || 'Parcelas deve ser entre 1 e 12',
            ]"
            @update:model-value="clampInstallments"
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
          <div class="text-subtitle1 text-bold">Dados do Paciente</div>

          <q-select
            v-model="selectedExistingClient"
            label="Selecionar paciente existente"
            :options="clientOptions"
            option-value="client_id"
            option-label="name"
            outlined
            emit-value
            map-options
            clearable
            use-input
            input-debounce="300"
            @filter="onFilterClients"
            @filter-abort="onFilterAbort"
            @update:model-value="onSelectExistingClient"
          >
            <template #no-option>
              <q-item>
                <q-item-section class="text-grey"> Nenhum paciente encontrado </q-item-section>
              </q-item>
            </template>
          </q-select>

          <div class="text-center text-grey-6">ou cadastre um novo paciente</div>

          <q-input
            v-model="clientForm.name"
            label="Nome completo"
            outlined
            :rules="[(val: string) => !!val || 'Nome é obrigatório']"
            @update:model-value="markClientFormDirty"
          />
          <q-input
            v-model="clientForm.cpf"
            label="CPF"
            outlined
            mask="###.###.###-##"
            unmasked-value
            :rules="[(val: string) => !!val || 'CPF é obrigatório']"
            @update:model-value="markClientFormDirty"
          />
          <q-input
            v-model="clientForm.email"
            label="E-mail (opcional)"
            type="email"
            outlined
            @update:model-value="markClientFormDirty"
          />
          <q-input
            v-model="clientForm.phone"
            label="Telefone (opcional)"
            outlined
            mask="(##) #####-####"
            unmasked-value
            @update:model-value="markClientFormDirty"
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
const isClientFormDirty = ref(false);
const saving = ref(false);

const paymentMethodOptions = [{ label: 'Boleto carnê', value: 'paghiper_boleto' }];

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

// Track when user modifies client fields after selecting an existing client
function markClientFormDirty() {
  if (selectedExistingClient.value) {
    isClientFormDirty.value = true;
  }
}

function clampAmount(val: string | number | null) {
  const num = parseFloat(String(val ?? ''));
  if (isNaN(num) || num < 0) {
    form.value.amount = '0';
  }
}

function clampInstallments(val: number | string | null) {
  const num = Number(val);
  if (isNaN(num) || num < 1) {
    form.value.installments = 1;
  } else if (num > 12) {
    form.value.installments = 12;
  }
}

function onFilterClients(val: string, update: (fn: () => void) => void) {
  if (val === '') {
    update(() => {
      clientOptions.value = [];
    });
    return;
  }
  update(() => {
    void (async () => {
      try {
        clientOptions.value = await clientService.search(val);
      } catch {
        $q.notify({ type: 'negative', message: 'Erro ao buscar pacientes' });
      }
    })();
  });
}

function onFilterAbort() {
  // nothing to clean up
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
      isClientFormDirty.value = false;
    }
  } else {
    clientForm.value = { name: '', cpf: '', email: '', phone: '', address: '' };
    isClientFormDirty.value = false;
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
    } else if (isClientFormDirty.value) {
      await clientService.update(clientId, {
        name: clientForm.value.name,
        cpf: clientForm.value.cpf,
        email: clientForm.value.email || null,
        phone: clientForm.value.phone || null,
        address: clientForm.value.address || null,
      });
    }

    const orderData = {
      amount: form.value.amount,
      installments: form.value.installments,
      payment_method: form.value.payment_method,
      client_id: clientId,
      client_name: clientForm.value.name,
      client_cpf: clientForm.value.cpf,
    };

    if (isEditing.value) {
      await orderService.update(orderId.value, orderData);
      $q.notify({ type: 'positive', message: 'Pedido atualizado com sucesso!' });
    } else {
      await orderService.create(orderData);
      $q.notify({ type: 'positive', message: 'Pedido criado com sucesso!' });
    }

    await router.push('/orders');
  } catch {
    $q.notify({ type: 'negative', message: 'Erro ao salvar pedido' });
  } finally {
    saving.value = false;
  }
}

onMounted(() => {
  void loadOrder();
});
</script>

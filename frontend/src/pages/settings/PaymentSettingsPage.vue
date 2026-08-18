<template>
  <q-page padding>
    <div class="row items-center q-mb-md">
      <div class="text-h5">Configurações de Métodos de Pagamento</div>
      <q-space />
      <q-btn
        color="primary"
        icon="save"
        label="Salvar todas as configurações"
        :loading="saving"
        @click="saveAll"
      />
    </div>

    <div v-if="loading" class="flex flex-center q-py-xl">
      <q-spinner size="lg" />
    </div>

    <template v-else>
      <q-card v-for="gateway in gateways" :key="gateway.name" class="q-mb-md" flat bordered>
        <q-card-section>
          <div class="row items-center">
            <div>
              <div class="text-h6">{{ gateway.label }}</div>
              <div class="text-caption text-grey-7">
                Slug: {{ gateway.name }} &middot; Métodos: {{ gateway.payment_methods.join(', ') }}
                <template v-if="gateway.supports_installments">
                  &middot; Suporta parcelamento
                </template>
              </div>
            </div>
          </div>
        </q-card-section>

        <q-separator />

        <q-card-section>
          <div class="row q-collector-sm q-collector-md">
            <div
              v-for="field in gateway.fields"
              :key="field.name"
              class="col-12 col-sm-6 col-md-4 q-pa-sm"
            >
              <!-- Campo texto -->
              <q-input
                v-if="field.type === 'text'"
                v-model="formValuesAny[gateway.name][field.name]"
                :label="field.label"
                :required="field.required"
                outlined
                stack-label
                dense
              />

              <!-- Campo senha -->
              <q-input
                v-else-if="field.type === 'password'"
                v-model="formValuesAny[gateway.name][field.name]"
                :label="field.label"
                :required="field.required"
                :type="passwordVisibility[gateway.name]?.[field.name] ? 'text' : 'password'"
                outlined
                stack-label
                dense
              >
                <template #append>
                  <q-icon
                    :name="
                      passwordVisibility[gateway.name]?.[field.name]
                        ? 'visibility_off'
                        : 'visibility'
                    "
                    class="cursor-pointer"
                    @click="togglePasswordVisibility(gateway.name, field.name)"
                  />
                </template>
              </q-input>

              <!-- Campo número -->
              <q-input
                v-else-if="field.type === 'number'"
                v-model.number="formValuesAny[gateway.name][field.name]"
                :label="field.label"
                :required="field.required"
                type="number"
                outlined
                stack-label
                dense
              />

              <!-- Campo checkbox -->
              <q-checkbox
                v-else-if="field.type === 'checkbox'"
                v-model="formValuesAny[gateway.name][field.name]"
                :label="field.label"
                dense
              />

              <!-- Campo select -->
              <q-select
                v-else-if="field.type === 'select'"
                v-model="formValuesAny[gateway.name][field.name]"
                :label="field.label"
                :options="field.options ?? []"
                :required="field.required"
                outlined
                dense
                emit-value
                map-options
              />
            </div>
          </div>
        </q-card-section>
      </q-card>

      <div v-if="gateways.length === 0" class="text-center text-grey-7 q-py-xl">
        <q-icon name="credit_card_off" size="lg" />
        <div class="q-mt-sm">Nenhum gateway de pagamento registrado.</div>
      </div>
    </template>
  </q-page>
</template>

<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import { useQuasar } from 'quasar';
import { paymentSettingsService } from '@/services/paymentSettingsService';
import type { PaymentGatewaySchema, PaymentSettingsResponse } from '@/types';

const $q = useQuasar();

const loading = ref(true);
const saving = ref(false);
const gateways = ref<PaymentGatewaySchema[]>([]);

// formValues[gatewayName][fieldName] = value
const formValues = reactive<Record<string, Record<string, boolean | number | string | undefined>>>(
  {},
);

// Helper tipado como any para evitar erros TS2322 e TS2532 no template
// (boolean não é aceito por QInput/QSelect; Record aninhado causa "possibly undefined" no index access)
// eslint-disable-next-line @typescript-eslint/no-explicit-any
const formValuesAny = formValues as Record<string, any>;

// Controle de visibilidade dos campos password
const passwordVisibility = reactive<Record<string, Record<string, boolean>>>({});

function togglePasswordVisibility(gatewayName: string, fieldName: string) {
  if (!passwordVisibility[gatewayName]) {
    passwordVisibility[gatewayName] = {};
  }
  passwordVisibility[gatewayName][fieldName] = !passwordVisibility[gatewayName][fieldName];
}

function buildInitialFormValues(data: PaymentSettingsResponse) {
  for (const gateway of data.gateways) {
    if (!formValues[gateway.name]) {
      formValues[gateway.name] = {};
    }
    for (const field of gateway.fields) {
      const savedValue = data.settings[gateway.name]?.[field.name]?.value;
      if (field.type === 'checkbox') {
        formValues[gateway.name]![field.name] = savedValue === 'true' || savedValue === '1';
      } else if (field.type === 'number') {
        formValues[gateway.name]![field.name] = savedValue ? Number(savedValue) : undefined;
      } else {
        formValues[gateway.name]![field.name] = savedValue ?? '';
      }
    }
  }
}

async function fetchSettings() {
  loading.value = true;
  try {
    const data = await paymentSettingsService.list();
    gateways.value = data.gateways;
    buildInitialFormValues(data);
  } catch (error: unknown) {
    let errorMsg = '';

    if (error instanceof Error) {
      errorMsg = error.message;
    }

    $q.notify({ type: 'negative', message: 'Erro ao carregar configurações. ' + errorMsg });
  } finally {
    loading.value = false;
  }
}

async function saveAll() {
  saving.value = true;
  try {
    // Constrói payload no formato esperado pelo backend
    const payloadSettings: Record<string, Record<string, string>> = {};

    for (const gatewayName of Object.keys(formValues)) {
      payloadSettings[gatewayName] = {};
      for (const fieldName of Object.keys(formValues[gatewayName]!)) {
        const val = formValues[gatewayName]![fieldName];
        if (val === true) {
          payloadSettings[gatewayName][fieldName] = 'true';
        } else if (val === false) {
          payloadSettings[gatewayName][fieldName] = 'false';
        } else if (val === undefined) {
          payloadSettings[gatewayName][fieldName] = '';
        } else {
          payloadSettings[gatewayName][fieldName] = String(val);
        }
      }
    }

    await paymentSettingsService.save({ settings: payloadSettings });
    $q.notify({ type: 'positive', message: 'Configurações salvas com sucesso.' });
  } catch (error: unknown) {
    let errorMsg = '';

    if (error instanceof Error) {
      errorMsg = error.message;
    }
    $q.notify({ type: 'negative', message: 'Erro ao salvar configurações. ' + errorMsg });
  } finally {
    saving.value = false;
  }
}

onMounted(() => {
  fetchSettings().catch(() => {
    $q.notify({ type: 'negative', message: 'Erro ao carregar configurações.' });
  });
});
</script>

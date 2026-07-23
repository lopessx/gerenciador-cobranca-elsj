<template>
  <q-layout view="lHh Lpr lFf">
    <q-page-container>
      <q-page class="flex flex-center bg-grey-2">
        <q-card class="q-pa-lg" style="width: 400px; max-width: 90vw">
          <q-card-section class="text-center">
            <div class="text-h5 text-weight-bold text-primary">Cobri</div>
            <div class="text-subtitle2 text-grey-7">Gerenciador de Pagamentos</div>
          </q-card-section>

          <q-card-section>
            <q-form @submit="onSubmit" class="q-gutter-md">
              <q-input
                v-model="form.email"
                label="Email"
                type="email"
                outlined
                :rules="[(val) => !!val || 'Email é obrigatório', (val) => /.+@.+/.test(val) || 'Email inválido']"
              >
                <template v-slot:prepend>
                  <q-icon name="email" />
                </template>
              </q-input>

              <q-input
                v-model="form.password"
                label="Senha"
                :type="isPwd ? 'password' : 'text'"
                outlined
                :rules="[(val) => !!val || 'Senha é obrigatória']"
              >
                <template v-slot:prepend>
                  <q-icon name="lock" />
                </template>
                <template v-slot:append>
                  <q-icon
                    :name="isPwd ? 'visibility_off' : 'visibility'"
                    class="cursor-pointer"
                    @click="isPwd = !isPwd"
                  />
                </template>
              </q-input>

              <q-btn
                type="submit"
                label="Entrar"
                color="primary"
                class="full-width"
                size="lg"
                :loading="loading"
              />
            </q-form>
          </q-card-section>
        </q-card>
      </q-page>
    </q-page-container>
  </q-layout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useQuasar } from 'quasar';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const $q = useQuasar();
const authStore = useAuthStore();

const form = ref({
  email: '',
  password: '',
});

const isPwd = ref(true);
const loading = ref(false);

async function onSubmit() {
  loading.value = true;
  try {
    await authStore.login(form.value.email, form.value.password);
    $q.notify({ type: 'positive', message: 'Login realizado com sucesso!' });
    await router.push('/');
  } catch (error: unknown) {
    const err = error as { response?: { data?: { message?: string } } };
    $q.notify({
      type: 'negative',
      message: err.response?.data?.message || 'Erro ao fazer login',
    });
  } finally {
    loading.value = false;
  }
}
</script>

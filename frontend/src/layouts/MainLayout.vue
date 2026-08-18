<template>
  <q-layout view="lHh Lpr lFf">
    <q-header elevated>
      <q-toolbar>
        <q-btn flat dense round icon="menu" aria-label="Menu" @click="toggleLeftDrawer" />

        <q-toolbar-title>Cobri</q-toolbar-title>

        <div class="row items-center q-gutter-sm">
          <q-btn flat round icon="logout" @click="logout">
            <q-tooltip>Sair</q-tooltip>
          </q-btn>
        </div>
      </q-toolbar>
    </q-header>

    <q-drawer v-model="leftDrawerOpen" show-if-above bordered>
      <q-list>
        <q-item-label header> Menu </q-item-label>

        <q-item clickable v-ripple to="/" exact>
          <q-item-section avatar>
            <q-icon name="home" />
          </q-item-section>
          <q-item-section>Início</q-item-section>
        </q-item>

        <q-item clickable v-ripple to="/users">
          <q-item-section avatar>
            <q-icon name="people" />
          </q-item-section>
          <q-item-section>Usuários</q-item-section>
        </q-item>

        <q-item clickable v-ripple to="/orders">
          <q-item-section avatar>
            <q-icon name="shopping_cart" />
          </q-item-section>
          <q-item-section>Pedidos</q-item-section>
        </q-item>

        <q-item clickable v-ripple to="/payment-settings">
          <q-item-section avatar>
            <q-icon name="settings" />
          </q-item-section>
          <q-item-section>Configurar Pagamentos</q-item-section>
        </q-item>
      </q-list>
    </q-drawer>

    <q-page-container>
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/services/api';

const leftDrawerOpen = ref(false);

function toggleLeftDrawer() {
  leftDrawerOpen.value = !leftDrawerOpen.value;
}

async function logout() {
  try {
    await api.post('/logout');
  } catch {
    // Ignora erro no logout; redireciona mesmo assim
  }

  const router = useRouter();
  void router.push('/login');
}
</script>

<template>
  <q-page padding>
    <div class="row items-center q-mb-md">
      <div class="text-h6 col">Usuários</div>
      <q-btn color="primary" icon="add" label="Novo" @click="openDialog()" />
    </div>

    <q-table
      :rows="users"
      :columns="columns"
      row-key="user_id"
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
          <div class="text-h6">{{ editing ? 'Editar Usuário' : 'Novo Usuário' }}</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section>
          <q-form @submit="saveUser" class="q-gutter-md">
            <q-input v-model="form.name" label="Nome" outlined :rules="[val => !!val || 'Nome é obrigatório']" />
            <q-input v-model="form.email" label="Email" type="email" outlined :rules="[val => !!val || 'Email é obrigatório', val => /.+@.+/.test(val) || 'Email inválido']" />
            <q-input v-model="form.password" label="Senha" type="password" outlined :rules="editing ? [] : [val => !!val || 'Senha é obrigatória']" />
            <q-input v-model="form.cpf" label="CPF" outlined mask="###.###.###-##" />
            <q-select v-model="form.role" label="Perfil" :options="roleOptions" option-value="value" option-label="label" outlined emit-value map-options :rules="[val => !!val || 'Perfil é obrigatório']" />

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
import { userService } from '@/services/userService';
import type { User } from '@/types';

const $q = useQuasar();

const users = ref<User[]>([]);
const loading = ref(false);
const dialogVisible = ref(false);
const saving = ref(false);
const editing = ref(false);

const form = ref({
  user_id: 0,
  name: '',
  email: '',
  password: '',
  cpf: '',
  role: 'operator',
});

const roleOptions = [
  { label: 'Administrador', value: 'admin' },
  { label: 'Operador', value: 'operator' },
];

const columns: { name: string; label: string; field: string; sortable?: boolean; align: 'left' | 'right' | 'center' }[] = [
  { name: 'user_id', label: 'ID', field: 'user_id', sortable: true, align: 'left' },
  { name: 'name', label: 'Nome', field: 'name', sortable: true, align: 'left' },
  { name: 'email', label: 'Email', field: 'email', sortable: true, align: 'left' },
  { name: 'cpf', label: 'CPF', field: 'cpf', sortable: true, align: 'left' },
  { name: 'role', label: 'Perfil', field: 'role', sortable: true, align: 'left' },
  { name: 'actions', label: 'Ações', field: 'actions', align: 'center' },
];

async function loadUsers() {
  loading.value = true;
  try {
    users.value = await userService.list();
  } catch {
    $q.notify({ type: 'negative', message: 'Erro ao carregar usuários' });
  } finally {
    loading.value = false;
  }
}

function openDialog(user?: User) {
  if (user) {
    editing.value = true;
    form.value = {
      user_id: user.user_id,
      name: user.name,
      email: user.email,
      password: '',
      cpf: user.cpf || '',
      role: user.role,
    };
  } else {
    editing.value = false;
    form.value = {
      user_id: 0,
      name: '',
      email: '',
      password: '',
      cpf: '',
      role: 'operator',
    };
  }
  dialogVisible.value = true;
}

async function saveUser() {
  saving.value = true;
  try {
    const data = { ...form.value };
    if (editing.value) {
      const { user_id, ...updateData } = data;
      if (!updateData.password) {
        // eslint-disable-next-line @typescript-eslint/no-dynamic-delete
        delete (updateData as Record<string, unknown>).password;
      }
      await userService.update(user_id, updateData as unknown as Partial<Omit<User, 'user_id'>>);
      $q.notify({ type: 'positive', message: 'Usuário atualizado com sucesso!' });
    } else {
      await userService.create(data as unknown as Omit<User, 'user_id'>);
      $q.notify({ type: 'positive', message: 'Usuário criado com sucesso!' });
    }
    dialogVisible.value = false;
    await loadUsers();
  } catch {
    $q.notify({ type: 'negative', message: 'Erro ao salvar usuário' });
  } finally {
    saving.value = false;
  }
}

function confirmDelete(user: User) {
  $q.dialog({
    title: 'Confirmar exclusão',
    message: `Deseja excluir o usuário "${user.name}"?`,
    cancel: true,
    persistent: true,
  }).onOk(async () => {
    try {
      await userService.remove(user.user_id);
      $q.notify({ type: 'positive', message: 'Usuário excluído com sucesso!' });
      await loadUsers();
    } catch {
      $q.notify({ type: 'negative', message: 'Erro ao excluir usuário' });
    }
  });
}

onMounted(loadUsers);
</script>

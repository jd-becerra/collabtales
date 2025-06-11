<template>
  <v-menu>
    <template v-slot:activator="{ props }" >
      <v-btn class="mr-6"
        icon
        v-bind="props" >
        <v-img
          src="/icons/account.svg"
          alt="Perfil icono"
          width="40"
          height="40"
          class="icon"
        ></v-img>
      </v-btn>
    </template>

    <v-list>
      <v-list-item @click="router.push('/perfil_usuario')">
        <v-list-item-title class="font-weight-bold">{{ $t('profile.profile_button') }}</v-list-item-title>
      </v-list-item>
      <v-list-item @click="logout" class="logout-text">
        <v-list-item-title>
          {{ $t('profile.logout') }}
        </v-list-item-title>
      </v-list-item>
    </v-list>
  </v-menu>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const router = useRouter();

function logout() {
  localStorage.removeItem('token');

  router.push('/'); // Redirect to login page
  alert(t('profile.logout_success'));
}
</script>

<style scoped>
.logout-text {
  color: var(--color-error);
}
</style>

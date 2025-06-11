<template>
  <AppNavbar />
  <div class="pa-6">
    <ReturnBtn
      class="return-btn mb-0 pb-0"
      @click="goToCuento">
      {{ $t('manage_collaborators.return') }}
    </ReturnBtn>

    <div class="d-flex flex-column align-start justify-start mb-0 py-0 mt-2">
      <div class="collab-header-container d-flex align-center">
        <v-img
          class="icon mr-2"
          src="/icons/groups_blue.svg"
          contain
          width="24"
          height="24"
        />
        <h1 class="collab-header">{{ $t('manage_collaborators.title') }}</h1>
      </div>
      <p class="collab-subheader text-h6 mb-0 pb-0">
        {{ $t('manage_collaborators.subtitle') }}
      </p>
    </div>

    <div v-if="loading">Loading...</div>
    <div v-else>
      <div class="cuento-header my-2 d-flex flex-row">
        <div>
          <h2 class="cuento-nombre">{{ $t('manage_collaborators.tale_title', { title: cuento.nombre }) }}</h2>
          <h3>{{ $t('manage_collaborators.tale_code')}}<b>{{ cuento.codigo_compartir }}</b></h3>
        </div>
        <div class="switch-container">
          <v-switch
            color="info"
            class="switch"
            :true-value="1"
            :false-value="0"
             v-model="switchValue"
            @change="onToggleColaboradores"
          />
          <p
            :style="{
                  color: cuento.admite_colaboradores === 1 ? getCSSVar('--color-text-blue') : getCSSVar('--color-text-off'),
            }"
          >
            <strong>{{ $t('manage_collaborators.allow_collaborators') }}</strong>
          </p>
        </div>
      </div>

      <v-list class="list">
        <v-list-item class="list-item-header">
          <v-row class="w-100">
            <v-col cols="4"><strong>{{ $t('manage_collaborators.collaborator_name') }}</strong></v-col>
            <v-col cols="4"><strong>{{ $t('manage_collaborators.collaborator_date') }}</strong></v-col>
            <v-col cols="4"><strong>{{ $t('manage_collaborators.actions') }}</strong></v-col>
          </v-row>
        </v-list-item>
        <v-divider></v-divider>
        <p v-if="colaboradores.length === 0" class="pa-4 text-center">
          {{ $t('manage_collaborators.no_collaborators') }}
        </p>
        <v-list-item
          v-for="colaborador in colaboradores"
          :key="colaborador.id_alumno"
          class="list-item"
        >
          <v-row class="list-row w-100 justify-center align-center">
            <v-col cols="4">
              <v-list-item-title>{{ colaborador.nombre }}</v-list-item-title>
            </v-col>
            <v-col cols="4">
              <v-list-item-subtitle>{{ colaborador.fecha_registro }}</v-list-item-subtitle>
            </v-col>
            <v-col cols="4">
              <BotonXs color_type="white_red" @click="showBloquear(colaborador.id_alumno, colaborador.nombre)">
                {{ $t('manage_collaborators.block_button') }}
              </BotonXs>
            </v-col>
          </v-row>
        </v-list-item>
      </v-list>

      <h3 class="mt-6">{{ $t('manage_collaborators.blocked_users') }}</h3>
      <v-list>
        <v-list-item class="list-item-header">
          <v-row class="w-100">
            <v-col cols="4"><strong>{{ $t('manage_collaborators.blocked_name') }}</strong></v-col>
            <v-col cols="4"><strong>{{ $t('manage_collaborators.blocked_date') }}</strong></v-col>
            <v-col cols="4"><strong>{{ $t('manage_collaborators.actions') }}</strong></v-col>
          </v-row>
        </v-list-item>
        <v-divider></v-divider>
        <p v-if="bloqueados.length === 0" class="pa-4 text-center">
          {{ $t('manage_collaborators.no_blocked_users') }}
        </p>
        <v-list-item
          v-for="bloqueado in bloqueados"
          :key="bloqueado.id_alumno"
          class="list-item"
        >
          <v-row class="list-row w-100 justify-center align-center">
            <v-col cols="4">
              <v-list-item-title>{{ bloqueado.nombre }}</v-list-item-title>
            </v-col>
            <v-col cols="4">
              <v-list-item-subtitle>{{ bloqueado.fecha_bloqueo }}</v-list-item-subtitle>
            </v-col>
            <v-col cols="4">
              <BotonXs color_type="white_purple" @click="showDesbloquear(bloqueado.id_alumno, bloqueado.nombre)">
                {{ $t('manage_collaborators.unblock_button') }}
              </BotonXs>
            </v-col>
          </v-row>
        </v-list-item>
      </v-list>

      <!-- Confirmación de bloqueo -->
      <v-dialog v-model="showConfirmarBloquearUsuario" max-width="400">
        <ConfirmacionBloquearUsuario
          :id_cuento="Number(id_cuento)"
          :id_usuario_bloquear="Number(selectedUser.id)"
          :nombre_usuario_bloquear="selectedUser.nombre"
          @close-popup="(selectedUser = { id: '', nombre: '' }, showConfirmarBloquearUsuario = false)"
          @confirmar-bloqueo="obtenerColaboradores"
        />
      </v-dialog>

      <!-- Confirmación de desbloqueo -->
      <v-dialog v-model="showConfirmarDesbloquearUsuario" max-width="400">
        <ConfirmacionDesbloquearUsuario
          :id_cuento="Number(id_cuento)"
          :id_usuario_desbloquear="Number(selectedUser.id)"
          :nombre_usuario_desbloquear="selectedUser.nombre"
          @close-popup="(selectedUser = { id: '', nombre: '' }, showConfirmarDesbloquearUsuario = false)"
          @confirmar-desbloqueo="obtenerColaboradores"
        />
      </v-dialog>

      <v-dialog v-model="showConfirmarPermitirColaboradores" max-width="400" @after-leave="switchValue = cuento.admite_colaboradores">
        <ConfirmacionPermitirColaboradores
          :id_cuento="Number(id_cuento)"
          @close-popup="showConfirmarPermitirColaboradores = false"
          @confirmar-permitir="obtenerColaboradores"
        />
      </v-dialog>

      <v-dialog v-model="showConfirmarRestringirColaboradores" max-width="400" @after-leave="switchValue = cuento.admite_colaboradores">
        <ConfirmacionRestringirColaboradores
          :id_cuento="Number(id_cuento)"
          @close-popup="showConfirmarRestringirColaboradores = false"
          @confirmar-permitir="obtenerColaboradores"
        />
      </v-dialog>
    </div>
  </div>
</template>

<script lang="ts">
import '../assets/base.css';

import axios from 'axios';
import { defineComponent, ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';

// Componentes
import AppNavbar from '@/components/AppNavbar.vue';
import ReturnBtn from '@/components/ReturnBtn.vue';
import BotonXs from '@/components/BotonXs.vue';
import ConfirmacionBloquearUsuario from '@/components/ConfirmacionBloquearUsuario.vue';
import ConfirmacionDesbloquearUsuario from '@/components/ConfirmacionDesbloquearUsuario.vue';
import ConfirmacionPermitirColaboradores from '@/components/ConfirmacionPermitirColaboradores.vue';
import ConfirmacionRestringirColaboradores from '@/components/ConfirmacionRestringirColaboradores.vue';

export default defineComponent({
  name: 'GestionColaboradoresView',
  components: {
    AppNavbar,
    ReturnBtn,
    BotonXs,
    ConfirmacionBloquearUsuario,
    ConfirmacionDesbloquearUsuario,
    ConfirmacionPermitirColaboradores,
    ConfirmacionRestringirColaboradores
  },
  props: {
    id_cuento: {
      type: String,
      required: true
    },
  },
  setup(props) {
    const cuento = ref({
      codigo_compartir: '',
      nombre: '',
      admite_colaboradores: 0,
    });
    const colaboradores = ref([{
      id_alumno: '',
      nombre: '',
      fecha_registro: ''
    }]);
    const bloqueados = ref([{
      id_alumno: '',
      nombre: '',
      fecha_bloqueo: ''
    }]);

    const loading = ref(false);
    const showConfirmarBloquearUsuario = ref(false);
    const showConfirmarDesbloquearUsuario = ref(false);
    const showConfirmarPermitirColaboradores = ref(false);
    const showConfirmarRestringirColaboradores = ref(false);
    const switchValue = ref(cuento.value.admite_colaboradores);
    const selectedUser = ref({ id: '', nombre: '' });
    const router = useRouter();

    const onToggleColaboradores = () => {
      if (switchValue.value !== cuento.value.admite_colaboradores) {
        if (switchValue.value === 1) {
          showConfirmarPermitirColaboradores.value = true;
        } else {
          showConfirmarRestringirColaboradores.value = true;
        }
      }
    };

    const obtenerColaboradores = async () => {
      try {
        const response = await axios.get(`https://collabtalesserver.avaldez0.com/php/obtener_colaboradores.php`, {
          params: {
            id_cuento: props.id_cuento
          },
          headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` }
        });

        if (response.status === 200) {
          cuento.value = response.data.cuento || {};
          switchValue.value = cuento.value.admite_colaboradores;
          colaboradores.value = response.data.colaboradores || [];
          bloqueados.value = response.data.bloqueados || [];

        } else {
          alert('An error occurred while fetching collaborators.');
          router.push('/mis_cuentos');
        }

      // eslint-disable-next-line @typescript-eslint/no-unused-vars
      } catch (error) {
        console.error('An error occurred while fetching collaborators');
      }
    }

    const goToCuento = () => {
      router.push('/ver_cuento_creado/' + props.id_cuento);
    };

    const getCSSVar = (varName: string) => {
      return getComputedStyle(document.documentElement).getPropertyValue(varName).trim();
    }

    const admiteColaboradores = computed({
      get: () => cuento.value.admite_colaboradores,
      set: (val) => {
        cuento.value.admite_colaboradores = val;
      }
    });

    const showBloquear = (id_usuario_bloquear: string, nombre_usuario_bloquear: string) => {
      selectedUser.value = { id: id_usuario_bloquear, nombre: nombre_usuario_bloquear };
      showConfirmarBloquearUsuario.value = true;
    }

    const showDesbloquear = (id_usuario_desbloquear: string, nombre_usuario_desbloquear: string) => {
      selectedUser.value = { id: id_usuario_desbloquear, nombre: nombre_usuario_desbloquear };
      showConfirmarDesbloquearUsuario.value = true;
    };

    onMounted(() => {
      loading.value = true;
      obtenerColaboradores().finally(() => {
        loading.value = false;
      });
    });

    return {
      cuento,
      colaboradores,
      bloqueados,
      loading,
      showConfirmarBloquearUsuario,
      showConfirmarDesbloquearUsuario,
      showConfirmarPermitirColaboradores,
      showConfirmarRestringirColaboradores,
      goToCuento,
      obtenerColaboradores,
      getCSSVar,
      admiteColaboradores,
      selectedUser,
      showBloquear,
      showDesbloquear,
      onToggleColaboradores,
      switchValue,
    };
  }
});
</script>

<style scoped>
.collab-header {
  font-weight: bold;
  font-size: var(--font-main-header-size);
}

.return-btn {
  font-size: 1rem;
}

.list {
  width: 100%;

}

.list-row {
  width: 100%;
}

.cuento-header {
  display: flex;
  justify-content: space-between;
  align-items: start;

}

.cuento-nombre {
  font-weight: bold;
  color: var(--color-text-blue);
}

.switch-container {
  display: flex;
  align-items: center;
}

.switch {
  margin: 0;
  padding: 0;

  height: 3.5rem;
  margin-right: 0.5rem;

  display: flex;
  align-items: start;
  justify-content: flex-start;
}

</style>

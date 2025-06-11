<template>
  <AppNavbar/>
  <div class="main-container">
    <v-container class="fill-height d-flex">
      <v-card color="transparent" width="500" variant="flat">
        <div class="d-inline-flex align-center ml-4">
          <v-img
            class="icon mr-2"
            src="/icons/person.svg"
            width="24"
            height="24"
          />
          <h1 class="text-h5 font-weight-bold">{{ $t('profile.title') }}</h1>
        </div>
        <div class="d-flex justify-end">
          <v-btn
            class="text-decoration-underline delete_account"
            variant="text"
            @click="showDeleteDialog = true"
            v-if="showPerfilEdicion"
          >
            {{ $t('profile.delete_account') }}
          </v-btn>
        </div>
        <v-card-text>
          <FormularioPerfil :datosAlumno="datosAlumno"
          v-if="showPerfil"
          key="perfil"
          @show-perfil-edicion="showPerfilEdicionForm"
          />

          <FormularioPerfilEdicion :datosAlumno="datosAlumno"
          v-if="showPerfilEdicion"
          key="edicion"
          @show-perfil="showPerfilForm"
          />
        </v-card-text>

        <v-btn
          variant="text"
          class="text-h7 justify-start"
          @click="retunPreviousPage"
        >
          <v-img
            src="/icons/chevron_left_black.svg"
            alt="Back arrow icon"
            width="24"
            height="24"
            class="mr-2 icon"
          ></v-img>
          {{ $t('profile.return') }}
        </v-btn>
      </v-card>

      <v-dialog v-model="showDeleteDialog" max-width="400">
        <v-card>
          <v-card-title class="text-h6">{{ $t('profile.confirm_delete_title') }}</v-card-title>
          <v-card-text>
            <p>{{ $t('profile.confirm_delete_message') }}</p>
          </v-card-text>
            <div class="d-flex justify-space-between px-4 mb-6">
              <BotonXs
                @click="showDeleteDialog = false"
              >
                {{ $t('profile.cancel') }}
              </BotonXs>
              <BotonXs
                color_type="white_red"
                @click="eliminarAlumno"
              >
                {{ $t('profile.confirm_delete') }}
              </BotonXs>
            </div>
        </v-card>
      </v-dialog>
    </v-container>

    <v-container class="d-flex justify-center align-center">
        <v-img
          class="image"
          src="/img/perfil.png"
        />
    </v-container>
  </div>
</template>


<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { useI18n } from 'vue-i18n';

import FormularioPerfil from '@/components/FormularioPerfil.vue';
import AppNavbar from '@/components/AppNavbar.vue';
import FormularioPerfilEdicion from '@/components/FormularioPerfilEdicion.vue';
import BotonXs from '@/components/BotonXs.vue';

const { t } = useI18n();
const router = useRouter();
const datosAlumno = ref({ id_alumno: '', nombre: '' , correo: ''});
const showDeleteDialog = ref(false);
const showPerfil = ref(true);
const showPerfilEdicion = ref(false);


onMounted(() => {
  getDatosAlumno();
});

function showPerfilForm() {
  showPerfil.value = true;
  showPerfilEdicion.value = false;
}

function showPerfilEdicionForm() {
  showPerfil.value = false;
  showPerfilEdicion.value = true;
}

function getDatosAlumno() {
  const id_alumno = localStorage.getItem('id_alumno');
  if (!id_alumno) {
    alert('No account found. Please log in again.');
    router.push('/');
    return;
  }

  axios
    .get(`https://collabtalesserver.avaldez0.com/php/obtener_alumno.php`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`
      },
      params: {
        id_alumno: id_alumno
      }
    })
    .then((response) => {
      datosAlumno.value = {
        id_alumno: response.data.id_alumno,
        nombre: response.data.nombre,
        correo: response.data.correo
      };
    })
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    .catch((error) => {
      console.error('Could not fetch user data');
    });
}

function eliminarAlumno() {
  axios
    .delete(`https://collabtalesserver.avaldez0.com/php/eliminar_alumno.php`,
    {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('token')}`
      }
    })
    .then(() => {
      alert(t('profile.delete_success'));
      localStorage.removeItem('id_alumno');
      localStorage.removeItem('token');
      router.push('/');
    })
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    .catch((error) => {
      console.error('Could not delete user account');
    });
}

function retunPreviousPage() {
  router.push('/mis_cuentos');
}
</script>

<style scoped>
.fill-height {
  height: 90vh;
}

.main-container {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: center;
  align-content: center;

  gap: 0;

}

.image {
  width: 65%;
  height: 65%;

  max-width: 65%;
  max-height: 65%;

  border-radius: 5px;
}

.delete_account {
  color: var(--color-error);
}
</style>

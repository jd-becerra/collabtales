<template>
    <v-container>
      <v-card class="pa-4" max-width="500" outlined>
        <v-card-title>Crear un Cuento</v-card-title>
        <v-card-text>
          <v-form @submit.prevent="crearCuento">
            <v-text-field 
              label="Nombre del cuento" 
              v-model="nombreCuento" 
              required
            ></v-text-field>
            <v-textarea 
              label="Descripción" 
              v-model="descripcionCuento" 
              required
            ></v-textarea>
            <v-btn color="success" type="submit" class="mr-2">Crear Cuento</v-btn>
            <v-btn color="error" @click="cancelar">Cancelar</v-btn>
          </v-form>
        </v-card-text>
      </v-card>
    </v-container>
  </template>
  
  <script setup lang="ts">
  import { ref } from 'vue';
  import { useRouter } from 'vue-router';
  
  const nombreCuento = ref('');
  const descripcionCuento = ref('');
  const router = useRouter();
  
  const crearCuento = () => {
    if (!nombreCuento.value.trim() || !descripcionCuento.value.trim()) {
      alert('Por favor, completa todos los campos.');
      return;
    }
    
    console.log('Cuento creado:', {
      nombre: nombreCuento.value,
      descripcion: descripcionCuento.value,
    });
    
    alert('Cuento creado exitosamente');
    router.push('/lista_cuentos');
  };
  
  const cancelar = () => {
    router.push('/lista_cuentos');
  };
  </script>
  
  <style scoped>
  .v-container {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
  }
  </style>
  
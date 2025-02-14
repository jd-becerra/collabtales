<template>
  <div>
    <div v-if="showRegister">
      <form @submit.prevent="register">
        <h1>Registro de cuenta</h1>
        <label for="username">Usuario:</label>
        <input type="text" id="username" v-model="registerData.username" required /><br />

        <label for="password">Contraseña:</label>
        <input type="password" id="password" v-model="registerData.password" required /><br />

        <input type="submit" value="Registrarse" />
        <button type="button" @click="showRegister = false">Ya tengo una cuenta</button>
      </form>
    </div>

    <div v-else>
      <form @submit.prevent="login">
        <h1>Iniciar sesión</h1>
        <label for="iniciarUsuario">Usuario:</label>
        <input type="text" id="iniciarUsuario" v-model="loginData.username" required /><br />

        <label for="iniciarPass">Contraseña:</label>
        <input type="password" id="iniciarPass" v-model="loginData.password" required /><br />

        <input type="submit" value="Iniciar Sesión" />
        <button type="button" @click="showRegister = true">Crear una cuenta</button>
      </form>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { useRouter } from 'vue-router';

export default {
  setup() {
    const router = useRouter();
    return { router };
  },
  data() {
    return {
      showRegister: false,
      registerData: {
        username: '',
        password: ''
      },
      loginData: {
        username: '',
        password: ''
      }
    };
  },
  methods: {
    register: function () {
      axios.post('./php/insert_alumno.php', new URLSearchParams(this.registerData))
      .then(function (response) {

        if (response.data.trim() !== 'El usuario ya existe') {
          alert(`Creación de cuenta exitosa para: ${this.registerData.username}, haga click para continuar`);
          localStorage.setItem('id_alumno', response.data.trim());
          this.router.push('/dashboard');
        } else {
          alert(response.data);
        }
      }).catch(function (error) {
        console.error('Error en registro:', error);
      });
    },

    login: function () {
      axios.post('./php/login_alumno.php', new URLSearchParams(this.loginData))
      .then(function (response) {
        const datos = response.data;
        if (datos.length > 0) {
          alert(`¡Bienvenido: ${this.loginData.username}! Haga click para continuar`);
          localStorage.setItem('id_alumno', datos[0].id_alumno);
          this.router.push('/dashboard');
        } else {
          alert('ERROR: Usuario o contraseña incorrectos');
        }
      }).catch(function (error) {
        console.error('Error en inicio de sesión:', error);
      });
    }
  }
};
</script>
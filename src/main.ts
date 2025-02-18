import './assets/main.css';

import { createApp } from 'vue';
import App from './App.vue';
import router from './router';

import { createVuetify } from 'vuetify';
import 'vuetify/styles';

import {
  VApp,
  VAppBar,
  VAppBarNavIcon,
  VContainer,
  VImg,
  VBtn,
  VSpacer,
  VMain,
  VCard,
  VCardText,
  VCardTitle,
  VCardActions,
  VNavigationDrawer,
  VList,
  VListItem,
  VListItemTitle,
  VForm,
  VTextarea,
  VTextField,
  VIcon,
  VToolbar,
  VToolbarTitle
} from 'vuetify/components';

import { Ripple, Intersect, Scroll, Touch } from 'vuetify/directives';

const vuetify = createVuetify({
  components: {
    VApp,
    VAppBar,
    VAppBarNavIcon,
    VContainer,
    VImg,
    VBtn,
    VSpacer,
    VMain,
    VCard,
    VCardText,
    VCardTitle,
    VCardActions,
    VNavigationDrawer,
    VList,
    VListItem,
    VListItemTitle,
    VForm,
    VTextarea,
    VTextField,
    VIcon,
    VToolbar,
    VToolbarTitle
  },
  directives: {
    Ripple,
    Intersect,
    Scroll,
    Touch
  }
});

const app = createApp(App);


import axios from 'axios';

axios.defaults.baseURL = 'http://localhost/collabtales/backend/';
axios.defaults.headers.common['Content-Type'] = 'application/x-www-form-urlencoded';


app.use(router);
app.use(vuetify);

app.mount('#app');

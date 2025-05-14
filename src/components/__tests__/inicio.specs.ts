import { mount } from '@vue/test-utils';
import { describe, it, expect, vi } from 'vitest';
import LoginRegister from '@/views/InicioSesionView.vue'; // Asegúrate de que la ruta es correcta

// Mock de useRouter
vi.mock('vue-router', () => ({
  useRouter: () => ({ push: vi.fn() }),
}));

describe('LoginRegister.vue', () => {
  it('muestra el formulario de login por defecto', () => {
    const wrapper = mount(LoginRegister);
    expect(wrapper.find('v-card-title').text()).toContain('Iniciar sesión');
  });

  it('cambia al formulario de registro cuando se presiona el botón', async () => {
    const wrapper = mount(LoginRegister);
    await wrapper.find('button').trigger('click');
    expect(wrapper.find('v-card-title').text()).toContain('Crea tu cuenta');
  });
});

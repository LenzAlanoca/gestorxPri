<template>
  <div class="register-container">
    <div class="register-card">
      <div class="register-header">
        <h2>Registro de Empresa</h2>
        <p>Crear nueva cuenta en Sistema GestorX</p>
      </div>
      
      <form @submit.prevent="register" class="register-form">
        <div class="form-row">
          <div class="form-group">
            <label for="nombre_comercial">Nombre Comercial *</label>
            <input 
              type="text" 
              id="nombre_comercial" 
              v-model="empresa.nombre_comercial"
              required
              placeholder="Nombre de la empresa"
            >
          </div>
          
          <div class="form-group">
            <label for="razon_social">Razón Social</label>
            <input 
              type="text" 
              id="razon_social" 
              v-model="empresa.razon_social"
              placeholder="Razón social (opcional)"
            >
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label for="correo_contacto">Correo Contacto *</label>
            <input 
              type="email" 
              id="correo_contacto" 
              v-model="empresa.correo_contacto"
              required
              placeholder="correo@empresa.com"
            >
          </div>
          
          <div class="form-group">
            <label for="telefono">Teléfono</label>
            <input 
              type="tel" 
              id="telefono" 
              v-model="empresa.telefono"
              placeholder="Número de teléfono"
            >
          </div>
        </div>
        
        <div class="form-group">
          <label for="direccion">Dirección</label>
          <input 
            type="text" 
            id="direccion" 
            v-model="empresa.direccion"
            placeholder="Dirección de la empresa"
          >
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label for="nombre_administrador">Nombre Administrador *</label>
            <input 
              type="text" 
              id="nombre_administrador" 
              v-model="empresa.nombre_administrador"
              required
              placeholder="Nombre del administrador"
            >
          </div>
          
          <div class="form-group">
            <label for="apellido_administrador">Apellido Administrador</label>
            <input 
              type="text" 
              id="apellido_administrador" 
              v-model="empresa.apellido_administrador"
              placeholder="Apellido del administrador"
            >
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label for="password">Contraseña *</label>
            <input 
              type="password" 
              id="password" 
              v-model="empresa.password"
              required
              placeholder="••••••••"
            >
          </div>
          
          <div class="form-group">
            <label for="confirm_password">Confirmar Contraseña *</label>
            <input 
              type="password" 
              id="confirm_password" 
              v-model="empresa.confirm_password"
              required
              placeholder="••••••••"
            >
          </div>
        </div>
        
        <div class="form-group">
          <label class="checkbox-label">
            <input type="checkbox" v-model="acceptTerms" required>
            Acepto los <a href="#">términos y condiciones</a> del servicio
          </label>
        </div>
        
        <button type="submit" class="btn-register" :disabled="loading">
          {{ loading ? 'Registrando...' : 'Registrar Empresa' }}
        </button>
        
        <div v-if="error" class="error-message">
          {{ error }}
        </div>
        
        <div v-if="success" class="success-message">
          {{ success }}
          <p>Redirigiendo al login...</p>
        </div>
      </form>
      
      <div class="register-footer">
        <p>¿Ya tienes una cuenta? <router-link to="/login">Iniciar Sesión</router-link></p>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

export default {
  setup() {
    const router = useRouter()
    const empresa = ref({
      nombre_comercial: '',
      razon_social: '',
      direccion: '',
      telefono: '',
      correo_contacto: '',
      nombre_administrador: '',
      apellido_administrador: '',
      password: '',
      confirm_password: ''
    })
    
    const acceptTerms = ref(false)
    const loading = ref(false)
    const error = ref('')
    const success = ref('')
    
    const API_URL = 'http://localhost/gestorx/backend/api'
    
    const register = async () => {
      if (empresa.value.password !== empresa.value.confirm_password) {
        error.value = 'Las contraseñas no coinciden'
        return
      }
      
      if (empresa.value.password.length < 6) {
        error.value = 'La contraseña debe tener al menos 6 caracteres'
        return
      }
      
      loading.value = true
      error.value = ''
      
      try {
        const response = await axios.post(`${API_URL}/registro.php`, empresa.value)
        
        if (response.data.success) {
          success.value = response.data.message
          
          setTimeout(() => {
            router.push('/login')
          }, 3000)
        } else {
          error.value = response.data.message
        }
      } catch (err) {
        error.value = 'Error de conexión con el servidor'
      } finally {
        loading.value = false
      }
    }
    
    return {
      empresa,
      acceptTerms,
      loading,
      error,
      success,
      register
    }
  }
}
</script>

<style scoped>
.register-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 20px;
}

.register-card {
  background: white;
  border-radius: 10px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
  padding: 40px;
  width: 100%;
  max-width: 800px;
}

.register-header {
  text-align: center;
  margin-bottom: 30px;
}

.register-header h2 {
  color: #333;
  margin-bottom: 10px;
}

.register-header p {
  color: #666;
}

.register-form {
  margin-bottom: 30px;
}

.form-row {
  display: flex;
  gap: 20px;
  margin-bottom: 20px;
}

.form-row .form-group {
  flex: 1;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  color: #555;
  font-weight: 500;
}

.form-group input {
  width: 100%;
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 16px;
  transition: border-color 0.3s;
}

.form-group input:focus {
  outline: none;
  border-color: #667eea;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: normal;
}

.checkbox-label a {
  color: #667eea;
  text-decoration: none;
}

.checkbox-label a:hover {
  text-decoration: underline;
}

.btn-register {
  width: 100%;
  padding: 12px;
  background: #667eea;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.3s;
}

.btn-register:hover:not(:disabled) {
  background: #5a67d8;
}

.btn-register:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.error-message {
  margin-top: 15px;
  padding: 10px;
  background: #fed7d7;
  color: #c53030;
  border-radius: 6px;
  text-align: center;
}

.success-message {
  margin-top: 15px;
  padding: 15px;
  background: #c6f6d5;
  color: #22543d;
  border-radius: 6px;
  text-align: center;
}

.register-footer {
  text-align: center;
  color: #888;
  font-size: 14px;
}

.register-footer a {
  color: #667eea;
  text-decoration: none;
}

.register-footer a:hover {
  text-decoration: underline;
}

@media (max-width: 768px) {
  .form-row {
    flex-direction: column;
    gap: 0;
  }
  
  .register-card {
    padding: 20px;
  }
}
</style>
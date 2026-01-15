<template>
  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <h2>Sistema GestorX</h2>
        <p>Inicio de Sesión</p>
      </div>
      
      <form @submit.prevent="login" class="login-form">
        <div class="form-group">
          <label for="email">Correo Electrónico</label>
          <input 
            type="email" 
            id="email" 
            v-model="credentials.correo"
            required
            placeholder="correo@ejemplo.com"
          >
        </div>
        
        <div class="form-group">
          <label for="password">Contraseña</label>
          <input 
            type="password" 
            id="password" 
            v-model="credentials.password"
            required
            placeholder="••••••••"
          >
        </div>
        
        <button type="submit" class="btn-login" :disabled="loading">
          {{ loading ? 'Iniciando...' : 'Iniciar Sesión' }}
        </button>
        
        <div v-if="error" class="error-message">
          {{ error }}
        </div>
      </form>
      
      <div class="login-footer">
        <p>Sistema de Gestión SaaS</p>
        <p>¿No tienes cuenta? <router-link to="/register">Regístrate aquí</router-link></p>
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
    const credentials = ref({
      correo: '',
      password: ''
    })
    const loading = ref(false)
    const error = ref('')
    
    const API_URL = 'http://localhost/gestorx/backend/api'
    
    const login = async () => {
      loading.value = true
      error.value = ''
      
      try {
        const response = await axios.post(`${API_URL}/auth.php`, {
          action: 'login',
          ...credentials.value
        })
        
        if (response.data.success) {
          localStorage.setItem('token', response.data.token)
          localStorage.setItem('user', JSON.stringify(response.data.user))
          
          const userRole = response.data.user.rol
          if (userRole === 'superadministrador' || userRole === 'administrador') {
            router.push('/admin/usuarios')
          } else {
            router.push('/user')
          }
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
      credentials,
      loading,
      error,
      login
    }
  }
}
</script>

<style scoped>
.login-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.login-card {
  background: white;
  border-radius: 10px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
  padding: 40px;
  width: 100%;
  max-width: 400px;
}

.login-header {
  text-align: center;
  margin-bottom: 30px;
}

.login-header h2 {
  color: #333;
  margin-bottom: 10px;
}

.login-header p {
  color: #666;
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

.btn-login {
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

.btn-login:hover:not(:disabled) {
  background: #5a67d8;
}

.btn-login:disabled {
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

.login-footer {
  margin-top: 30px;
  text-align: center;
  color: #888;
  font-size: 14px;
}

.login-footer a {
  color: #667eea;
  text-decoration: none;
}

.login-footer a:hover {
  text-decoration: underline;
}
</style>
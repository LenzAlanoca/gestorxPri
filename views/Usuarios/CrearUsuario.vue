<template>
  <div class="crear-usuario-container">
    <div class="header">
      <h1>Crear Nuevo Usuario</h1>
      <router-link to="/admin/usuarios" class="btn-secondary">
        ← Volver a Usuarios
      </router-link>
    </div>
    
    <div class="card">
      <form @submit.prevent="crearUsuario" class="user-form">
        <div class="form-row">
          <div class="form-group">
            <label for="nombre">Nombre *</label>
            <input 
              type="text" 
              id="nombre" 
              v-model="usuario.nombre"
              required
              placeholder="Nombre del usuario"
              :class="{ 'input-error': errors.nombre }"
            >
            <div v-if="errors.nombre" class="error-message">
              {{ errors.nombre }}
            </div>
          </div>
          
          <div class="form-group">
            <label for="apellido">Apellido *</label>
            <input 
              type="text" 
              id="apellido" 
              v-model="usuario.apellido"
              required
              placeholder="Apellido del usuario"
              :class="{ 'input-error': errors.apellido }"
            >
            <div v-if="errors.apellido" class="error-message">
              {{ errors.apellido }}
            </div>
          </div>
        </div>
        
        <div class="form-group">
          <label for="correo">Correo Electrónico *</label>
          <input 
            type="email" 
            id="correo" 
            v-model="usuario.correo"
            required
            placeholder="correo@ejemplo.com"
            :class="{ 'input-error': errors.correo }"
          >
          <div v-if="errors.correo" class="error-message">
            {{ errors.correo }}
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label for="password">Contraseña *</label>
            <div class="password-input">
              <input 
                :type="showPassword ? 'text' : 'password'" 
                id="password" 
                v-model="usuario.password"
                required
                placeholder="••••••••"
                :class="{ 'input-error': errors.password }"
              >
              <button 
                type="button" 
                class="toggle-password"
                @click="showPassword = !showPassword"
              >
                {{ showPassword ? '🙈' : '👁️' }}
              </button>
            </div>
            <div class="password-strength" v-if="usuario.password">
              <div class="strength-bar" :class="passwordStrengthClass"></div>
              <small>{{ passwordStrengthText }}</small>
            </div>
            <div v-if="errors.password" class="error-message">
              {{ errors.password }}
            </div>
          </div>
          
          <div class="form-group">
            <label for="confirm_password">Confirmar Contraseña *</label>
            <input 
              :type="showConfirmPassword ? 'text' : 'password'" 
              id="confirm_password" 
              v-model="usuario.confirm_password"
              required
              placeholder="••••••••"
              :class="{ 'input-error': errors.confirm_password }"
            >
            <div v-if="errors.confirm_password" class="error-message">
              {{ errors.confirm_password }}
            </div>
          </div>
        </div>
        
        <div class="form-group">
          <label for="id_rol">Rol *</label>
          <select 
            id="id_rol" 
            v-model="usuario.id_rol"
            required
            :class="{ 'input-error': errors.id_rol }"
          >
            <option value="">Seleccione un rol</option>
            <option v-for="rol in roles" :key="rol.id_rol" :value="rol.id_rol">
              {{ rol.nombre_rol }} - {{ rol.descripcion }}
            </option>
          </select>
          <div v-if="errors.id_rol" class="error-message">
            {{ errors.id_rol }}
          </div>
        </div>
        
        <div class="form-actions">
          <button 
            type="button" 
            @click="$router.push('/admin/usuarios')" 
            class="btn-secondary"
            :disabled="loading"
          >
            Cancelar
          </button>
          <button 
            type="submit" 
            class="btn-primary"
            :disabled="loading"
          >
            {{ loading ? 'Creando...' : 'Crear Usuario' }}
          </button>
        </div>
      </form>
      
      <div v-if="successMessage" class="success-message">
        <div class="success-icon">✅</div>
        <div>
          <h3>¡Usuario creado exitosamente!</h3>
          <p>{{ successMessage }}</p>
          <div class="success-actions">
            <button @click="crearOtro" class="btn-secondary">
              Crear otro usuario
            </button>
            <router-link to="/admin/usuarios" class="btn-primary">
              Ver todos los usuarios
            </router-link>
          </div>
        </div>
      </div>
      
      <div v-if="errorMessage" class="error-message-card">
        <div class="error-icon">❌</div>
        <div>
          <h3>Error al crear usuario</h3>
          <p>{{ errorMessage }}</p>
          <button @click="errorMessage = ''" class="btn-secondary">
            Intentar de nuevo
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

export default {
  setup() {
    const router = useRouter()
    const usuario = ref({
      nombre: '',
      apellido: '',
      correo: '',
      password: '',
      confirm_password: '',
      id_rol: ''
    })
    
    const errors = ref({})
    const loading = ref(false)
    const successMessage = ref('')
    const errorMessage = ref('')
    const showPassword = ref(false)
    const showConfirmPassword = ref(false)
    const roles = ref([])
    
    const API_URL = 'http://localhost/gestorx/backend/api'
    
    const getAuthHeader = () => {
      const token = localStorage.getItem('token')
      return {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      }
    }
    
    const passwordStrength = computed(() => {
      const password = usuario.value.password
      if (!password) return 0
      
      let strength = 0
      if (password.length >= 8) strength += 1
      if (/[A-Z]/.test(password)) strength += 1
      if (/[0-9]/.test(password)) strength += 1
      if (/[^A-Za-z0-9]/.test(password)) strength += 1
      
      return strength
    })
    
    const passwordStrengthClass = computed(() => {
      const strength = passwordStrength.value
      if (strength === 0) return 'strength-0'
      if (strength === 1) return 'strength-1'
      if (strength === 2) return 'strength-2'
      if (strength === 3) return 'strength-3'
      return 'strength-4'
    })
    
    const passwordStrengthText = computed(() => {
      const strength = passwordStrength.value
      if (strength === 0) return 'Muy débil'
      if (strength === 1) return 'Débil'
      if (strength === 2) return 'Regular'
      if (strength === 3) return 'Buena'
      return 'Excelente'
    })
    
    const validarFormulario = () => {
      errors.value = {}
      let isValid = true
      
      if (!usuario.value.nombre.trim()) {
        errors.value.nombre = 'El nombre es requerido'
        isValid = false
      }
      
      if (!usuario.value.apellido.trim()) {
        errors.value.apellido = 'El apellido es requerido'
        isValid = false
      }
      
      if (!usuario.value.correo.trim()) {
        errors.value.correo = 'El correo es requerido'
        isValid = false
      } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(usuario.value.correo)) {
        errors.value.correo = 'Correo electrónico inválido'
        isValid = false
      }
      
      if (!usuario.value.password) {
        errors.value.password = 'La contraseña es requerida'
        isValid = false
      } else if (usuario.value.password.length < 6) {
        errors.value.password = 'La contraseña debe tener al menos 6 caracteres'
        isValid = false
      }
      
      if (!usuario.value.confirm_password) {
        errors.value.confirm_password = 'Confirme la contraseña'
        isValid = false
      } else if (usuario.value.password !== usuario.value.confirm_password) {
        errors.value.confirm_password = 'Las contraseñas no coinciden'
        isValid = false
      }
      
      if (!usuario.value.id_rol) {
        errors.value.id_rol = 'Seleccione un rol'
        isValid = false
      }
      
      return isValid
    }
    
    const cargarRoles = async () => {
      try {
        const response = await axios.get(`${API_URL}/roles.php`, getAuthHeader())
        if (response.data.success) {
          roles.value = response.data.data
        }
      } catch (error) {
        console.error('Error al cargar roles:', error)
      }
    }
    
    const crearUsuario = async () => {
      if (!validarFormulario()) return
      
      loading.value = true
      errorMessage.value = ''
      successMessage.value = ''
      
      try {
        const userData = {
          nombre: usuario.value.nombre,
          apellido: usuario.value.apellido,
          correo: usuario.value.correo,
          password: usuario.value.password,
          id_rol: usuario.value.id_rol
        }
        
        const response = await axios.post(
          `${API_URL}/usuarios.php`,
          userData,
          getAuthHeader()
        )
        
        if (response.data.success) {
          successMessage.value = `Usuario "${userData.nombre} ${userData.apellido}" creado exitosamente.`
        } else {
          errorMessage.value = response.data.message
        }
      } catch (error) {
        console.error('Error al crear usuario:', error)
        
        if (error.response) {
          if (error.response.status === 401) {
            localStorage.removeItem('token')
            localStorage.removeItem('user')
            router.push('/login')
          } else if (error.response.data && error.response.data.message) {
            errorMessage.value = error.response.data.message
          } else {
            errorMessage.value = 'Error al crear usuario. Verifique los datos.'
          }
        } else {
          errorMessage.value = 'Error de conexión con el servidor'
        }
      } finally {
        loading.value = false
      }
    }
    
    const crearOtro = () => {
      successMessage.value = ''
      usuario.value = {
        nombre: '',
        apellido: '',
        correo: '',
        password: '',
        confirm_password: '',
        id_rol: ''
      }
      errors.value = {}
    }
    
    onMounted(() => {
      cargarRoles()
    })
    
    return {
      usuario,
      errors,
      loading,
      successMessage,
      errorMessage,
      showPassword,
      showConfirmPassword,
      roles,
      passwordStrengthClass,
      passwordStrengthText,
      crearUsuario,
      crearOtro
    }
  }
}
</script>

<style scoped>
.crear-usuario-container {
  padding: 20px;
  max-width: 800px;
  margin: 0 auto;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
  flex-wrap: wrap;
  gap: 20px;
}

.header h1 {
  margin: 0;
  color: #333;
  font-size: 28px;
}

.card {
  background: white;
  border-radius: 10px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  padding: 30px;
}

.user-form {
  display: flex;
  flex-direction: column;
  gap: 25px;
}

.form-row {
  display: flex;
  gap: 20px;
}

.form-row .form-group {
  flex: 1;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group label {
  font-weight: 600;
  color: #4a5568;
  font-size: 14px;
}

.form-group input,
.form-group select {
  padding: 12px 15px;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  font-size: 15px;
  transition: all 0.3s;
  background: white;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-group input.input-error,
.form-group select.input-error {
  border-color: #fc8181;
}

.form-group input.input-error:focus {
  box-shadow: 0 0 0 3px rgba(252, 129, 129, 0.1);
}

.password-input {
  position: relative;
}

.toggle-password {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  font-size: 18px;
  color: #718096;
  padding: 0;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.toggle-password:hover {
  color: #4a5568;
}

.password-strength {
  margin-top: 8px;
}

.strength-bar {
  height: 6px;
  border-radius: 3px;
  margin-bottom: 4px;
  transition: width 0.3s;
}

.strength-0 {
  width: 20%;
  background: #fc8181;
}

.strength-1 {
  width: 40%;
  background: #f6ad55;
}

.strength-2 {
  width: 60%;
  background: #f6e05e;
}

.strength-3 {
  width: 80%;
  background: #68d391;
}

.strength-4 {
  width: 100%;
  background: #38a169;
}

.password-strength small {
  font-size: 12px;
  color: #718096;
}

.error-message {
  color: #e53e3e;
  font-size: 12px;
  margin-top: 4px;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 15px;
  margin-top: 30px;
  padding-top: 20px;
  border-top: 1px solid #e2e8f0;
}

.btn-primary, .btn-secondary {
  padding: 12px 24px;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.btn-primary {
  background: #667eea;
  color: white;
  min-width: 120px;
}

.btn-primary:hover:not(:disabled) {
  background: #5a67d8;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
  background: #e2e8f0;
  color: #4a5568;
  min-width: 120px;
}

.btn-secondary:hover:not(:disabled) {
  background: #cbd5e0;
  transform: translateY(-2px);
}

.btn-primary:disabled,
.btn-secondary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.success-message {
  background: #f0fff4;
  border: 1px solid #9ae6b4;
  border-radius: 8px;
  padding: 25px;
  margin-top: 30px;
  display: flex;
  align-items: flex-start;
  gap: 20px;
  animation: fadeIn 0.5s;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}

.success-icon {
  font-size: 40px;
  flex-shrink: 0;
}

.success-message h3 {
  margin: 0 0 10px 0;
  color: #22543d;
}

.success-message p {
  margin: 0 0 20px 0;
  color: #38a169;
}

.success-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.error-message-card {
  background: #fff5f5;
  border: 1px solid #fed7d7;
  border-radius: 8px;
  padding: 25px;
  margin-top: 30px;
  display: flex;
  align-items: flex-start;
  gap: 20px;
  animation: fadeIn 0.5s;
}

.error-icon {
  font-size: 40px;
  flex-shrink: 0;
}

.error-message-card h3 {
  margin: 0 0 10px 0;
  color: #c53030;
}

.error-message-card p {
  margin: 0 0 20px 0;
  color: #e53e3e;
}

@media (max-width: 768px) {
  .crear-usuario-container {
    padding: 15px;
  }
  
  .header {
    flex-direction: column;
    align-items: stretch;
  }
  
  .form-row {
    flex-direction: column;
    gap: 15px;
  }
  
  .form-actions {
    flex-direction: column;
  }
  
  .form-actions button {
    width: 100%;
  }
  
  .success-message,
  .error-message-card {
    flex-direction: column;
    text-align: center;
    align-items: center;
  }
}
</style>
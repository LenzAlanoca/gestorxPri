<template>
  <div class="editar-usuario-container">
    <div class="header">
      <h1>Editar Usuario</h1>
      <router-link to="/admin/usuarios" class="btn-secondary">
        ← Volver a Usuarios
      </router-link>
    </div>
    
    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <p>Cargando información del usuario...</p>
    </div>
    
    <div v-else-if="error" class="error-container">
      <div class="error-icon">❌</div>
      <h3>Error al cargar usuario</h3>
      <p>{{ error }}</p>
      <router-link to="/admin/usuarios" class="btn-primary">
        Volver a la lista
      </router-link>
    </div>
    
    <div v-else class="card">
      <form @submit.prevent="actualizarUsuario" class="user-form">
        <div class="user-header">
          <div class="avatar-large">
            {{ getInitials(usuario.nombre, usuario.apellido) }}
          </div>
          <div>
            <h2>{{ usuario.nombre }} {{ usuario.apellido }}</h2>
            <p class="user-email">{{ usuario.correo }}</p>
            <p class="user-info">
              <span class="badge" :class="getRoleClass(usuario.nombre_rol)">
                {{ usuario.nombre_rol }}
              </span>
              <span class="badge" :class="usuario.estado_usuario === 'activo' ? 'badge-success' : 'badge-danger'">
                {{ usuario.estado_usuario === 'activo' ? 'Activo' : 'Inactivo' }}
              </span>
            </p>
          </div>
        </div>
        
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
        
        <div class="form-section">
          <h3>Cambiar Contraseña (Opcional)</h3>
          <div class="form-row">
            <div class="form-group">
              <label for="nueva_password">Nueva Contraseña</label>
              <div class="password-input">
                <input 
                  :type="showPassword ? 'text' : 'password'" 
                  id="nueva_password" 
                  v-model="usuario.nueva_password"
                  placeholder="Dejar en blanco para no cambiar"
                >
                <button 
                  type="button" 
                  class="toggle-password"
                  @click="showPassword = !showPassword"
                >
                  {{ showPassword ? '🙈' : '👁️' }}
                </button>
              </div>
            </div>
            
            <div class="form-group">
              <label for="confirmar_password">Confirmar Contraseña</label>
              <input 
                :type="showConfirmPassword ? 'text' : 'password'" 
                id="confirmar_password" 
                v-model="usuario.confirmar_password"
                placeholder="Confirmar nueva contraseña"
              >
            </div>
          </div>
          <small class="text-muted">Complete solo si desea cambiar la contraseña del usuario.</small>
        </div>
        
        <div class="form-actions">
          <button 
            type="button" 
            @click="$router.push('/admin/usuarios')" 
            class="btn-secondary"
            :disabled="saving"
          >
            Cancelar
          </button>
          <button 
            type="submit" 
            class="btn-primary"
            :disabled="saving"
          >
            {{ saving ? 'Guardando...' : 'Guardar Cambios' }}
          </button>
        </div>
      </form>
      
      <div v-if="successMessage" class="success-message">
        <div class="success-icon">✅</div>
        <div>
          <h3>¡Cambios guardados!</h3>
          <p>{{ successMessage }}</p>
        </div>
      </div>
      
      <div v-if="errorMessage" class="error-message-card">
        <div class="error-icon">❌</div>
        <div>
          <h3>Error al guardar cambios</h3>
          <p>{{ errorMessage }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'

export default {
  props: {
    id: {
      type: [String, Number],
      required: true
    }
  },
  
  setup(props) {
    const route = useRoute()
    const router = useRouter()
    const usuario = ref({
      id_usuario: '',
      nombre: '',
      apellido: '',
      correo: '',
      id_rol: '',
      nombre_rol: '',
      estado_usuario: '',
      nueva_password: '',
      confirmar_password: ''
    })
    
    const errors = ref({})
    const loading = ref(true)
    const saving = ref(false)
    const successMessage = ref('')
    const errorMessage = ref('')
    const error = ref('')
    const showPassword = ref(false)
    const showConfirmPassword = ref(false)
    const roles = ref([])
    const currentUser = JSON.parse(localStorage.getItem('user') || '{}')
    
    const API_URL = 'http://localhost/gestorx/backend/api'
    
    const getAuthHeader = () => {
      const token = localStorage.getItem('token')
      return {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      }
    }
    
    const getInitials = (nombre, apellido) => {
      return `${nombre?.[0] || ''}${apellido?.[0] || ''}`.toUpperCase()
    }
    
    const getRoleClass = (rol) => {
      const classes = {
        'superadministrador': 'badge-purple',
        'administrador': 'badge-blue',
        'cajero': 'badge-green'
      }
      return classes[rol] || 'badge-gray'
    }
    
    const cargarUsuario = async () => {
      loading.value = true
      error.value = ''
      
      try {
        const response = await axios.get(
          `${API_URL}/usuarios.php?id=${props.id}`,
          getAuthHeader()
        )
        
        if (response.data.success) {
          usuario.value = response.data.data
        } else {
          error.value = response.data.message || 'Usuario no encontrado'
        }
      } catch (error) {
        console.error('Error al cargar usuario:', error)
        
        if (error.response && error.response.status === 401) {
          localStorage.removeItem('token')
          localStorage.removeItem('user')
          router.push('/login')
        } else {
          error.value = 'Error al cargar información del usuario'
        }
      } finally {
        loading.value = false
      }
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
      
      if (!usuario.value.id_rol) {
        errors.value.id_rol = 'Seleccione un rol'
        isValid = false
      }
      
      if (usuario.value.nueva_password && usuario.value.nueva_password !== usuario.value.confirmar_password) {
        errorMessage.value = 'Las contraseñas no coinciden'
        isValid = false
      }
      
      if (usuario.value.nueva_password && usuario.value.nueva_password.length < 6) {
        errorMessage.value = 'La contraseña debe tener al menos 6 caracteres'
        isValid = false
      }
      
      return isValid
    }
    
    const actualizarUsuario = async () => {
      if (!validarFormulario()) return
      
      saving.value = true
      successMessage.value = ''
      errorMessage.value = ''
      
      try {
        const userData = {
          id_usuario: usuario.value.id_usuario,
          nombre: usuario.value.nombre,
          apellido: usuario.value.apellido,
          correo: usuario.value.correo,
          id_rol: usuario.value.id_rol
        }
        
        const response = await axios.put(
          `${API_URL}/usuarios.php`,
          userData,
          getAuthHeader()
        )
        
        if (response.data.success) {
          successMessage.value = `Usuario "${userData.nombre} ${userData.apellido}" actualizado exitosamente.`
          
          // Si hay nueva contraseña, cambiarla
          if (usuario.value.nueva_password) {
            await cambiarPassword(usuario.value.nueva_password)
          }
          
          // Recargar datos actualizados
          await cargarUsuario()
        } else {
          errorMessage.value = response.data.message
        }
      } catch (error) {
        console.error('Error al actualizar usuario:', error)
        
        if (error.response) {
          if (error.response.status === 401) {
            localStorage.removeItem('token')
            localStorage.removeItem('user')
            router.push('/login')
          } else if (error.response.data && error.response.data.message) {
            errorMessage.value = error.response.data.message
          } else {
            errorMessage.value = 'Error al actualizar usuario'
          }
        } else {
          errorMessage.value = 'Error de conexión con el servidor'
        }
      } finally {
        saving.value = false
      }
    }
    
    const cambiarPassword = async (nuevaPassword) => {
      try {
        const response = await axios.post(
          `${API_URL}/auth.php`,
          {
            action: 'change_password',
            id_usuario: usuario.value.id_usuario,
            nueva_password: nuevaPassword
          },
          getAuthHeader()
        )
        
        if (response.data.success) {
          successMessage.value += ' Contraseña actualizada correctamente.'
        }
      } catch (error) {
        console.error('Error al cambiar contraseña:', error)
        errorMessage.value = 'Usuario actualizado pero error al cambiar contraseña.'
      }
    }
    
    onMounted(async () => {
      await Promise.all([cargarUsuario(), cargarRoles()])
    })
    
    return {
      usuario,
      errors,
      loading,
      saving,
      successMessage,
      errorMessage,
      error,
      showPassword,
      showConfirmPassword,
      roles,
      currentUser,
      getInitials,
      getRoleClass,
      actualizarUsuario
    }
  }
}
</script>

<style scoped>
.editar-usuario-container {
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

.loading-container {
  text-align: center;
  padding: 60px 20px;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 5px solid #e2e8f0;
  border-top-color: #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 20px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.loading-container p {
  color: #718096;
  font-size: 16px;
}

.error-container {
  text-align: center;
  padding: 60px 20px;
  background: #fff5f5;
  border-radius: 10px;
  border: 1px solid #fed7d7;
}

.error-icon {
  font-size: 60px;
  margin-bottom: 20px;
  color: #fc8181;
}

.error-container h3 {
  color: #c53030;
  margin-bottom: 10px;
}

.error-container p {
  color: #e53e3e;
  margin-bottom: 30px;
}

.card {
  background: white;
  border-radius: 10px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  padding: 30px;
}

.user-header {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-bottom: 30px;
  padding-bottom: 20px;
  border-bottom: 1px solid #e2e8f0;
}

.avatar-large {
  width: 80px;
  height: 80px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: bold;
  font-size: 28px;
  flex-shrink: 0;
}

.user-header h2 {
  margin: 0 0 5px 0;
  color: #2d3748;
  font-size: 24px;
}

.user-email {
  margin: 0 0 10px 0;
  color: #718096;
  font-size: 14px;
}

.user-info {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.badge-purple {
  background: #d6bcfa;
  color: #553c9a;
}

.badge-blue {
  background: #bee3f8;
  color: #2c5282;
}

.badge-green {
  background: #c6f6d5;
  color: #22543d;
}

.badge-gray {
  background: #e2e8f0;
  color: #4a5568;
}

.badge-success {
  background: #c6f6d5;
  color: #22543d;
}

.badge-danger {
  background: #fed7d7;
  color: #c53030;
}

.user-form {
  display: flex;
  flex-direction: column;
  gap: 25px;
}

.form-section {
  background: #f7fafc;
  padding: 20px;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
}

.form-section h3 {
  margin: 0 0 15px 0;
  color: #4a5568;
  font-size: 16px;
}

.text-muted {
  color: #718096;
  font-size: 13px;
  display: block;
  margin-top: 10px;
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
  min-width: 120px;
}

.btn-primary {
  background: #667eea;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #5a67d8;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
  background: #e2e8f0;
  color: #4a5568;
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
  margin: 0;
  color: #38a169;
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
  margin: 0;
  color: #e53e3e;
}

@media (max-width: 768px) {
  .editar-usuario-container {
    padding: 15px;
  }
  
  .header {
    flex-direction: column;
    align-items: stretch;
  }
  
  .user-header {
    flex-direction: column;
    text-align: center;
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
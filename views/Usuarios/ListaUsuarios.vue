<template>
  <div class="usuarios-container">
    <div class="header-actions">
      <h1>Gestión de Usuarios</h1>
      <div class="actions">
        <router-link to="/admin/usuarios/crear" class="btn-primary">
          <span class="icon">+</span>
          Nuevo Usuario
        </router-link>
        <button @click="refreshUsers" class="btn-secondary" :disabled="loading">
          <span class="icon">🔄</span>
          Actualizar
        </button>
      </div>
    </div>
    
    <div class="card">
      <div class="table-responsive">
        <table class="usuarios-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Correo</th>
              <th>Rol</th>
              <th>Estado</th>
              <th>Último Acceso</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody v-if="usuarios.length > 0">
            <tr v-for="usuario in usuarios" :key="usuario.id_usuario">
              <td class="text-center">{{ usuario.id_usuario }}</td>
              <td>
                <div class="user-info-cell">
                  <div class="avatar-small">{{ getInitials(usuario.nombre, usuario.apellido) }}</div>
                  <div>
                    <strong>{{ usuario.nombre }} {{ usuario.apellido }}</strong>
                    <small>ID: {{ usuario.id_usuario }}</small>
                  </div>
                </div>
              </td>
              <td>{{ usuario.correo }}</td>
              <td>
                <span class="badge" :class="getRoleClass(usuario.nombre_rol)">
                  {{ usuario.nombre_rol }}
                </span>
              </td>
              <td>
                <span class="badge" :class="usuario.estado_usuario === 'activo' ? 'badge-success' : 'badge-danger'">
                  {{ usuario.estado_usuario === 'activo' ? 'Activo' : 'Inactivo' }}
                </span>
              </td>
              <td>
                {{ formatDate(usuario.ultimo_acceso) || 'Nunca' }}
              </td>
              <td>
                <div class="action-buttons">
                  <router-link 
                    :to="{ name: 'EditarUsuario', params: { id: usuario.id_usuario } }" 
                    class="btn-icon btn-edit"
                    title="Editar"
                  >
                    ✏️
                  </router-link>
                  
                  <button 
                    v-if="usuario.estado_usuario === 'activo' && user.rol === 'superadministrador'"
                    @click="confirmDeactivate(usuario)"
                    class="btn-icon btn-delete"
                    title="Desactivar"
                    :disabled="loading"
                  >
                    🗑️
                  </button>
                  
                  <button 
                    v-else-if="usuario.estado_usuario === 'inactivo' && user.rol === 'superadministrador'"
                    @click="activateUser(usuario.id_usuario)"
                    class="btn-icon btn-activate"
                    title="Activar"
                    :disabled="loading"
                  >
                    ✅
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
          <tbody v-else>
            <tr>
              <td colspan="7" class="empty-state">
                <div class="empty-content">
                  <span class="icon">👥</span>
                  <h3>No hay usuarios</h3>
                  <p>Crea tu primer usuario para comenzar</p>
                  <router-link to="/admin/usuarios/crear" class="btn-primary">
                    Crear Usuario
                  </router-link>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <div v-if="loading" class="loading-overlay">
        <div class="spinner"></div>
        <p>Cargando usuarios...</p>
      </div>
    </div>
    
    <!-- Modal de confirmación -->
    <div v-if="showConfirmModal" class="modal-overlay">
      <div class="modal">
        <div class="modal-header">
          <h3>Confirmar Desactivación</h3>
          <button @click="showConfirmModal = false" class="btn-close">×</button>
        </div>
        <div class="modal-body">
          <p>¿Estás seguro de desactivar al usuario <strong>{{ selectedUser?.nombre }} {{ selectedUser?.apellido }}</strong>?</p>
          <p class="text-muted">El usuario no podrá acceder al sistema pero su información se mantendrá.</p>
        </div>
        <div class="modal-footer">
          <button @click="showConfirmModal = false" class="btn-secondary">
            Cancelar
          </button>
          <button @click="deactivateUser" class="btn-danger" :disabled="loading">
            {{ loading ? 'Desactivando...' : 'Sí, Desactivar' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

export default {
  setup() {
    const router = useRouter()
    const usuarios = ref([])
    const loading = ref(false)
    const showConfirmModal = ref(false)
    const selectedUser = ref(null)
    const user = JSON.parse(localStorage.getItem('user') || '{}')
    
    const API_URL = 'http://localhost/gestorx/backend/api'
    
    const getAuthHeader = () => {
      const token = localStorage.getItem('token')
      return {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      }
    }
    
    const fetchUsuarios = async () => {
      loading.value = true
      try {
        const response = await axios.get(`${API_URL}/usuarios.php`, getAuthHeader())
        
        if (response.data.success) {
          usuarios.value = response.data.data
        } else {
          console.error('Error:', response.data.message)
        }
      } catch (error) {
        console.error('Error al cargar usuarios:', error)
        if (error.response && error.response.status === 401) {
          localStorage.removeItem('token')
          localStorage.removeItem('user')
          router.push('/login')
        }
      } finally {
        loading.value = false
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
    
    const formatDate = (dateString) => {
      if (!dateString) return ''
      const date = new Date(dateString)
      return date.toLocaleDateString('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    }
    
    const refreshUsers = () => {
      fetchUsuarios()
    }
    
    const confirmDeactivate = (usuario) => {
      selectedUser.value = usuario
      showConfirmModal.value = true
    }
    
    const deactivateUser = async () => {
      if (!selectedUser.value) return
      
      loading.value = true
      try {
        const response = await axios.delete(
          `${API_URL}/usuarios.php?id=${selectedUser.value.id_usuario}`,
          getAuthHeader()
        )
        
        if (response.data.success) {
          await fetchUsuarios()
          showConfirmModal.value = false
        } else {
          alert('Error: ' + response.data.message)
        }
      } catch (error) {
        console.error('Error al desactivar usuario:', error)
        alert('Error al desactivar usuario')
      } finally {
        loading.value = false
      }
    }
    
    const activateUser = async (idUsuario) => {
      // Implementar activación si es necesario
      alert('Función de activación no implementada aún')
    }
    
    onMounted(() => {
      fetchUsuarios()
    })
    
    return {
      usuarios,
      loading,
      showConfirmModal,
      selectedUser,
      user,
      getInitials,
      getRoleClass,
      formatDate,
      refreshUsers,
      confirmDeactivate,
      deactivateUser,
      activateUser
    }
  }
}
</script>

<style scoped>
.usuarios-container {
  padding: 20px;
}

.header-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
  flex-wrap: wrap;
  gap: 20px;
}

.header-actions h1 {
  margin: 0;
  color: #333;
  font-size: 28px;
}

.actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.btn-primary, .btn-secondary, .btn-danger {
  padding: 10px 20px;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: all 0.3s;
  text-decoration: none;
}

.btn-primary {
  background: #667eea;
  color: white;
}

.btn-primary:hover {
  background: #5a67d8;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
  background: #e2e8f0;
  color: #4a5568;
}

.btn-secondary:hover {
  background: #cbd5e0;
  transform: translateY(-2px);
}

.btn-danger {
  background: #fc8181;
  color: white;
}

.btn-danger:hover {
  background: #f56565;
}

.btn-danger:disabled, .btn-primary:disabled, .btn-secondary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.card {
  background: white;
  border-radius: 10px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  overflow: hidden;
}

.table-responsive {
  overflow-x: auto;
}

.usuarios-table {
  width: 100%;
  border-collapse: collapse;
}

.usuarios-table thead {
  background: #f7fafc;
  border-bottom: 2px solid #e2e8f0;
}

.usuarios-table th {
  padding: 15px;
  text-align: left;
  font-weight: 600;
  color: #4a5568;
  text-transform: uppercase;
  font-size: 12px;
  letter-spacing: 0.5px;
}

.usuarios-table tbody tr {
  border-bottom: 1px solid #e2e8f0;
  transition: background 0.3s;
}

.usuarios-table tbody tr:hover {
  background: #f7fafc;
}

.usuarios-table td {
  padding: 15px;
  vertical-align: middle;
}

.text-center {
  text-align: center;
}

.user-info-cell {
  display: flex;
  align-items: center;
  gap: 12px;
}

.avatar-small {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: bold;
  font-size: 14px;
}

.user-info-cell div {
  display: flex;
  flex-direction: column;
}

.user-info-cell strong {
  color: #2d3748;
  font-size: 14px;
}

.user-info-cell small {
  color: #718096;
  font-size: 12px;
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

.action-buttons {
  display: flex;
  gap: 8px;
}

.btn-icon {
  width: 36px;
  height: 36px;
  border: none;
  border-radius: 6px;
  background: transparent;
  cursor: pointer;
  font-size: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s;
}

.btn-edit:hover {
  background: #bee3f8;
  color: #2c5282;
}

.btn-delete:hover {
  background: #fed7d7;
  color: #c53030;
}

.btn-activate:hover {
  background: #c6f6d5;
  color: #22543d;
}

.empty-state {
  padding: 60px 20px;
  text-align: center;
}

.empty-content {
  max-width: 400px;
  margin: 0 auto;
}

.empty-content .icon {
  font-size: 60px;
  margin-bottom: 20px;
  opacity: 0.5;
}

.empty-content h3 {
  color: #4a5568;
  margin-bottom: 10px;
}

.empty-content p {
  color: #718096;
  margin-bottom: 20px;
}

.loading-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.9);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  z-index: 10;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 5px solid #e2e8f0;
  border-top-color: #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 15px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.modal {
  background: white;
  border-radius: 10px;
  width: 100%;
  max-width: 500px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
  animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #e2e8f0;
}

.modal-header h3 {
  margin: 0;
  color: #2d3748;
}

.btn-close {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #718096;
  transition: color 0.3s;
}

.btn-close:hover {
  color: #4a5568;
}

.modal-body {
  padding: 20px;
}

.modal-body p {
  margin: 0 0 10px 0;
  color: #4a5568;
}

.text-muted {
  color: #718096 !important;
  font-size: 14px;
}

.modal-footer {
  padding: 20px;
  border-top: 1px solid #e2e8f0;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

@media (max-width: 768px) {
  .header-actions {
    flex-direction: column;
    align-items: stretch;
  }
  
  .actions {
    justify-content: center;
  }
  
  .modal-footer {
    flex-direction: column;
  }
  
  .modal-footer button {
    width: 100%;
  }
}
</style>
<template>
  <div class="admin-layout">
    <nav class="sidebar">
      <div class="logo">
        <h3>GestorX</h3>
      </div>
      
      <div class="user-info">
        <div class="avatar">
          {{ userInitials }}
        </div>
        <div class="user-details">
          <strong>{{ userName }}</strong>
          <small>{{ userRole }}</small>
          <small>{{ userEmpresa }}</small>
        </div>
      </div>
      
      <ul class="menu">
        <li>
          <router-link to="/admin/usuarios" class="menu-item">
            <span class="icon">👥</span>
            <span>Usuarios</span>
          </router-link>
        </li>
        <li>
          <a href="#" class="menu-item">
            <span class="icon">📦</span>
            <span>Productos</span>
          </a>
        </li>
        <li>
          <a href="#" class="menu-item">
            <span class="icon">💰</span>
            <span>Ventas</span>
          </a>
        </li>
        <li>
          <a href="#" class="menu-item">
            <span class="icon">📊</span>
            <span>Reportes</span>
          </a>
        </li>
        <li>
          <a href="#" class="menu-item">
            <span class="icon">⚙️</span>
            <span>Configuración</span>
          </a>
        </li>
      </ul>
      
      <button @click="logout" class="logout-btn">
        <span class="icon">🚪</span>
        <span>Cerrar Sesión</span>
      </button>
    </nav>
    
    <main class="content">
      <div class="content-header">
        <h1>{{ currentRouteName }}</h1>
        <div class="breadcrumb">
          <router-link to="/">Inicio</router-link>
          <span> / </span>
          <span>{{ currentRouteName }}</span>
        </div>
      </div>
      
      <div class="content-body">
        <router-view />
      </div>
    </main>
  </div>
</template>

<script>
import { computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'

export default {
  setup() {
    const router = useRouter()
    const route = useRoute()
    const user = JSON.parse(localStorage.getItem('user') || '{}')
    
    const userName = computed(() => `${user.nombre} ${user.apellido}`)
    const userRole = computed(() => user.rol)
    const userEmpresa = computed(() => user.empresa)
    const userInitials = computed(() => {
      return userName.value
        .split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase()
        .substring(0, 2)
    })
    
    const currentRouteName = computed(() => {
      const routeName = route.name
      const names = {
        'Usuarios': 'Gestión de Usuarios',
        'CrearUsuario': 'Crear Usuario',
        'EditarUsuario': 'Editar Usuario'
      }
      return names[routeName] || routeName
    })
    
    const logout = () => {
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      router.push('/login')
    }
    
    return {
      userName,
      userRole,
      userEmpresa,
      userInitials,
      currentRouteName,
      logout
    }
  }
}
</script>

<style scoped>
.admin-layout {
  display: flex;
  min-height: 100vh;
  background: #f5f5f5;
}

.sidebar {
  width: 250px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 20px;
  display: flex;
  flex-direction: column;
  box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
  z-index: 100;
}

.logo {
  text-align: center;
  margin-bottom: 30px;
  padding-bottom: 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.logo h3 {
  margin: 0;
  font-size: 24px;
  font-weight: 700;
  letter-spacing: 1px;
}

.user-info {
  display: flex;
  align-items: center;
  background: rgba(255, 255, 255, 0.1);
  padding: 15px;
  border-radius: 8px;
  margin-bottom: 30px;
  transition: background 0.3s;
}

.user-info:hover {
  background: rgba(255, 255, 255, 0.15);
}

.avatar {
  width: 45px;
  height: 45px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 15px;
  font-weight: bold;
  font-size: 16px;
}

.user-details {
  flex: 1;
}

.user-details strong {
  display: block;
  font-size: 14px;
  margin-bottom: 2px;
}

.user-details small {
  display: block;
  opacity: 0.8;
  font-size: 12px;
  margin-bottom: 2px;
}

.menu {
  list-style: none;
  padding: 0;
  margin: 0;
  flex: 1;
}

.menu li {
  margin-bottom: 5px;
}

.menu-item {
  display: flex;
  align-items: center;
  padding: 12px 15px;
  color: white;
  text-decoration: none;
  border-radius: 6px;
  transition: all 0.3s;
}

.menu-item:hover {
  background: rgba(255, 255, 255, 0.1);
  transform: translateX(5px);
}

.menu-item.router-link-active {
  background: rgba(255, 255, 255, 0.2);
  font-weight: 600;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}

.icon {
  margin-right: 10px;
  font-size: 18px;
}

.logout-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  background: rgba(255, 255, 255, 0.1);
  color: white;
  border: none;
  padding: 12px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.3s;
  margin-top: auto;
  font-size: 14px;
}

.logout-btn:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: translateY(-2px);
}

.content {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow-y: auto;
}

.content-header {
  background: white;
  padding: 20px 30px;
  border-bottom: 1px solid #eaeaea;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.content-header h1 {
  margin: 0 0 10px 0;
  color: #333;
  font-size: 24px;
}

.breadcrumb {
  color: #666;
  font-size: 14px;
}

.breadcrumb a {
  color: #667eea;
  text-decoration: none;
}

.breadcrumb a:hover {
  text-decoration: underline;
}

.breadcrumb span {
  color: #999;
}

.content-body {
  flex: 1;
  padding: 30px;
  overflow-y: auto;
}

@media (max-width: 768px) {
  .sidebar {
    width: 70px;
    padding: 15px 10px;
  }
  
  .logo h3 {
    font-size: 18px;
  }
  
  .user-info, .menu-item span:not(.icon), .logout-btn span:not(.icon) {
    display: none;
  }
  
  .avatar {
    margin: 0 auto;
    width: 35px;
    height: 35px;
  }
  
  .menu-item, .logout-btn {
    justify-content: center;
    padding: 12px;
  }
  
  .icon {
    margin: 0;
    font-size: 20px;
  }
  
  .content-header, .content-body {
    padding: 15px;
  }
}
</style>
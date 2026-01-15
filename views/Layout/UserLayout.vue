<template>
  <div class="user-layout">
    <nav class="user-navbar">
      <div class="nav-left">
        <h2>GestorX - Punto de Venta</h2>
      </div>
      <div class="nav-right">
        <div class="user-menu">
          <span class="user-greeting">Hola, {{ userName }}</span>
          <button @click="logout" class="btn-logout">Cerrar Sesión</button>
        </div>
      </div>
    </nav>
    
    <main class="user-content">
      <div class="welcome-message">
        <h1>👋 ¡Bienvenido {{ userName }}!</h1>
        <p>Estás en el módulo de punto de venta. Aquí puedes realizar ventas y consultar productos.</p>
      </div>
      
      <div class="quick-actions">
        <div class="action-card">
          <div class="action-icon">💰</div>
          <h3>Nueva Venta</h3>
          <p>Iniciar una nueva venta</p>
          <button class="btn-action">Iniciar</button>
        </div>
        
        <div class="action-card">
          <div class="action-icon">📦</div>
          <h3>Productos</h3>
          <p>Ver catálogo de productos</p>
          <button class="btn-action">Ver</button>
        </div>
        
        <div class="action-card">
          <div class="action-icon">👥</div>
          <h3>Clientes</h3>
          <p>Gestionar clientes</p>
          <button class="btn-action">Gestionar</button>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
import { computed } from 'vue'
import { useRouter } from 'vue-router'

export default {
  setup() {
    const router = useRouter()
    const user = JSON.parse(localStorage.getItem('user') || '{}')
    
    const userName = computed(() => user.nombre || 'Usuario')
    
    const logout = () => {
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      router.push('/login')
    }
    
    return {
      userName,
      logout
    }
  }
}
</script>

<style scoped>
.user-layout {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.user-navbar {
  background: white;
  padding: 15px 30px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.nav-left h2 {
  margin: 0;
  color: #333;
  font-size: 20px;
}

.user-menu {
  display: flex;
  align-items: center;
  gap: 20px;
}

.user-greeting {
  color: #666;
  font-weight: 500;
}

.btn-logout {
  background: #667eea;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  transition: background 0.3s;
}

.btn-logout:hover {
  background: #5a67d8;
}

.user-content {
  padding: 40px;
  max-width: 1200px;
  margin: 0 auto;
}

.welcome-message {
  background: white;
  border-radius: 10px;
  padding: 30px;
  margin-bottom: 40px;
  text-align: center;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.welcome-message h1 {
  margin: 0 0 15px 0;
  color: #333;
}

.welcome-message p {
  margin: 0;
  color: #666;
  font-size: 18px;
}

.quick-actions {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 30px;
}

.action-card {
  background: white;
  border-radius: 10px;
  padding: 30px;
  text-align: center;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s;
}

.action-card:hover {
  transform: translateY(-5px);
}

.action-icon {
  font-size: 50px;
  margin-bottom: 20px;
}

.action-card h3 {
  margin: 0 0 10px 0;
  color: #333;
}

.action-card p {
  margin: 0 0 20px 0;
  color: #666;
}

.btn-action {
  background: #667eea;
  color: white;
  border: none;
  padding: 12px 30px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  font-size: 16px;
  transition: all 0.3s;
  width: 100%;
}

.btn-action:hover {
  background: #5a67d8;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

@media (max-width: 768px) {
  .user-navbar {
    flex-direction: column;
    gap: 15px;
    text-align: center;
  }
  
  .user-content {
    padding: 20px;
  }
  
  .quick-actions {
    grid-template-columns: 1fr;
  }
}
</style>
import { createRouter, createWebHistory } from 'vue-router'
import Login from '../views/Login.vue'
import Register from '../views/Register.vue'
import Dashboard from '../views/Dashboard.vue'
import AdminLayout from '../views/Layout/AdminLayout.vue'
import UserLayout from '../views/Layout/UserLayout.vue'
import ListaUsuarios from '../views/Usuarios/ListaUsuarios.vue'
import CrearUsuario from '../views/Usuarios/CrearUsuario.vue'
import EditarUsuario from '../views/Usuarios/EditarUsuario.vue'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: Login
  },
  {
    path: '/register',
    name: 'Register',
    component: Register
  },
  {
    path: '/',
    name: 'Dashboard',
    component: Dashboard,
    meta: { requiresAuth: true }
  },
  {
    path: '/admin',
    component: AdminLayout,
    meta: { requiresAuth: true, role: ['superadministrador', 'administrador'] },
    children: [
      {
        path: 'usuarios',
        name: 'Usuarios',
        component: ListaUsuarios
      },
      {
        path: 'usuarios/crear',
        name: 'CrearUsuario',
        component: CrearUsuario
      },
      {
        path: 'usuarios/editar/:id',
        name: 'EditarUsuario',
        component: EditarUsuario,
        props: true
      }
    ]
  },
  {
    path: '/user',
    component: UserLayout,
    meta: { requiresAuth: true, role: ['cajero'] }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')
  const user = JSON.parse(localStorage.getItem('user') || '{}')
  
  if (to.meta.requiresAuth && !token) {
    next('/login')
  } else if (to.meta.role && !to.meta.role.includes(user.rol)) {
    next('/')
  } else {
    next()
  }
})

export default router
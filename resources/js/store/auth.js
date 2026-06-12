import { reactive, computed } from 'vue'
import axios from '../services/axios'

const state = reactive({
    user: null,
    token: localStorage.getItem('token'),
    isAuthenticated: !!localStorage.getItem('token'),
})

export const useAuthStore = () => {
    const login = async (email, password) => {
        try {
            const response = await axios.post('/v1/login', { email, password })
            const { user, token } = response.data
            
            state.user = user
            state.token = token
            state.isAuthenticated = true
            
            localStorage.setItem('token', token)
            axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
            
            return { success: true }
        } catch (error) {
            return { success: false, message: error.response?.data?.message || 'Error de autenticación' }
        }
    }
    
    const logout = async () => {
        try {
            await axios.post('/v1/logout')
        } catch (error) {
            console.error('Logout error:', error)
        } finally {
            state.user = null
            state.token = null
            state.isAuthenticated = false
            localStorage.removeItem('token')
            delete axios.defaults.headers.common['Authorization']
        }
    }
    
    const checkAuth = () => {
        if (state.token) {
            axios.defaults.headers.common['Authorization'] = `Bearer ${state.token}`
            axios.get('/v1/user')
                .then(response => {
                    state.user = response.data
                    state.isAuthenticated = true
                })
                .catch(() => {
                    logout()
                })
        }
    }
    
    const hasRole = (roles) => {
        if (!state.user) return false
        return roles.includes(state.user.role)
    }
    
    return {
        user: computed(() => state.user),
        token: computed(() => state.token),
        isAuthenticated: computed(() => state.isAuthenticated),
        login,
        logout,
        checkAuth,
        hasRole,
    }
}
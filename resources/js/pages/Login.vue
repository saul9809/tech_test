<template>
    <div class="login-container">
        <div class="login-card">
            <h1>TCG Engineering Hub</h1>
            <form @submit.prevent="handleLogin">
                <div class="form-group">
                    <input type="email" v-model="email" placeholder="Email" required>
                </div>
                <div class="form-group">
                    <input type="password" v-model="password" placeholder="Password" required>
                </div>
                <div v-if="error" class="error-message">{{ error }}</div>
                <button type="submit" :disabled="loading">
                    {{ loading ? 'Ingresando...' : 'Ingresar' }}
                </button>
            </form>
            <div class="demo-credentials">
                <p>Usuarios de prueba:</p>
                <ul>
                    <li>admin@tcg.com / password</li>
                    <li>pm@tcg.com / password</li>
                    <li>engineer@tcg.com / password</li>
                    <li>viewer@tcg.com / password</li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()
const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')

const handleLogin = async () => {
    loading.value = true
    error.value = ''
    
    const result = await authStore.login(email.value, password.value)
    
    if (result.success) {
        router.push('/')
    } else {
        error.value = result.message
    }
    
    loading.value = false
}
</script>

<style scoped>
.login-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.login-card {
    background: white;
    padding: 2rem;
    border-radius: 0.5rem;
    width: 100%;
    max-width: 400px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}
h1 {
    text-align: center;
    margin-bottom: 2rem;
    color: #333;
}
.form-group {
    margin-bottom: 1rem;
}
input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 0.25rem;
    font-size: 1rem;
}
button {
    width: 100%;
    padding: 0.75rem;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 0.25rem;
    font-size: 1rem;
    cursor: pointer;
}
button:disabled {
    opacity: 0.5;
}
.error-message {
    color: #e53e3e;
    margin-bottom: 1rem;
    text-align: center;
}
.demo-credentials {
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 1px solid #eee;
    font-size: 0.875rem;
    color: #666;
}
.demo-credentials ul {
    margin-top: 0.5rem;
    padding-left: 1.5rem;
}
</style>
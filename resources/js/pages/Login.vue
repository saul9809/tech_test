<template>
    <div class="login-container">
        <div class="login-card">
            <h1>TCG Engineering Hub</h1>
            <form @submit.prevent="handleLogin">
                <div class="form-group">
                    <input 
                        type="email" 
                        v-model="email" 
                        placeholder="Email" 
                        required
                        :disabled="loading"
                    >
                </div>
                <div class="form-group">
                    <input 
                        type="password" 
                        v-model="password" 
                        placeholder="Password" 
                        required
                        :disabled="loading"
                    >
                </div>
                <div v-if="error" class="error-message">{{ error }}</div>
                <button type="submit" :disabled="loading" class="login-btn">
                    {{ loading ? 'Ingresando...' : 'Ingresar' }}
                </button>
            </form>
            <div class="demo-credentials">
                <p>Usuarios de prueba:</p>
                <ul>
                    <li><strong>admin@tcg.com</strong> / password (Admin)</li>
                    <li><strong>pm@tcg.com</strong> / password (PM)</li>
                    <li><strong>engineer@tcg.com</strong> / password (Engineer)</li>
                    <li><strong>viewer@tcg.com</strong> / password (Viewer)</li>
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
        router.push('/projects')
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
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

h1 {
    text-align: center;
    margin-bottom: 2rem;
    color: #2d3748;
    font-size: 1.5rem;
}

.form-group {
    margin-bottom: 1rem;
}

input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.25rem;
    font-size: 1rem;
    transition: border-color 0.2s;
}

input:focus {
    outline: none;
    border-color: #667eea;
}

.login-btn {
    width: 100%;
    padding: 0.75rem;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 0.25rem;
    font-size: 1rem;
    cursor: pointer;
    transition: background 0.2s;
}

.login-btn:hover:not(:disabled) {
    background: #5a67d8;
}

.login-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.error-message {
    color: #e53e3e;
    margin-bottom: 1rem;
    text-align: center;
    font-size: 0.875rem;
}

.demo-credentials {
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 1px solid #e2e8f0;
    font-size: 0.75rem;
    color: #718096;
}

.demo-credentials ul {
    margin-top: 0.5rem;
    padding-left: 1rem;
}

.demo-credentials li {
    margin: 0.25rem 0;
}
</style>
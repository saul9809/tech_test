import axios from 'axios'

const axiosInstance = axios.create({
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
})

axiosInstance.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            localStorage.removeItem('token')
            delete axiosInstance.defaults.headers.common['Authorization']
            window.location.href = '/login'
        }
        return Promise.reject(error)
    }
)

export default axiosInstance
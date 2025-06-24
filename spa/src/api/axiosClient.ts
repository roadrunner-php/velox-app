import axios from 'axios'

const axiosClient = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL,
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
})

axiosClient.interceptors.response.use(
  (response) => response,
  (error) => {
    console.error('[API Error]', error)
    return Promise.reject(error)
  },
)

export default axiosClient

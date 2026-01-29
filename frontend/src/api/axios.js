import axios from 'axios';

const api = axios.create({
    baseURL: import.meta.env.VITE_API_URL,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});

// Intercepteur pour ajouter le token JWT
api.interceptors.request.use((config) => {
    const token = localStorage.getItem('token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// Intercepteur pour gérer les erreurs 401
// IMPORTANT: Ne pas déconnecter sur les routes d'auth (login, register)
api.interceptors.response.use(
    (response) => response,
    (error) => {
        const isAuthRoute = error.config?.url?.includes('/login') ||
            error.config?.url?.includes('/register') ||
            error.config?.url?.includes('/students') ||
            error.config?.url?.includes('/teachers');

        // Ne déconnecte que si c'est un vrai 401 sur une route protégée
        // et qu'on a un token (donc on était censé être connecté)
        if (error.response?.status === 401 && !isAuthRoute && localStorage.getItem('token')) {
            console.warn('Session expirée, déconnexion...');
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            window.location.href = '/login';
        }
        return Promise.reject(error);
    }
);

export default api;

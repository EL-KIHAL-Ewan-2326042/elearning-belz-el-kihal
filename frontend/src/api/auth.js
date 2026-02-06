import api from './axios';

export const login = async (email, password) => {
    const response = await api.post('/api/login_check', {
        username: email,
        password
    });
    return response.data;
};

export const getCurrentUser = async () => {
    const response = await api.get('/api/me');
    return response.data;
};

/**
 * Inscription via API avec retour JWT direct
 * @param {Object} userData - Données de l'utilisateur
 * @param {string} userType - 'student' ou 'teacher'
 * @returns {Promise<{token: string, user: Object}>}
 */
export const register = async (userData, userType = 'student') => {
    const endpoint = userType === 'teacher' ? '/api/register/teacher' : '/api/register/student';
    const response = await api.post(endpoint, userData);
    return response.data;
};

/**
 * Inscription legacy (pour compatibilité)
 * @deprecated Utiliser register() à la place
 */
export const registerLegacy = async (userData, endpoint = '/api/students') => {
    const response = await api.post(endpoint, userData);
    return response.data;
};

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

export const register = async (userData, endpoint = '/api/students') => {
    // Post to endpoint for registration (students or teachers)
    const response = await api.post(endpoint, userData);
    return response.data;
};

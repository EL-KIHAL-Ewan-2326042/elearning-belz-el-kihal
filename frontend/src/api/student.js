import api from './axios';

export const getMyStats = async () => {
    const response = await api.get('/api/me/stats');
    return response.data;
};

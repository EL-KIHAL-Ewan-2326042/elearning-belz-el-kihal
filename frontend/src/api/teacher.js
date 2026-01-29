import api from './axios';

export const getStudents = async () => {
    const response = await api.get('/teacher/students');
    return response.data;
};

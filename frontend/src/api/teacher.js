import api from './axios';

export const getStudents = async () => {
    const response = await api.get('/teacher/students');
    return response.data;
};

export const getStudentAnalytics = async (id) => {
    const response = await api.get(`/teacher/students/${id}/analytics`);
    return response.data;
};

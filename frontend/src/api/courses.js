import api from './axios';

export const getAllCourses = async () => {
    const response = await api.get('/api/courses');
    return response.data['hydra:member'] || response.data;
};

export const getCourseById = async (id) => {
    const response = await api.get(`/api/courses/${id}`);
    return response.data;
};

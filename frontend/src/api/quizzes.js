import api from './axios';

export const getQuizzesByCourse = async (courseId) => {
    const response = await api.get(`/api/quizzes?course=${courseId}`);
    return response.data['hydra:member'] || response.data;
};

export const getQuizById = async (id) => {
    const response = await api.get(`/api/quizzes/${id}`);
    return response.data;
};

export const submitQuizAttempt = async (quizId, answers, timeSpent) => {
    const response = await api.post('/api/quiz_attempts', {
        quiz: `/api/quizzes/${quizId}`,
        answers,
        timeSpentSeconds: timeSpent,
    });
    return response.data;
};

export const getMyResults = async (studentId) => {
    const response = await api.get(`/api/quiz_attempts?student=${studentId}`);
    return response.data['hydra:member'] || response.data;
};

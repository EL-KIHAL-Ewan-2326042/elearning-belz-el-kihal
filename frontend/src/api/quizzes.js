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
    // API Platform often requires the IRI for relation filters
    const validId = studentId.toString().includes('/api/') ? studentId : `/api/students/${studentId}`;
    try {
        const response = await api.get(`/api/quiz_attempts?student=${validId}`);
        return response.data['hydra:member'] || response.data;
    } catch (e) {
        // Fallback: try with raw ID if IRI fails (though IRI is standard)
        if (!studentId.toString().includes('/api/')) {
            const responseRetry = await api.get(`/api/quiz_attempts?student=${studentId}`);
            return responseRetry.data['hydra:member'] || responseRetry.data;
        }
        throw e;
    }
};

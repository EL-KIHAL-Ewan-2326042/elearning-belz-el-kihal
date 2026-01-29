import { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import api from '../api/axios';
import Navbar from '../components/Navbar';
import ScoreBadge from '../components/ScoreBadge';

import LoadingSpinner from '../components/LoadingSpinner';

export default function QuizAttemptDetail() {
    const { id } = useParams();
    const [attempt, setAttempt] = useState(null);
    const [quiz, setQuiz] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');

    useEffect(() => {
        const fetchData = async () => {
            try {
                // 1. Fetch Attempt
                const attemptRes = await api.get(`/api/quiz_attempts/${id}`);
                const attemptData = attemptRes.data;
                setAttempt(attemptData);

                // 2. Fetch Quiz details
                // Robust extraction of Quiz ID or URL
                console.log('Attempt Data:', attemptData);

                let quizUrl = null;
                if (typeof attemptData.quiz === 'string') {
                    quizUrl = attemptData.quiz;
                } else if (attemptData.quiz?.['@id']) {
                    quizUrl = attemptData.quiz['@id'];
                } else if (attemptData.quiz?.id) {
                    quizUrl = `/api/quizzes/${attemptData.quiz.id}`;
                }

                if (!quizUrl) {
                    throw new Error("Impossible de trouver le lien vers le QCM.");
                }

                console.log('Fetching Quiz from:', quizUrl);
                const quizRes = await api.get(quizUrl);
                console.log('Quiz Data:', quizRes.data);
                setQuiz(quizRes.data);

            } catch (err) {
                console.error('Detail Load Error:', err);
                setError("Erreur lors du chargement des détails.");
            } finally {
                setLoading(false);
            }
        };

        fetchData();
    }, [id]);

    if (loading) {
        return (
            <div className="min-h-screen bg-light">
                <Navbar />
                <div className="flex justify-center items-center h-64">
                    <LoadingSpinner fullScreen={false} />
                </div>
            </div>
        );
    }

    if (error || !attempt || !quiz) {
        return (
            <div className="min-h-screen bg-light">
                <Navbar />
                <div className="max-w-4xl mx-auto px-4 py-8">
                    <div className="bg-red-100 text-red-700 p-4 rounded-lg">
                        {error || "Impossible de charger les données."}
                    </div>
                </div>
            </div>
        );
    }

    // Helper to find the student's answer for a question
    const getStudentAnswerId = (questionId) => {
        if (!attempt?.answers) return null;
        return attempt.answers[questionId];
    };

    if (!quiz.questions) {
        return <div className="p-8 text-center text-red-600">Erreur: Les questions du QCM n'ont pas pu être chargées.</div>;
    }

    return (
        <div className="min-h-screen bg-light">
            <Navbar />
            <div className="max-w-4xl mx-auto px-4 py-8">

                {/* Header / Breadcrumb */}
                <div className="mb-6">
                    <Link to="/results" className="text-gray-500 hover:text-primary transition">
                        ← Retour à mes résultats
                    </Link>
                </div>

                <div className="bg-white rounded-2xl shadow-xl overflow-hidden mb-8">
                    <div className="hero-gradient p-8 text-white text-center">
                        <h1 className="text-3xl font-bold mb-2">{quiz.title}</h1>
                        <p className="opacity-90 text-lg">Détails de votre tentative</p>

                        <div className="flex justify-center items-center gap-6 mt-6">
                            <div className="bg-white/20 backdrop-blur-sm px-6 py-3 rounded-xl">
                                <div className="text-sm opacity-80">Score</div>
                                <div className="text-2xl font-bold">
                                    {attempt.score} / {attempt.maxScore}
                                </div>
                            </div>
                            <div className="bg-white/20 backdrop-blur-sm px-6 py-3 rounded-xl">
                                <div className="text-sm opacity-80">Pourcentage</div>
                                <div className="text-2xl font-bold">
                                    {Math.round((attempt.score / attempt.maxScore) * 100)}%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Version Warning */}
                {quiz.updatedAt && attempt.submittedAt && new Date(quiz.updatedAt) > new Date(attempt.submittedAt) && (
                    <div className="bg-orange-50 border-l-4 border-orange-500 p-4 mb-8 rounded-r shadow-sm">
                        <div className="flex">
                            <div className="flex-shrink-0">
                                <svg className="h-5 w-5 text-orange-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fillRule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clipRule="evenodd" />
                                </svg>
                            </div>
                            <div className="ml-3">
                                <p className="text-sm text-orange-700">
                                    <span className="font-bold">Attention :</span> Ce QCM a été modifié le {new Date(quiz.updatedAt).toLocaleDateString()} à {new Date(quiz.updatedAt).toLocaleTimeString()}, après votre tentative. Le contenu des questions ou les réponses attendues peuvent avoir changé.
                                </p>
                            </div>
                        </div>
                    </div>
                )}

                {/* Questions Breakdown */}
                <div className="space-y-6">
                    {quiz.questions.map((question, index) => {
                        const studentAnsRaw = getStudentAnswerId(question.id);
                        // Ensure we work with an array for comparison logic
                        const studentAnsIds = Array.isArray(studentAnsRaw) ? studentAnsRaw : (studentAnsRaw ? [studentAnsRaw] : []);

                        // Find all selected answers
                        const studentAnswers = question.answers.filter(a => studentAnsIds.includes(a.id));

                        // Find all correct answers
                        const correctAnswers = question.answers.filter(a => a.isCorrect === true || a.correct === true);
                        const correctAnsIds = correctAnswers.map(a => a.id);

                        // Determine if correct
                        // For multiple choice: must have ALL correct answers and NO incorrect answers
                        // For single choice: just match ID
                        let isCorrect = false;
                        if (question.isMultiple) {
                            isCorrect = studentAnsIds.length === correctAnsIds.length &&
                                studentAnsIds.every(id => correctAnsIds.includes(id));
                        } else {
                            // Fallback for legacy single choice
                            isCorrect = studentAnsIds.length > 0 && correctAnsIds.includes(studentAnsIds[0]);
                        }

                        return (
                            <div key={question.id} className={`bg-white rounded-xl shadow p-6 border-l-8 ${isCorrect ? 'border-success' : 'border-danger'}`}>
                                <div className="flex gap-4">
                                    <div className={`flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full font-bold text-white ${isCorrect ? 'bg-success' : 'bg-danger'}`}>
                                        {index + 1}
                                    </div>
                                    <div className="flex-grow">
                                        <h3 className="text-lg font-bold text-gray-800 mb-4">{question.content}</h3>

                                        <div className="space-y-3">
                                            {/* Student's Answer */}
                                            <div className={`p-3 rounded-lg border flex items-center justify-between ${isCorrect
                                                ? 'bg-green-50 border-green-200 text-green-800'
                                                : 'bg-red-50 border-red-200 text-red-800'
                                                }`}>
                                                <div>
                                                    <span className="font-semibold text-xs uppercase opacity-70 block mb-1">
                                                        Votre réponse
                                                    </span>
                                                    {studentAnswers.length > 0 ? (
                                                        <ul className="list-disc list-inside">
                                                            {studentAnswers.map(a => (
                                                                <li key={a.id}>{a.content}</li>
                                                            ))}
                                                        </ul>
                                                    ) : (
                                                        <span className="italic">Pas de réponse</span>
                                                    )}
                                                </div>
                                                <div className="text-2xl">
                                                    {isCorrect ? '✓' : '✗'}
                                                </div>
                                            </div>

                                            {/* Correct Answer (if wrong) */}
                                            {!isCorrect && (
                                                <div className="p-3 rounded-lg bg-blue-50 border border-blue-200 text-blue-800">
                                                    <span className="font-semibold text-xs uppercase opacity-70 block mb-1">
                                                        La bonne réponse
                                                    </span>
                                                    <ul className="list-disc list-inside">
                                                        {correctAnswers.map(a => (
                                                            <li key={a.id}>{a.content}</li>
                                                        ))}
                                                    </ul>
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        );
                    })}
                </div>

            </div>
        </div>
    );
}

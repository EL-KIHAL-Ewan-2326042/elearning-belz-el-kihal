import { useState, useEffect } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import { getQuizById, submitQuizAttempt } from '../api/quizzes';
import Navbar from '../components/Navbar';
import ProgressBar from '../components/ProgressBar';

export default function Quiz() {
    const { id } = useParams();
    const navigate = useNavigate();

    const [quiz, setQuiz] = useState(null);
    const [currentQuestion, setCurrentQuestion] = useState(0);
    const [selectedAnswers, setSelectedAnswers] = useState({});
    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    const [error, setError] = useState('');
    const [startTime] = useState(Date.now());

    useEffect(() => {
        const fetchQuiz = async () => {
            try {
                const data = await getQuizById(id);
                setQuiz(data);
            } catch (err) {
                setError('Erreur lors du chargement du QCM');
                console.error('Erreur:', err);
            } finally {
                setLoading(false);
            }
        };

        fetchQuiz();
    }, [id]);

    const handleSelectAnswer = (questionId, answerId) => {
        setSelectedAnswers(prev => ({
            ...prev,
            [questionId]: answerId,
        }));
    };

    const handleNext = () => {
        if (currentQuestion < quiz.questions.length - 1) {
            setCurrentQuestion(prev => prev + 1);
        }
    };

    const handlePrevious = () => {
        if (currentQuestion > 0) {
            setCurrentQuestion(prev => prev - 1);
        }
    };

    const handleSubmit = async () => {
        setSubmitting(true);
        const timeSpent = Math.floor((Date.now() - startTime) / 1000);

        try {
            await submitQuizAttempt(id, selectedAnswers, timeSpent);
            navigate('/results', {
                state: { message: 'QCM soumis avec succès !' }
            });
        } catch (err) {
            setError('Erreur lors de la soumission du QCM');
            console.error('Erreur:', err);
        } finally {
            setSubmitting(false);
        }
    };

    if (loading) {
        return (
            <div className="min-h-screen bg-light">
                <Navbar />
                <div className="flex justify-center items-center h-96">
                    <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
                </div>
            </div>
        );
    }

    if (error || !quiz) {
        return (
            <div className="min-h-screen bg-light">
                <Navbar />
                <div className="text-center py-20">
                    <h1 className="text-2xl font-bold text-gray-600">{error || 'QCM non trouvé'}</h1>
                    <Link to="/courses" className="text-primary mt-4 inline-block">
                        ← Retour aux cours
                    </Link>
                </div>
            </div>
        );
    }

    if (!quiz.questions || quiz.questions.length === 0) {
        return (
            <div className="min-h-screen bg-light">
                <Navbar />
                <div className="text-center py-20">
                    <h1 className="text-2xl font-bold text-gray-600">Ce QCM n'a pas de questions</h1>
                    <Link to="/courses" className="text-primary mt-4 inline-block">
                        ← Retour aux cours
                    </Link>
                </div>
            </div>
        );
    }

    const question = quiz.questions[currentQuestion];
    const progress = ((currentQuestion + 1) / quiz.questions.length) * 100;
    const answeredCount = Object.keys(selectedAnswers).length;

    return (
        <div className="min-h-screen bg-gradient-to-br from-blue-50 to-cyan-50">
            <Navbar />

            <div className="max-w-3xl mx-auto px-4 py-8">
                {/* Header */}
                <div className="bg-white rounded-2xl shadow-lg p-6 mb-6">
                    <h1 className="text-2xl font-bold text-dark mb-4">{quiz.title}</h1>
                    <ProgressBar progress={progress} />
                    <div className="flex justify-between items-center mt-3">
                        <p className="text-sm text-gray-500">
                            Question {currentQuestion + 1} sur {quiz.questions.length}
                        </p>
                        <p className="text-sm text-gray-500">
                            {answeredCount}/{quiz.questions.length} réponses
                        </p>
                    </div>
                </div>

                {/* Question */}
                <div className="bg-white rounded-2xl shadow-lg p-8 mb-6">
                    <h2 className="text-xl font-semibold text-dark mb-6">
                        {question.content}
                    </h2>

                    <div className="space-y-4">
                        {question.answers?.map(answer => (
                            <button
                                key={answer.id}
                                onClick={() => handleSelectAnswer(question.id, answer.id)}
                                className={`w-full text-left p-4 rounded-xl border-2 transition ${selectedAnswers[question.id] === answer.id
                                        ? 'border-primary bg-blue-50 text-primary'
                                        : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50'
                                    }`}
                            >
                                <div className="flex items-center gap-4">
                                    <div className={`w-6 h-6 rounded-full border-2 flex items-center justify-center flex-shrink-0 ${selectedAnswers[question.id] === answer.id
                                            ? 'border-primary bg-primary'
                                            : 'border-gray-300'
                                        }`}>
                                        {selectedAnswers[question.id] === answer.id && (
                                            <svg className="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                                            </svg>
                                        )}
                                    </div>
                                    <span className="font-medium">{answer.content}</span>
                                </div>
                            </button>
                        ))}
                    </div>
                </div>

                {/* Navigation */}
                <div className="flex justify-between">
                    <button
                        onClick={handlePrevious}
                        disabled={currentQuestion === 0}
                        className="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        ← Précédent
                    </button>

                    {currentQuestion === quiz.questions.length - 1 ? (
                        <button
                            onClick={handleSubmit}
                            disabled={submitting || answeredCount !== quiz.questions.length}
                            className="px-8 py-3 bg-success text-white font-bold rounded-lg hover:bg-emerald-600 transition disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {submitting ? 'Envoi...' : '✓ Terminer le QCM'}
                        </button>
                    ) : (
                        <button
                            onClick={handleNext}
                            className="px-6 py-3 hero-gradient text-white font-semibold rounded-lg hover:shadow-lg transition"
                        >
                            Suivant →
                        </button>
                    )}
                </div>

                {/* Question dots navigation */}
                <div className="flex justify-center gap-2 mt-8">
                    {quiz.questions.map((q, index) => (
                        <button
                            key={q.id}
                            onClick={() => setCurrentQuestion(index)}
                            className={`w-3 h-3 rounded-full transition ${index === currentQuestion
                                    ? 'bg-primary scale-125'
                                    : selectedAnswers[q.id]
                                        ? 'bg-success'
                                        : 'bg-gray-300 hover:bg-gray-400'
                                }`}
                            title={`Question ${index + 1}`}
                        />
                    ))}
                </div>
            </div>
        </div>
    );
}

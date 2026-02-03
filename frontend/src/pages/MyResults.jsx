import { useState, useEffect } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
import { getMyResults } from '../api/quizzes';
import { useAuth } from '../context/AuthContext';
import Navbar from '../components/Navbar';
import ScoreBadge from '../components/ScoreBadge';

export default function MyResults() {
    const { user } = useAuth();
    const location = useLocation();
    const navigate = useNavigate();
    const [results, setResults] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const successMessage = location.state?.message;

    useEffect(() => {
        const fetchResults = async () => {
            try {
                if (user?.id) {
                    const searchParams = new URLSearchParams(location.search);
                    const courseId = searchParams.get('course');

                    let url = `/api/quiz_attempts?student=${user.id}`;
                    if (courseId) {
                        // We need to filter by quiz.course, but typically this requires a subresource or a custom filter
                        // Easier: fetch all and filter client side OR use the nested filter if enabled
                        // I enabled: properties: ['student' => 'exact', 'quiz' => 'exact']
                        // Wait, I didn't enable 'quiz.course'. 
                        // Let's rely on client side filtering or fetch by quiz if the user wants. 
                        // Actually, 'quiz.course' filter is not enabled. 
                        // Let's just fetch all and filter in JS for now to be safe and quick.
                    }

                    const data = await getMyResults(user.id);

                    let filteredData = data;
                    if (courseId) {
                        filteredData = data.filter(r => r.quiz?.course?.id == courseId || r.quiz?.course == `/api/courses/${courseId}`);
                    }

                    setResults(filteredData);
                }
            } catch (err) {
                setError('Erreur lors du chargement des résultats');
                console.error('Erreur:', err);
            } finally {
                setLoading(false);
            }
        };

        fetchResults();
    }, [user]);

    const formatDate = (dateString) => {
        return new Date(dateString).toLocaleDateString('fr-FR', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    };

    const formatTime = (seconds) => {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${mins}:${String(secs).padStart(2, '0')}`;
    };

    return (
        <div className="min-h-screen bg-light">
            <Navbar />

            <div className="max-w-5xl mx-auto px-4 py-8">
                <h1 className="text-3xl font-bold text-dark mb-8">📊 Mes Résultats</h1>

                {successMessage && (
                    <div className="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                        ✅ {successMessage}
                    </div>
                )}

                {error && (
                    <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                        {error}
                    </div>
                )}

                {loading ? (
                    <div className="flex justify-center items-center h-64">
                        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
                    </div>
                ) : results.length === 0 ? (
                    <div className="bg-white rounded-2xl shadow-lg p-8 text-center">
                        <div className="text-5xl mb-4">📭</div>
                        <p className="text-gray-500 text-lg">
                            Vous n'avez pas encore passé de QCM.
                        </p>
                    </div>
                ) : (
                    <div className="bg-white rounded-2xl shadow-lg overflow-hidden">
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead className="hero-gradient text-white">
                                    <tr>
                                        <th className="px-6 py-4 text-left font-semibold">QCM</th>
                                        <th className="px-6 py-4 text-left font-semibold">Cours</th>
                                        <th className="px-6 py-4 text-center font-semibold">Score</th>
                                        <th className="px-6 py-4 text-center font-semibold">Temps</th>
                                        <th className="px-6 py-4 text-left font-semibold">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {results.map((result, index) => (
                                        <tr
                                            key={result.id}
                                            onClick={() => navigate(`/results/${result.id}`)}
                                            className={`border-b border-gray-100 hover:bg-gray-100 transition cursor-pointer ${index % 2 === 0 ? 'bg-white' : 'bg-gray-50'
                                                }`}
                                        >
                                            <td className="px-6 py-4 font-medium">
                                                {result.quiz?.title || 'QCM'}
                                            </td>
                                            <td className="px-6 py-4 text-gray-600">
                                                {result.quiz?.course?.title || '-'}
                                            </td>
                                            <td className="px-6 py-4 text-center">
                                                <ScoreBadge score={result.score} maxScore={result.maxScore} />
                                            </td>
                                            <td className="px-6 py-4 text-center text-gray-600">
                                                {result.timeSpentSeconds ? formatTime(result.timeSpentSeconds) : '-'}
                                            </td>
                                            <td className="px-6 py-4 text-gray-500 text-sm">
                                                {result.submittedAt ? formatDate(result.submittedAt) : '-'}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                )}

                {/* Stats Summary */}
                {results.length > 0 && (
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
                        <div className="bg-white rounded-xl shadow-lg p-6 text-center">
                            <div className="text-4xl font-bold text-primary">{results.length}</div>
                            <div className="text-gray-500 mt-2">QCM passés</div>
                        </div>
                        <div className="bg-white rounded-xl shadow-lg p-6 text-center">
                            <div className="text-4xl font-bold text-success">
                                {results.length > 0
                                    ? Math.round(results.reduce((acc, r) => acc + (r.score / r.maxScore) * 100, 0) / results.length)
                                    : 0}%
                            </div>
                            <div className="text-gray-500 mt-2">Moyenne générale</div>
                        </div>
                        <div className="bg-white rounded-xl shadow-lg p-6 text-center">
                            <div className="text-4xl font-bold text-secondary">
                                {results.length > 0
                                    ? Math.max(...results.map(r => (r.score / r.maxScore) * 100)).toFixed(0)
                                    : 0}%
                            </div>
                            <div className="text-gray-500 mt-2">Meilleur score</div>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
}

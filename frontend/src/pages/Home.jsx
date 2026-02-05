import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { getAllCourses } from '../api/courses';
import Navbar from '../components/Navbar';

export default function Home() {
    const { isAuthenticated, isStudent, user } = useAuth();
    const [courses, setCourses] = useState([]);
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        // Si l'utilisateur est connecté, charger les cours avec leurs quizzes
        if (isAuthenticated) {
            setLoading(true);
            getAllCourses()
                .then(data => setCourses(data))
                .catch(err => console.error('Erreur chargement cours:', err))
                .finally(() => setLoading(false));
        }
    }, [isAuthenticated]);

    // Extraire tous les quizzes de tous les cours
    const allQuizzes = courses.flatMap(course =>
        (course.quizzes || []).map(quiz => ({
            ...quiz,
            courseName: course.title
        }))
    );

    return (
        <div className="min-h-screen bg-light">
            <Navbar />

            {/* Hero Section */}
            <div className="hero-gradient text-white py-20 text-center">
                <div className="max-w-4xl mx-auto px-4">
                    <h1 className="text-5xl font-extrabold mb-6">
                        {isAuthenticated
                            ? `Bienvenue, ${user?.firstName || user?.email?.split('@')[0] || 'Étudiant'} ! 👋`
                            : 'Plateforme d\'Apprentissage Moderne'
                        }
                    </h1>
                    <p className="text-xl opacity-90 mb-8">
                        {isAuthenticated
                            ? 'Continuez votre apprentissage et testez vos connaissances'
                            : 'Accédez à vos cours, regardez des vidéos, consultez des documents et passez des QCM générés par IA'
                        }
                    </p>

                    <div className="flex gap-4 justify-center">
                        {!isAuthenticated && (
                            <>
                                <Link to="/register" className="btn bg-gray-200 text-gray-700 hover:bg-gray-300 border-0 no-underline">
                                    S'enregistrer
                                </Link>
                                <Link to="/login" className="btn btn-outline bg-white text-primary border-white hover:bg-opacity-90 no-underline">
                                    Se connecter
                                </Link>
                            </>
                        )}
                        {isAuthenticated && (
                            <Link to="/courses" className="btn bg-white/20 text-white border-2 border-white/50 hover:bg-white/30 no-underline">
                                📚 Mes cours
                            </Link>
                        )}
                        {isAuthenticated && isStudent && (
                            <Link to="/results" className="btn bg-white/20 text-white border-2 border-white/50 hover:bg-white/30 no-underline">
                                📊 Mes résultats
                            </Link>
                        )}
                    </div>

                    {!isAuthenticated && (
                        <div className="flex gap-12 justify-center mt-12">
                            <div className="text-center">
                                <div className="text-4xl font-extrabold">150+</div>
                                <div className="text-sm opacity-80">Cours disponibles</div>
                            </div>
                            <div className="text-center">
                                <div className="text-4xl font-extrabold">2,500+</div>
                                <div className="text-sm opacity-80">Étudiants actifs</div>
                            </div>
                            <div className="text-center">
                                <div className="text-4xl font-extrabold">98%</div>
                                <div className="text-sm opacity-80">Taux de satisfaction</div>
                            </div>
                        </div>
                    )}
                </div>
            </div>

            {/* QCM Section for logged-in students */}
            {isAuthenticated && isStudent && (
                <div className="max-w-6xl mx-auto px-4 py-16">
                    <h2 className="text-3xl font-bold text-center mb-8">📝 QCM Disponibles</h2>

                    {loading ? (
                        <div className="flex justify-center">
                            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
                        </div>
                    ) : allQuizzes.length > 0 ? (
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            {allQuizzes.map(quiz => (
                                <div key={quiz.id} className="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition">
                                    <div className="flex items-start justify-between mb-4">
                                        <div>
                                            <h3 className="text-lg font-bold text-dark">{quiz.title}</h3>
                                            <p className="text-sm text-gray-500">{quiz.courseName}</p>
                                        </div>
                                        {quiz.isGeneratedByAI && (
                                            <span className="bg-purple-100 text-purple-600 text-xs px-2 py-1 rounded-full">
                                                🤖 IA
                                            </span>
                                        )}
                                    </div>
                                    <p className="text-gray-600 text-sm mb-4 line-clamp-2">
                                        {quiz.description || 'Testez vos connaissances sur ce chapitre !'}
                                    </p>
                                    <Link
                                        to={`/quiz/${quiz.id}`}
                                        className="btn btn-primary w-full no-underline text-center"
                                    >
                                        Passer le QCM →
                                    </Link>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <div className="text-center py-12 bg-white rounded-xl shadow-lg">
                            <div className="text-5xl mb-4">📭</div>
                            <p className="text-gray-500">Aucun QCM disponible pour le moment</p>
                            <Link to="/courses" className="text-primary font-bold mt-4 inline-block">
                                Explorez les cours →
                            </Link>
                        </div>
                    )}
                </div>
            )}

            {/* Features Section for non-logged users */}
            {!isAuthenticated && (
                <div className="max-w-6xl mx-auto px-4 py-16">
                    <h2 className="text-3xl font-bold text-center mb-12">Fonctionnalités</h2>

                    <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div className="bg-white rounded-xl p-8 shadow-lg text-center">
                            <div className="text-5xl mb-4">📹</div>
                            <h3 className="text-xl font-bold mb-2">Vidéos de Cours</h3>
                            <p className="text-gray-600">
                                Accédez à des vidéos pédagogiques de haute qualité pour chaque cours.
                            </p>
                        </div>

                        <div className="bg-white rounded-xl p-8 shadow-lg text-center">
                            <div className="text-5xl mb-4">📄</div>
                            <h3 className="text-xl font-bold mb-2">Documents PDF</h3>
                            <p className="text-gray-600">
                                Consultez et téléchargez les supports de cours en format PDF.
                            </p>
                        </div>

                        <div className="bg-white rounded-xl p-8 shadow-lg text-center">
                            <div className="text-5xl mb-4">🤖</div>
                            <h3 className="text-xl font-bold mb-2">QCM par IA</h3>
                            <p className="text-gray-600">
                                Testez vos connaissances avec des QCM générés automatiquement par IA.
                            </p>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}

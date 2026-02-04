import { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import { getCourseById } from '../api/courses';
import { getQuizzesByCourse, getMyResults } from '../api/quizzes';
import { useAuth } from '../context/AuthContext';
import Navbar from '../components/Navbar';
import VideoPlayer from '../components/VideoPlayer';

export default function CourseDetail() {
    const { user } = useAuth();
    const { id } = useParams();
    const [course, setCourse] = useState(null);
    const [quizzes, setQuizzes] = useState([]);
    const [myResults, setMyResults] = useState([]);
    const [activeTab, setActiveTab] = useState('videos');
    const [selectedVideo, setSelectedVideo] = useState(null);
    const [selectedDocument, setSelectedDocument] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const fetchData = async () => {
            try {
                const promises = [
                    getCourseById(id),
                    getQuizzesByCourse(id),
                ];

                if (user) {
                    promises.push(getMyResults(user.id));
                }

                const results = await Promise.all(promises);
                const courseData = results[0];
                const quizzesData = results[1];
                const myResultsData = user ? results[2] : [];

                setCourse(courseData);
                setQuizzes(quizzesData);
                setMyResults(myResultsData);

                if (courseData.videos?.length > 0) {
                    setSelectedVideo(courseData.videos[0]);
                }
                if (courseData.documents?.length > 0) {
                    setSelectedDocument(courseData.documents[0]);
                }
            } catch (error) {
                console.error('Erreur:', error);
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
                <div className="flex justify-center items-center h-96">
                    <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
                </div>
            </div>
        );
    }

    if (!course) {
        return (
            <div className="min-h-screen bg-light">
                <Navbar />
                <div className="text-center py-20">
                    <h1 className="text-2xl font-bold text-gray-600">Cours non trouvé</h1>
                    <p className="text-gray-500 mt-2">Le cours demandé n'existe pas ou vous n'y avez pas accès.</p>
                    <Link to="/courses" className="text-primary mt-4 inline-block btn btn-outline-primary">
                        ← Retour à la liste des cours
                    </Link>
                </div>
            </div>
        );
    }

    return (
        <div className="min-h-screen bg-light">
            <Navbar />

            <div className="max-w-7xl mx-auto px-4 py-8">
                {/* Header */}
                <div className="bg-white rounded-2xl shadow-lg p-6 mb-6">
                    <Link to="/courses" className="text-primary text-sm mb-4 inline-block">
                        ← Retour aux cours
                    </Link>
                    <h1 className="text-3xl font-bold text-dark mb-2">{course.title}</h1>
                    <p className="text-gray-600 mb-4">{course.description}</p>
                    <div className="flex items-center gap-4 text-sm text-gray-500">
                        <span>👨‍🏫 {course.teacher?.firstName || 'Prof.'} {course.teacher?.lastName || ''}</span>
                        <span>📹 {course.videos?.length || 0} vidéos</span>
                        <span>📄 {course.documents?.length || 0} documents</span>
                        <span>📝 {quizzes.length} QCM</span>
                    </div>
                </div>

                {/* Tabs */}
                <div className="flex gap-4 mb-6">
                    <button
                        onClick={() => setActiveTab('videos')}
                        className={`px-6 py-3 rounded-lg font-semibold transition ${activeTab === 'videos'
                            ? 'hero-gradient text-white'
                            : 'bg-white text-gray-600 hover:bg-gray-50'
                            }`}
                    >
                        📹 Vidéos
                    </button>
                    <button
                        onClick={() => setActiveTab('documents')}
                        className={`px-6 py-3 rounded-lg font-semibold transition ${activeTab === 'documents'
                            ? 'hero-gradient text-white'
                            : 'bg-white text-gray-600 hover:bg-gray-50'
                            }`}
                    >
                        📄 Documents
                    </button>
                    <button
                        onClick={() => setActiveTab('quizzes')}
                        className={`px-6 py-3 rounded-lg font-semibold transition ${activeTab === 'quizzes'
                            ? 'hero-gradient text-white'
                            : 'bg-white text-gray-600 hover:bg-gray-50'
                            }`}
                    >
                        📝 QCM
                    </button>
                </div>

                {/* Content */}
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Main Content */}
                    <div className="lg:col-span-2">
                        {activeTab === 'videos' && selectedVideo && (
                            <div className="bg-white rounded-2xl shadow-lg overflow-hidden">
                                <VideoPlayer video={selectedVideo} />
                                <div className="p-6 border-b border-gray-100">
                                    <h2 className="text-xl font-bold mb-2">{selectedVideo.title}</h2>
                                    <p className="text-gray-600">{selectedVideo.description}</p>
                                </div>
                                <div className="p-6 bg-gray-50/50">
                                    <h3 className="text-sm font-bold uppercase tracking-wider text-gray-500 mb-3 flex items-center gap-2">
                                        📄 Transcription (IA)
                                    </h3>
                                    <div className="bg-white p-4 rounded-xl border border-gray-200 shadow-sm max-h-60 overflow-y-auto custom-scrollbar">
                                        {selectedVideo.transcription ? (
                                            <p className="text-gray-700 whitespace-pre-line text-sm leading-relaxed">
                                                {selectedVideo.transcription}
                                            </p>
                                        ) : (
                                            <div className="text-center py-6 text-gray-400">
                                                <p className="text-sm italic">Aucune transcription disponible.</p>
                                                <p className="text-xs mt-1 opacity-70">
                                                    (La génération automatique audio-vers-texte nécessite une clé API spécifique)
                                                </p>
                                            </div>
                                        )}
                                    </div>
                                </div>
                            </div>
                        )}

                        {activeTab === 'videos' && !selectedVideo && (
                            <div className="bg-white rounded-2xl shadow-lg p-8 text-center">
                                <div className="text-5xl mb-4">📹</div>
                                <p className="text-gray-500">Aucune vidéo disponible pour ce cours</p>
                            </div>
                        )}

                        {activeTab === 'documents' && selectedDocument && (
                            <div className="bg-white rounded-2xl shadow-lg p-6">
                                <h2 className="text-xl font-bold mb-4">{selectedDocument.title}</h2>
                                <p className="text-gray-600 mb-4">{selectedDocument.description}</p>
                                <a
                                    href={`/uploads/documents/${selectedDocument.fileName}`}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="btn btn-primary inline-block no-underline"
                                >
                                    📥 Télécharger le document
                                </a>
                            </div>
                        )}

                        {activeTab === 'documents' && !selectedDocument && (
                            <div className="bg-white rounded-2xl shadow-lg p-8 text-center">
                                <div className="text-5xl mb-4">📄</div>
                                <p className="text-gray-500">Aucun document disponible pour ce cours</p>
                            </div>
                        )}

                        {activeTab === 'quizzes' && (
                            <div className="space-y-4">
                                {quizzes.length > 0 ? (
                                    <>
                                        <div className="flex justify-end mb-4">
                                            <Link
                                                to={`/results?course=${course.id}`}
                                                className="btn bg-blue-100 text-primary hover:bg-blue-200 transition no-underline px-4 py-2 rounded-lg font-medium"
                                            >
                                                📊 Voir tous mes résultats pour ce cours
                                            </Link>
                                        </div>
                                        {quizzes.map(quiz => {
                                            // Find attempts for this quiz
                                            const quizAttempts = myResults.filter(r => r.quiz?.id === quiz.id || r.quiz === `/api/quizzes/${quiz.id}`);
                                            const bestAttempt = quizAttempts.length > 0
                                                ? quizAttempts.reduce((prev, current) => (prev.score / prev.maxScore > current.score / current.maxScore) ? prev : current)
                                                : null;

                                            return (
                                                <div key={quiz.id} className="bg-white rounded-xl shadow-lg p-6">
                                                    <div className="flex items-start justify-between">
                                                        <div>
                                                            <h3 className="text-xl font-bold mb-2">{quiz.title}</h3>
                                                            <p className="text-gray-600 mb-4">{quiz.description}</p>
                                                            <div className="flex gap-3 text-sm text-gray-500 items-center">
                                                                <span>
                                                                    {quiz.questions?.length || '?'} questions
                                                                    {quiz.isGeneratedByAI && ' • 🤖 Généré par IA'}
                                                                </span>

                                                                {bestAttempt && (
                                                                    <div className="flex items-center gap-2 bg-green-50 text-green-700 px-3 py-1 rounded-full border border-green-200">
                                                                        <span>🏆 Meilleur score: {bestAttempt.score}/{bestAttempt.maxScore}</span>
                                                                    </div>
                                                                )}
                                                            </div>
                                                        </div>
                                                        <div className="flex flex-col gap-2">
                                                            <Link
                                                                to={`/quiz/${quiz.id}`}
                                                                className="btn btn-primary no-underline text-center"
                                                            >
                                                                {bestAttempt ? 'Refaire le QCM' : 'Passer le QCM'}
                                                            </Link>
                                                            {bestAttempt && (
                                                                <Link
                                                                    to={`/results/${bestAttempt.id}`}
                                                                    className="btn btn-outline-secondary text-sm py-1"
                                                                >
                                                                    Voir détails
                                                                </Link>
                                                            )}
                                                        </div>
                                                    </div>
                                                </div>
                                            );
                                        })}
                                    </>
                                ) : (
                                    <div className="bg-white rounded-2xl shadow-lg p-8 text-center">
                                        <div className="text-5xl mb-4">📝</div>
                                        <p className="text-gray-500">Aucun QCM disponible pour ce cours</p>
                                    </div>
                                )}
                            </div>
                        )}
                    </div>

                    {/* Sidebar - Liste */}
                    <div className="bg-white rounded-2xl shadow-lg p-4 h-fit max-h-[600px] overflow-y-auto">
                        <h3 className="font-bold text-lg mb-4">
                            {activeTab === 'videos' && '📹 Toutes les vidéos'}
                            {activeTab === 'documents' && '📄 Tous les documents'}
                            {activeTab === 'quizzes' && '📝 Tous les QCM'}
                        </h3>

                        {activeTab === 'videos' && (
                            <div className="space-y-2">
                                {course.videos?.length > 0 ? (
                                    course.videos.map(video => (
                                        <button
                                            key={video.id}
                                            onClick={() => setSelectedVideo(video)}
                                            className={`w-full text-left p-3 rounded-lg transition ${selectedVideo?.id === video.id
                                                ? 'bg-blue-100 border-l-4 border-primary'
                                                : 'hover:bg-gray-100'
                                                }`}
                                        >
                                            <p className="font-medium text-sm">{video.title}</p>
                                            {video.duration && (
                                                <p className="text-xs text-gray-500">{Math.floor(video.duration / 60)} min</p>
                                            )}
                                        </button>
                                    ))
                                ) : (
                                    <p className="text-gray-500 text-sm">Aucune vidéo</p>
                                )}
                            </div>
                        )}

                        {activeTab === 'documents' && (
                            <div className="space-y-2">
                                {course.documents?.length > 0 ? (
                                    course.documents.map(doc => (
                                        <button
                                            key={doc.id}
                                            onClick={() => setSelectedDocument(doc)}
                                            className={`w-full text-left p-3 rounded-lg transition ${selectedDocument?.id === doc.id
                                                ? 'bg-blue-100 border-l-4 border-primary'
                                                : 'hover:bg-gray-100'
                                                }`}
                                        >
                                            <p className="font-medium text-sm">{doc.title}</p>
                                        </button>
                                    ))
                                ) : (
                                    <p className="text-gray-500 text-sm">Aucun document</p>
                                )}
                            </div>
                        )}

                        {activeTab === 'quizzes' && (
                            <div className="space-y-2">
                                {quizzes.length > 0 ? (
                                    quizzes.map(quiz => (
                                        <Link
                                            key={quiz.id}
                                            to={`/quiz/${quiz.id}`}
                                            className="block p-3 rounded-lg hover:bg-gray-100 transition no-underline text-dark"
                                        >
                                            <p className="font-medium text-sm">{quiz.title}</p>
                                            <p className="text-xs text-gray-500">
                                                {quiz.questions?.length || '?'} questions
                                            </p>
                                        </Link>
                                    ))
                                ) : (
                                    <p className="text-gray-500 text-sm">Aucun QCM</p>
                                )}
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
}

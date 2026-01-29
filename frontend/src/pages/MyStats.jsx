import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { getMyStats } from '../api/student';
import Navbar from '../components/Navbar';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, PieChart, Pie, Cell } from 'recharts';

const COLORS = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#06B6D4'];

import LoadingSpinner from '../components/LoadingSpinner';

export default function MyStats() {
    const navigate = useNavigate();
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchStats = async () => {
            try {
                const result = await getMyStats();
                setData(result);
            } catch (err) {
                setError("Impossible de charger vos statistiques.");
                console.error(err);
            } finally {
                setLoading(false);
            }
        };
        fetchStats();
    }, []);

    if (loading) {
        return (
            <div className="min-h-screen bg-light">
                <Navbar />
                <div className="flex items-center justify-center h-96">
                    <LoadingSpinner fullScreen={false} />
                </div>
            </div>
        );
    }

    if (error) {
        return (
            <div className="min-h-screen bg-light">
                <Navbar />
                <div className="flex flex-col items-center justify-center h-96 text-red-500 gap-2">
                    <span className="text-4xl">⚠️</span>
                    <p>{error}</p>
                    <button onClick={() => navigate('/courses')} className="btn btn-primary mt-4">
                        Retour aux cours
                    </button>
                </div>
            </div>
        );
    }

    if (!data) return null;

    return (
        <div className="min-h-screen bg-light">
            <Navbar />
            <div className="max-w-6xl mx-auto px-4 py-8">
                {/* Header */}
                <div className="mb-8">
                    <h1 className="text-3xl font-bold text-gray-800 mb-2">📊 Mes statistiques</h1>
                    <p className="text-gray-500">Suivez votre progression et vos performances</p>
                </div>

                {/* KPIs */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                    <div className="bg-white rounded-2xl shadow-sm p-6 border-l-4 border-blue-500">
                        <p className="text-sm text-gray-500 uppercase font-semibold mb-1">Moyenne générale</p>
                        <p className="text-3xl font-bold text-gray-800">
                            {(data.avgScore ?? 0).toFixed(1)} <span className="text-sm text-gray-400 font-normal">/ 20</span>
                        </p>
                    </div>
                    <div className="bg-white rounded-2xl shadow-sm p-6 border-l-4 border-green-500">
                        <p className="text-sm text-gray-500 uppercase font-semibold mb-1">Taux de réussite</p>
                        <p className="text-3xl font-bold text-gray-800">{data.successRate ?? 0}%</p>
                    </div>
                    <div className="bg-white rounded-2xl shadow-sm p-6 border-l-4 border-purple-500">
                        <p className="text-sm text-gray-500 uppercase font-semibold mb-1">QCM passés</p>
                        <p className="text-3xl font-bold text-gray-800">{data.totalAttempts ?? 0}</p>
                    </div>
                    <div className="bg-white rounded-2xl shadow-sm p-6 border-l-4 border-amber-500">
                        <p className="text-sm text-gray-500 uppercase font-semibold mb-1">Cours suivis</p>
                        <p className="text-3xl font-bold text-gray-800">{data.coursesCount ?? 0}</p>
                    </div>
                </div>

                {/* Charts */}
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    {/* Evolution Chart */}
                    <div className="bg-white rounded-2xl shadow-sm p-6 lg:col-span-2">
                        <h3 className="text-lg font-bold text-gray-800 mb-4">📈 Évolution des résultats</h3>
                        {data.evolution && data.evolution.length > 0 ? (
                            <div className="h-64">
                                <ResponsiveContainer width="100%" height="100%">
                                    <LineChart data={data.evolution.map((item, idx) => ({ ...item, idx }))} margin={{ top: 10, right: 10, left: 0, bottom: 0 }}>
                                        <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#E5E7EB" />
                                        <XAxis dataKey="idx" stroke="#9CA3AF" tick={{ fontSize: 11 }} tickFormatter={(idx) => data.evolution[idx]?.date || ''} />
                                        <YAxis domain={[0, 20]} stroke="#9CA3AF" tick={{ fontSize: 12 }} />
                                        <Tooltip
                                            contentStyle={{ backgroundColor: '#fff', borderRadius: '8px', boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)', border: 'none' }}
                                            labelFormatter={(idx) => data.evolution[idx]?.quiz || data.evolution[idx]?.date}
                                            formatter={(value) => [`${value} / 20`, 'Note']}
                                        />
                                        <Line type="monotone" dataKey="score" name="Note" stroke="#3B82F6" strokeWidth={3} dot={{ r: 5, fill: '#3B82F6' }} activeDot={{ r: 7 }} />
                                    </LineChart>
                                </ResponsiveContainer>
                            </div>
                        ) : (
                            <div className="h-64 flex items-center justify-center text-gray-400">
                                Pas encore de données
                            </div>
                        )}
                    </div>

                    {/* Course Distribution */}
                    <div className="bg-white rounded-2xl shadow-sm p-6">
                        <h3 className="text-lg font-bold text-gray-800 mb-4">📚 Répartition par cours</h3>
                        {data.courseDistribution && data.courseDistribution.length > 0 ? (
                            <div className="h-64">
                                <ResponsiveContainer width="100%" height="100%">
                                    <PieChart>
                                        <Pie
                                            data={data.courseDistribution}
                                            cx="50%"
                                            cy="50%"
                                            innerRadius={50}
                                            outerRadius={80}
                                            paddingAngle={3}
                                            dataKey="value"
                                        >
                                            {data.courseDistribution.map((entry, index) => (
                                                <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                                            ))}
                                        </Pie>
                                        <Tooltip />
                                    </PieChart>
                                </ResponsiveContainer>
                            </div>
                        ) : (
                            <div className="h-64 flex items-center justify-center text-gray-400">
                                Pas encore de données
                            </div>
                        )}
                    </div>
                </div>

                {/* Recent Attempts */}
                <div className="bg-white rounded-2xl shadow-sm p-6">
                    <div className="flex justify-between items-center mb-4">
                        <h3 className="text-lg font-bold text-gray-800">🎯 Dernières tentatives</h3>
                        <button onClick={() => navigate('/results')} className="text-sm text-primary hover:underline">
                            Voir tout →
                        </button>
                    </div>
                    {data.recentAttempts && data.recentAttempts.length > 0 ? (
                        <div className="divide-y divide-gray-100">
                            {data.recentAttempts.map((attempt, idx) => (
                                <div key={idx} className="py-3 flex justify-between items-center">
                                    <div>
                                        <p className="font-medium text-gray-800">{attempt.quizTitle}</p>
                                        <p className="text-sm text-gray-500">{attempt.courseTitle}</p>
                                    </div>
                                    <div className="text-right">
                                        <span className={`px-3 py-1 rounded-full text-sm font-bold ${attempt.score >= 16 ? 'bg-green-100 text-green-700' :
                                            attempt.score >= 10 ? 'bg-yellow-100 text-yellow-700' :
                                                'bg-red-100 text-red-700'
                                            }`}>
                                            {attempt.score.toFixed(1)} / 20
                                        </span>
                                        <p className="text-xs text-gray-400 mt-1">{attempt.date}</p>
                                    </div>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <p className="text-center text-gray-400 py-8">Aucune tentative pour le moment</p>
                    )}
                </div>
            </div>
        </div>
    );
}

import { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import { getStudentAnalytics } from '../api/teacher';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer, BarChart, Bar, PieChart, Pie, Cell } from 'recharts';
import { GraduationCap, Trophy, Clock, BookOpen, AlertCircle, FileText } from 'lucide-react';

const COLORS = ['#0088FE', '#00C49F', '#FFBB28', '#FF8042', '#8884d8', '#82ca9d'];

export default function StudentAnalytics() {
    const { id } = useParams();
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [activeTab, setActiveTab] = useState('global'); // 'global' or 'contextual'

    useEffect(() => {
        const fetchData = async () => {
            setLoading(true);
            try {
                const result = await getStudentAnalytics(id);
                setData(result);
                setError(null);
            } catch (err) {
                setError("Impossible de charger les données de l'étudiant.");
                console.error(err);
            } finally {
                setLoading(false);
            }
        };
        if (id) fetchData();
    }, [id]);

    if (loading) return <div className="flex justify-center items-center h-full"><div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div></div>;
    if (error) return <div className="flex flex-col items-center justify-center h-full text-red-500 gap-2"><AlertCircle className="h-8 w-8" /> {error}</div>;
    if (!data) return null;

    const stats = activeTab === 'global' ? data.global : data.contextual;
    const isGlobal = activeTab === 'global';

    return (
        <div className="p-6 max-w-7xl mx-auto space-y-6">
            {/* Header */}
            <div className="flex justify-between items-start bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <div className="flex gap-4">
                    <div className="h-16 w-16 bg-gradient-to-br from-primary to-blue-600 rounded-full flex items-center justify-center text-white text-2xl font-bold shadow-md">
                        {data.student.firstName[0]}{data.student.lastName[0]}
                    </div>
                    <div>
                        <h1 className="text-2xl font-bold text-gray-800">{data.student.firstName} {data.student.lastName}</h1>
                        <p className="text-gray-500 flex items-center gap-2">
                            <span className="bg-blue-50 text-blue-700 px-2 py-0.5 rounded text-xs font-semibold uppercase tracking-wide">Étudiant</span>
                            {data.student.studentNumber && <span className="text-sm">Matricule: {data.student.studentNumber}</span>}
                        </p>
                        <div className="mt-2 text-sm text-gray-400">
                            Inscrit le : {data.student.enrollmentDate || 'N/A'}
                        </div>
                    </div>
                </div>
                <div className="flex bg-gray-100 p-1 rounded-lg">
                    <button
                        onClick={() => setActiveTab('global')}
                        className={`px-4 py-2 rounded-md text-sm font-medium transition-all ${isGlobal ? 'bg-white text-primary shadow-sm' : 'text-gray-500 hover:text-gray-700'}`}
                    >
                        Analyse Globale
                    </button>
                    <button
                        onClick={() => setActiveTab('contextual')}
                        className={`px-4 py-2 rounded-md text-sm font-medium transition-all ${!isGlobal ? 'bg-white text-primary shadow-sm' : 'text-gray-500 hover:text-gray-700'}`}
                    >
                        Mes Cours
                    </button>
                </div>
            </div>

            {/* KPIs */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md transition">
                    <div className="p-4 bg-green-50 text-green-600 rounded-full">
                        <Trophy className="h-8 w-8" />
                    </div>
                    <div>
                        <p className="text-sm text-gray-500 font-medium">Moyenne Générale</p>
                        <h3 className="text-3xl font-bold text-gray-800">{stats.kpi.avgScore} <span className="text-sm text-gray-400 font-normal">/ 20</span></h3>
                    </div>
                </div>
                <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md transition">
                    <div className="p-4 bg-blue-50 text-blue-600 rounded-full">
                        <GraduationCap className="h-8 w-8" />
                    </div>
                    <div>
                        <p className="text-sm text-gray-500 font-medium">Taux de Réussite</p>
                        <h3 className="text-3xl font-bold text-gray-800">{stats.kpi.successRate}%</h3>
                    </div>
                </div>
                <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md transition">
                    <div className="p-4 bg-purple-50 text-purple-600 rounded-full">
                        <FileText className="h-8 w-8" />
                    </div>
                    <div>
                        <p className="text-sm text-gray-500 font-medium">QCM Passés</p>
                        <h3 className="text-3xl font-bold text-gray-800">{stats.kpi.totalAttempts}</h3>
                    </div>
                </div>
            </div>

            {/* Charts Section */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {/* Evolution Chart */}
                <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-100 lg:col-span-2">
                    <h3 className="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <Clock className="w-5 h-5 text-gray-500" /> Progression des résultats
                    </h3>
                    <div className="h-80">
                        <ResponsiveContainer width="100%" height="100%">
                            <LineChart data={stats.charts.evolution}>
                                <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#E5E7EB" />
                                <XAxis dataKey="date" stroke="#9CA3AF" tick={{ fontSize: 12 }} />
                                <YAxis domain={[0, 20]} stroke="#9CA3AF" tick={{ fontSize: 12 }} />
                                <Tooltip
                                    contentStyle={{ backgroundColor: '#fff', borderRadius: '8px', boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1)', border: 'none' }}
                                    formatter={(value) => [`${value} / 20`, 'Note Moyenne']}
                                />
                                <Legend />
                                <Line type="monotone" dataKey="avgScore" name="Moyenne" stroke="#3B82F6" strokeWidth={3} activeDot={{ r: 8 }} dot={{ r: 4, fill: '#3B82F6', strokeWidth: 2, stroke: '#fff' }} />
                            </LineChart>
                        </ResponsiveContainer>
                    </div>
                </div>

                {/* Distributions - Only on Global Tab */}
                {isGlobal && (
                    <>
                        <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                            <h3 className="text-lg font-bold text-gray-800 mb-4">Répartition par Cours</h3>
                            <div className="h-64">
                                <ResponsiveContainer width="100%" height="100%">
                                    <PieChart>
                                        <Pie
                                            data={stats.charts.courseDistribution}
                                            cx="50%"
                                            cy="50%"
                                            innerRadius={60}
                                            outerRadius={80}
                                            fill="#8884d8"
                                            paddingAngle={5}
                                            dataKey="value"
                                        >
                                            {stats.charts.courseDistribution.map((entry, index) => (
                                                <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                                            ))}
                                        </Pie>
                                        <Tooltip />
                                        <Legend />
                                    </PieChart>
                                </ResponsiveContainer>
                            </div>
                        </div>

                        <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                            <h3 className="text-lg font-bold text-gray-800 mb-4">Répartition par Professeur</h3>
                            <div className="h-64">
                                <ResponsiveContainer width="100%" height="100%">
                                    <BarChart data={stats.charts.teacherDistribution}>
                                        <CartesianGrid strokeDasharray="3 3" vertical={false} />
                                        <XAxis dataKey="name" hide />
                                        <YAxis />
                                        <Tooltip />
                                        <Legend />
                                        <Bar dataKey="value" name="QCM Passés" fill="#82ca9d" radius={[4, 4, 0, 0]} />
                                    </BarChart>
                                </ResponsiveContainer>
                            </div>
                        </div>
                    </>
                )}

                {/* Course List - Only on Contextual Tab */}
                {!isGlobal && (
                    <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-100 lg:col-span-2">
                        <h3 className="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                            <BookOpen className="w-5 h-5 text-gray-500" />  Détail par Cours
                        </h3>
                        <div className="overflow-x-auto">
                            <table className="w-full text-left">
                                <thead>
                                    <tr className="bg-gray-50 text-gray-600 text-xs uppercase tracking-wider">
                                        <th className="px-6 py-3 font-medium">Cours</th>
                                        <th className="px-6 py-3 font-medium text-center">Tentatives</th>
                                        <th className="px-6 py-3 font-medium text-center">Meilleure Note</th>
                                        <th className="px-6 py-3 font-medium text-right">Dernière Activité</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100">
                                    {data.contextual.courses.length === 0 ? (
                                        <tr>
                                            <td colSpan="4" className="px-6 py-8 text-center text-gray-400">
                                                Aucun cours tenté pour le moment.
                                            </td>
                                        </tr>
                                    ) : (
                                        data.contextual.courses.map((course) => (
                                            <tr key={course.id} className="hover:bg-gray-50 transition">
                                                <td className="px-6 py-4 font-medium text-gray-800">{course.title}</td>
                                                <td className="px-6 py-4 text-center">
                                                    <span className="bg-gray-100 text-gray-600 px-2 py-1 rounded-md text-xs font-bold">
                                                        {course.attempts}
                                                    </span>
                                                </td>
                                                <td className="px-6 py-4 text-center">
                                                    <span className={`px-2 py-1 rounded-md text-xs font-bold ${course.maxScore >= 16 ? 'bg-green-100 text-green-700' :
                                                            course.maxScore >= 10 ? 'bg-yellow-100 text-yellow-700' :
                                                                'bg-red-100 text-red-700'
                                                        }`}>
                                                        {Number(course.maxScore).toFixed(1)} / 20
                                                    </span>
                                                </td>
                                                <td className="px-6 py-4 text-right text-gray-500 text-sm">
                                                    {course.lastAttempt || '-'}
                                                </td>
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
}

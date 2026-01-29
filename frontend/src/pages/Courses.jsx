import { useState, useEffect } from 'react';
import { getAllCourses } from '../api/courses';
import Navbar from '../components/Navbar';
import CourseCard from '../components/CourseCard';

export default function Courses() {
    const [courses, setCourses] = useState([]);
    const [loading, setLoading] = useState(true);
    const [searchTerm, setSearchTerm] = useState('');
    const [error, setError] = useState('');

    useEffect(() => {
        const fetchCourses = async () => {
            try {
                const data = await getAllCourses();
                setCourses(data);
            } catch (err) {
                setError('Erreur lors du chargement des cours');
                console.error('Erreur:', err);
            } finally {
                setLoading(false);
            }
        };

        fetchCourses();
    }, []);

    const filteredCourses = courses.filter(course =>
        course.title?.toLowerCase().includes(searchTerm.toLowerCase()) ||
        course.description?.toLowerCase().includes(searchTerm.toLowerCase())
    );

    return (
        <div className="min-h-screen bg-light">
            <Navbar />

            <div className="max-w-7xl mx-auto px-4 py-8">
                <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
                    <h1 className="text-3xl font-bold text-dark">📚 Tous les Cours</h1>

                    <input
                        type="text"
                        placeholder="🔍 Rechercher un cours..."
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                        className="form-input w-full md:w-64"
                    />
                </div>

                {error && (
                    <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                        {error}
                    </div>
                )}

                {loading ? (
                    <div className="flex justify-center items-center h-64">
                        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
                    </div>
                ) : (
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        {filteredCourses.map(course => (
                            <CourseCard key={course.id} course={course} />
                        ))}
                    </div>
                )}

                {!loading && filteredCourses.length === 0 && (
                    <div className="text-center py-12 text-gray-500">
                        <div className="text-5xl mb-4">📭</div>
                        <p className="text-lg">Aucun cours trouvé</p>
                    </div>
                )}
            </div>
        </div>
    );
}

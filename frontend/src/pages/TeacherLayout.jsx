import { useState, useEffect } from 'react';
import { Outlet, useNavigate, useLocation } from 'react-router-dom';
import { getStudents } from '../api/teacher';
import { Search, User } from 'lucide-react';

export default function TeacherLayout() {
    const [students, setStudents] = useState([]);
    const [filteredStudents, setFilteredStudents] = useState([]);
    const [search, setSearch] = useState('');
    const navigate = useNavigate();
    const location = useLocation();

    useEffect(() => {
        const fetchStudents = async () => {
            try {
                const data = await getStudents();
                setStudents(data);
                setFilteredStudents(data);
            } catch (error) {
                console.error('Failed to fetch students', error);
            }
        };
        fetchStudents();
    }, []);

    useEffect(() => {
        const lower = search.toLowerCase();
        setFilteredStudents(students.filter(s =>
            s.firstName.toLowerCase().includes(lower) ||
            s.lastName.toLowerCase().includes(lower) ||
            s.email.toLowerCase().includes(lower)
        ));
    }, [search, students]);

    return (
        <div className="flex h-[calc(100vh-64px)] overflow-hidden">
            <aside className="w-80 bg-white border-r border-gray-200 flex flex-col z-10 shadow-sm">
                <div className="p-4 border-b border-gray-100 bg-gray-50/50">
                    <h2 className="text-lg font-bold text-gray-800 mb-3 flex items-center gap-2">
                        <User className="h-5 w-5 text-primary" />
                        Mes Étudiants
                    </h2>
                    <div className="relative">
                        <input
                            type="text"
                            placeholder="Rechercher un étudiant..."
                            className="w-full pl-9 pr-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                        />
                        <Search className="absolute left-3 top-2.5 h-4 w-4 text-gray-400" />
                    </div>
                </div>
                <div className="flex-1 overflow-y-auto">
                    {filteredStudents.length === 0 ? (
                        <div className="p-8 text-center text-gray-400 text-sm">
                            Aucun étudiant trouvé.
                        </div>
                    ) : (
                        <ul className="divide-y divide-gray-50">
                            {filteredStudents.map(student => {
                                const isActive = location.pathname.includes(`/teacher/students/${student.id}`);
                                return (
                                    <li key={student.id}>
                                        <button
                                            onClick={() => navigate(`/teacher/students/${student.id}`)}
                                            className={`w-full text-left px-4 py-3 hover:bg-gray-50 transition flex items-center gap-3 ${isActive ? 'bg-primary/5 border-r-4 border-primary' : ''}`}
                                        >
                                            <div className={`h-10 w-10 rounded-full flex items-center justify-center font-bold text-sm shadow-sm ${isActive ? 'bg-primary text-white' : 'bg-white border border-gray-200 text-gray-600'}`}>
                                                {student.firstName[0]}{student.lastName[0]}
                                            </div>
                                            <div className="min-w-0 flex-1">
                                                <div className={`text-sm font-medium ${isActive ? 'text-primary' : 'text-gray-900'}`}>
                                                    {student.firstName} {student.lastName}
                                                </div>
                                                <div className="text-xs text-gray-500 truncate">{student.email}</div>
                                            </div>
                                        </button>
                                    </li>
                                );
                            })}
                        </ul>
                    )}
                </div>
                <div className="p-3 bg-gray-50 border-t border-gray-100 text-xs text-center text-gray-400">
                    {filteredStudents.length} étudiant(s)
                </div>
            </aside>
            <main className="flex-1 overflow-y-auto bg-gray-50 relative">
                <Outlet />
            </main>
        </div>
    );
}

import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

export default function Navbar() {
    const { user, logout, isAuthenticated } = useAuth();
    const navigate = useNavigate();

    const handleLogout = () => {
        logout();
        navigate('/login');
    };

    return (
        <nav className="bg-white shadow-md py-5 sticky top-0 z-50">
            <div className="max-w-6xl mx-auto px-5 flex justify-between items-center">
                <Link to="/" className="logo-gradient no-underline">
                    🎓 EduLearn
                </Link>

                <div className="flex items-center gap-6">
                    {isAuthenticated ? (
                        <>
                            <Link to="/courses" className="text-gray-600 hover:text-primary font-medium transition">
                                Cours
                            </Link>
                            <Link to="/results" className="text-gray-600 hover:text-primary font-medium transition">
                                Mes Notes
                            </Link>

                            <div className="flex items-center gap-4 pl-6 border-l border-gray-200">
                                <span className="text-gray-700 font-medium">
                                    👋 {user?.email?.split('@')[0]}
                                </span>
                                <button
                                    onClick={handleLogout}
                                    className="btn btn-danger"
                                >
                                    Se déconnecter
                                </button>
                            </div>
                        </>
                    ) : (
                        <div className="flex items-center gap-4">
                            <Link to="/register" className="btn bg-gray-200 text-gray-700 hover:bg-gray-300 no-underline">
                                S'enregistrer
                            </Link>
                            <Link to="/login" className="btn btn-primary no-underline">
                                Se connecter
                            </Link>
                        </div>
                    )}
                </div>
            </div>
        </nav>
    );
}

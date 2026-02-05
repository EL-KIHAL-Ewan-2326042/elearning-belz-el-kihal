import React from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from './AuthContext';

function Navbar() {
    const { isAuthenticated, user } = useAuth();

    return (
        <nav className="bg-white shadow-md py-5 sticky top-0 z-50">
            <div className="max-w-6xl mx-auto px-5 flex justify-between items-center">
                <Link to="/" className="logo-gradient no-underline">
                    🎓 EduLearn
                </Link>
                <div className="flex gap-4">
                    {isAuthenticated ? (
                        <>
                            <span className="flex items-center text-dark font-medium">
                                👋 {user.firstName || user.email}
                            </span>
                            <form action="/logout" method="post" className="inline">
                                <button
                                    type="submit"
                                    className="btn btn-danger"
                                >
                                    Se déconnecter
                                </button>
                            </form>
                        </>
                    ) : (
                        <Link to="/login" className="btn btn-primary no-underline">
                            Se connecter
                        </Link>
                    )}
                </div>
            </div>
        </nav>
    );
}

export default Navbar;

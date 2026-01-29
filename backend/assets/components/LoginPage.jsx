import React, { useState } from 'react';
import { Link } from 'react-router-dom';

function LoginPage({ csrfToken, loginError, lastUsername }) {
    const [userType, setUserType] = useState('student');

    return (
        <div className="min-h-screen bg-light flex items-center justify-center p-5">
            <div className="bg-white rounded-2xl w-full max-w-md p-10 shadow-xl animate-slideDown">
                <div className="text-center mb-8">
                    <Link to="/" className="logo-gradient no-underline text-3xl">
                        🎓 EduLearn
                    </Link>
                </div>

                <h1 className="text-3xl font-extrabold text-dark mb-2">
                    {userType === 'student' ? 'Connexion Étudiant' : 'Connexion Professeur'}
                </h1>
                <p className="text-sm text-slate-500 mb-8">
                    {userType === 'student'
                        ? 'Accédez à vos cours et QCM'
                        : 'Gérez vos cours et évaluations'}
                </p>

                {/* Tabs */}
                <div className="modal-tabs flex gap-2 mb-8">
                    <button
                        className={`tab-btn ${userType === 'student' ? 'active' : ''}`}
                        onClick={() => setUserType('student')}
                    >
                        👨‍🎓 Étudiant
                    </button>
                    <button
                        className={`tab-btn ${userType === 'teacher' ? 'active' : ''}`}
                        onClick={() => setUserType('teacher')}
                    >
                        👨‍🏫 Professeur
                    </button>
                </div>

                {/* Error message */}
                {loginError && (
                    <div className="bg-red-100 text-red-800 p-4 rounded-lg mb-6 text-sm">
                        {loginError}
                    </div>
                )}

                {/* Login form - posts to Symfony's form_login */}
                <form method="post" action="/login">
                    <div className="mb-5">
                        <label className="block font-semibold text-dark mb-2">
                            Email
                        </label>
                        <input
                            type="email"
                            name="_username"
                            defaultValue={lastUsername}
                            className="form-input"
                            placeholder="votre.email@exemple.com"
                            required
                            autoFocus
                        />
                    </div>

                    <div className="mb-6">
                        <label className="block font-semibold text-dark mb-2">
                            Mot de passe
                        </label>
                        <input
                            type="password"
                            name="_password"
                            className="form-input"
                            placeholder="••••••••"
                            required
                        />
                    </div>

                    {/* CSRF Token */}
                    <input type="hidden" name="_csrf_token" value={csrfToken} />

                    <button
                        type="submit"
                        className="w-full py-4 border-none rounded-lg font-bold text-base cursor-pointer transition-all text-white hero-gradient hover:-translate-y-0.5"
                        style={{ boxShadow: '0 8px 20px rgba(37, 99, 235, 0.3)' }}
                    >
                        Se connecter
                    </button>
                </form>

                <p className="text-center mt-6 text-slate-500 text-sm">
                    Pas encore de compte ?{' '}
                    <a href="#" className="text-primary font-semibold no-underline hover:underline">
                        S'inscrire
                    </a>
                </p>

                <div className="text-center mt-4">
                    <Link to="/" className="text-slate-400 text-sm hover:text-primary">
                        ← Retour à l'accueil
                    </Link>
                </div>
            </div>
        </div>
    );
}

export default LoginPage;

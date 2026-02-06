import { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

export default function Login() {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(false);

    const { login } = useAuth();
    const navigate = useNavigate();

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');
        setLoading(true);

        try {
            const user = await login(email, password);
            console.log('User logged in:', user);

            // Redirect based on role
            if (user.roles && user.roles.includes('ROLE_TEACHER')) {
                console.log('Redirecting to teacher panel...');
                // Pour les professeurs, redirection vers le panel Symfony (/courses)
                window.location.href = '/courses';
            } else {
                console.log('Redirecting to student panel...');
                // Pour les étudiants, navigation React
                navigate('/courses');
            }
        } catch (err) {
            console.error('Login error:', err);
            if (err.response?.data?.message) {
                setError(err.response.data.message);
            } else if (err.response?.status === 401) {
                setError('Email ou mot de passe incorrect');
            } else {
                setError('Erreur de connexion. Vérifiez vos identifiants.');
            }
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="min-h-screen hero-gradient flex items-center justify-center px-4">
            <div className="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md">
                <div className="text-center mb-8">
                    <Link to="/" className="logo-gradient no-underline text-3xl">
                        🎓 EduLearn
                    </Link>
                    <p className="text-gray-500 mt-4">Connectez-vous à votre espace</p>
                </div>

                {error && (
                    <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm">
                        {error}
                    </div>
                )}

                <form onSubmit={handleSubmit} className="space-y-6">
                    <div>
                        <label className="block text-sm font-semibold text-dark mb-2">
                            Adresse email
                        </label>
                        <input
                            type="email"
                            value={email}
                            onChange={(e) => setEmail(e.target.value)}
                            className="form-input"
                            placeholder="votre@email.com"
                            required
                            autoFocus
                        />
                    </div>

                    <div>
                        <label className="block text-sm font-semibold text-dark mb-2">
                            Mot de passe
                        </label>
                        <input
                            type="password"
                            value={password}
                            onChange={(e) => setPassword(e.target.value)}
                            className="form-input"
                            placeholder="••••••••"
                            required
                        />
                    </div>

                    <button
                        type="submit"
                        disabled={loading}
                        className="w-full py-4 hero-gradient text-white font-bold rounded-lg hover:-translate-y-0.5 transition disabled:opacity-50"
                        style={{ boxShadow: '0 8px 20px rgba(37, 99, 235, 0.3)' }}
                    >
                        {loading ? 'Connexion...' : 'Se connecter'}
                    </button>
                </form>

                <div className="text-center mt-6 space-y-3">
                    <p className="text-sm text-gray-500">
                        Pas encore de compte ?{' '}
                        <Link to="/register" className="text-primary font-bold hover:underline">
                            Créer un compte
                        </Link>
                    </p>

                    <div className="flex items-center gap-4 my-4">
                        <div className="flex-1 h-px bg-gray-200"></div>
                        <span className="text-gray-400 text-sm">ou</span>
                        <div className="flex-1 h-px bg-gray-200"></div>
                    </div>

                    <a
                        href="/teacher/login"
                        className="inline-block w-full py-3 border-2 border-primary text-primary font-semibold rounded-lg hover:bg-primary hover:text-white transition"
                    >
                        👨‍🏫 Espace Professeur (Classique)
                    </a>

                    <div>
                        <Link to="/" className="text-gray-400 text-sm hover:text-primary transition">
                            ← Retour à l'accueil
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    );
}

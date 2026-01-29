import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { register, login as apiLogin } from '../api/auth';
import { useAuth } from '../context/AuthContext';

export default function Register() {
    const navigate = useNavigate();
    const { login } = useAuth();
    const [formData, setFormData] = useState({
        email: '',
        plainPassword: '',
        firstName: '',
        lastName: '',
        userType: 'student' // Default to student
    });
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');

    const handleChange = (e) => {
        setFormData({
            ...formData,
            [e.target.name]: e.target.value
        });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');
        setLoading(true);

        try {
            // Choose endpoint based on user type
            const endpoint = formData.userType === 'teacher' ? '/api/teachers' : '/api/students';
            await register({
                email: formData.email,
                plainPassword: formData.plainPassword,
                firstName: formData.firstName,
                lastName: formData.lastName,
                enrollmentDate: formData.userType === 'student' ? new Date().toISOString() : undefined
            }, endpoint);

            // Auto-login after successful registration
            try {
                const user = await login(formData.email, formData.plainPassword);

                // Redirect based on role
                if (user.roles && user.roles.includes('ROLE_TEACHER')) {
                    window.location.href = '/courses';
                } else {
                    navigate('/courses');
                }
            } catch (loginErr) {
                // If auto-login fails, redirect to login page with success message
                console.warn('Auto-login failed, redirecting to login page', loginErr);
                navigate('/login', { state: { message: 'Inscription réussie ! Connectez-vous.' } });
            }
        } catch (err) {
            console.error(err);
            if (err.response && err.response.data) {
                if (err.response.data.violations) {
                    const messages = err.response.data.violations.map(v => `${v.propertyPath}: ${v.message}`).join(', ');
                    setError(messages);
                } else if (err.response.data.detail) {
                    setError(err.response.data.detail);
                } else if (err.response.data.message) {
                    setError(err.response.data.message);
                } else {
                    setError('Erreur lors de l\'inscription.');
                }
            } else {
                setError('Erreur lors de l\'inscription. Vérifiez votre connexion.');
            }
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="min-h-screen hero-gradient flex items-center justify-center px-4">
            <div className="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md animate-slideDown">
                <div className="text-center mb-8">
                    <Link to="/" className="logo-gradient no-underline text-3xl">
                        🎓 EduLearn
                    </Link>
                    <p className="text-gray-500 mt-4">Créer un compte</p>
                </div>

                {error && (
                    <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm">
                        {error}
                    </div>
                )}

                <form onSubmit={handleSubmit} className="space-y-4">
                    {/* User Type Selector */}
                    <div className="flex rounded-lg overflow-hidden border border-gray-200">
                        <button
                            type="button"
                            onClick={() => setFormData({ ...formData, userType: 'student' })}
                            className={`flex-1 py-3 text-sm font-semibold transition ${formData.userType === 'student'
                                ? 'bg-primary text-white'
                                : 'bg-gray-50 text-gray-600 hover:bg-gray-100'
                                }`}
                        >
                            🎓 Étudiant
                        </button>
                        <button
                            type="button"
                            onClick={() => setFormData({ ...formData, userType: 'teacher' })}
                            className={`flex-1 py-3 text-sm font-semibold transition ${formData.userType === 'teacher'
                                ? 'bg-primary text-white'
                                : 'bg-gray-50 text-gray-600 hover:bg-gray-100'
                                }`}
                        >
                            👨‍🏫 Professeur
                        </button>
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-semibold text-dark mb-2">
                                Prénom
                            </label>
                            <input
                                type="text"
                                name="firstName"
                                value={formData.firstName}
                                onChange={handleChange}
                                className="form-input"
                                placeholder="Jean"
                                required
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-semibold text-dark mb-2">
                                Nom
                            </label>
                            <input
                                type="text"
                                name="lastName"
                                value={formData.lastName}
                                onChange={handleChange}
                                className="form-input"
                                placeholder="Dupont"
                                required
                            />
                        </div>
                    </div>

                    <div>
                        <label className="block text-sm font-semibold text-dark mb-2">
                            Adresse email
                        </label>
                        <input
                            type="email"
                            name="email"
                            value={formData.email}
                            onChange={handleChange}
                            className="form-input"
                            placeholder="votre@email.com"
                            required
                        />
                    </div>

                    <div>
                        <label className="block text-sm font-semibold text-dark mb-2">
                            Mot de passe
                        </label>
                        <input
                            type="password"
                            name="plainPassword"
                            value={formData.plainPassword}
                            onChange={handleChange}
                            className="form-input"
                            placeholder="••••••••"
                            required
                            minLength={6}
                        />
                    </div>

                    <button
                        type="submit"
                        disabled={loading}
                        className="w-full py-4 hero-gradient text-white font-bold rounded-lg hover:-translate-y-0.5 transition disabled:opacity-50"
                        style={{ boxShadow: '0 8px 20px rgba(37, 99, 235, 0.3)' }}
                    >
                        {loading ? (
                            <div className="flex items-center justify-center gap-2">
                                <div className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin" />
                                <span>Inscription...</span>
                            </div>
                        ) : 'S\'inscrire'}
                    </button>
                </form>

                <div className="text-center mt-6">
                    <p className="text-sm text-gray-500">
                        Déjà inscrit ?{' '}
                        <Link to="/login" className="text-primary font-bold hover:underline">
                            Se connecter
                        </Link>
                    </p>

                    <div className="mt-4">
                        <Link to="/" className="text-gray-400 text-sm hover:text-primary transition">
                            ← Retour à l'accueil
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    );
}

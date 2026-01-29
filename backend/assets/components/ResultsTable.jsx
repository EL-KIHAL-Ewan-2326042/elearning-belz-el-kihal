import React, { useState } from 'react';

const mockResults = [
    {
        id: 1,
        student: 'Alice Martin',
        qcm: 'Introduction à Symfony',
        date: '15/01/2026',
        score: '18/20',
        grade: 'excellent',
        gradeLabel: 'Excellent',
        details: {
            totalQuestions: 10,
            correct: 9,
            incorrect: 1,
            time: '18 min',
            questions: [
                { text: 'Qu\'est-ce que Symfony ?', correct: true },
                { text: 'Architecture MVC', correct: true },
                { text: 'Les bundles Symfony', correct: true },
                { text: 'Doctrine ORM', correct: false },
                { text: 'Routing', correct: true },
            ]
        }
    },
    {
        id: 2,
        student: 'Thomas Dubois',
        qcm: 'Security Bundle',
        date: '14/01/2026',
        score: '15/20',
        grade: 'good',
        gradeLabel: 'Bien',
        details: {
            totalQuestions: 10,
            correct: 7,
            incorrect: 3,
            time: '22 min',
            questions: [
                { text: 'Authentification', correct: true },
                { text: 'Firewall', correct: false },
                { text: 'Voters', correct: true },
                { text: 'JWT Tokens', correct: false },
                { text: 'Encodeurs', correct: true },
            ]
        }
    },
    {
        id: 3,
        student: 'Sophie Bernard',
        qcm: 'API Platform',
        date: '13/01/2026',
        score: '12/20',
        grade: 'average',
        gradeLabel: 'Moyen',
        details: {
            totalQuestions: 10,
            correct: 6,
            incorrect: 4,
            time: '25 min',
            questions: [
                { text: 'REST API', correct: true },
                { text: 'Serialization', correct: false },
                { text: 'Filters', correct: true },
                { text: 'Pagination', correct: false },
                { text: 'Operations', correct: true },
            ]
        }
    },
    {
        id: 4,
        student: 'Lucas Laurent',
        qcm: 'Doctrine ORM',
        date: '12/01/2026',
        score: '9/20',
        grade: 'poor',
        gradeLabel: 'À améliorer',
        details: {
            totalQuestions: 10,
            correct: 4,
            incorrect: 6,
            time: '28 min',
            questions: [
                { text: 'Entities', correct: true },
                { text: 'Repositories', correct: false },
                { text: 'Migrations', correct: false },
                { text: 'Relations', correct: false },
                { text: 'DQL', correct: true },
            ]
        }
    },
    {
        id: 5,
        student: 'Emma Petit',
        qcm: 'Twig Templates',
        date: '11/01/2026',
        score: '17/20',
        grade: 'excellent',
        gradeLabel: 'Excellent',
        details: {
            totalQuestions: 10,
            correct: 8,
            incorrect: 2,
            time: '16 min',
            questions: [
                { text: 'Syntaxe Twig', correct: true },
                { text: 'Héritage', correct: true },
                { text: 'Filtres', correct: false },
                { text: 'Blocks', correct: true },
                { text: 'Extensions', correct: true },
            ]
        }
    },
];

function ResultsTable() {
    const [expandedRow, setExpandedRow] = useState(null);

    const toggleDetails = (id) => {
        setExpandedRow(expandedRow === id ? null : id);
    };

    const exportResults = () => {
        alert('Export CSV en cours... 📥\n\nLes résultats seront téléchargés dans quelques secondes.');
    };

    const getScoreClass = (grade) => {
        switch (grade) {
            case 'excellent': return 'score-excellent';
            case 'good': return 'score-good';
            case 'average': return 'score-average';
            case 'poor': return 'score-poor';
            default: return '';
        }
    };

    return (
        <div className="bg-white rounded-2xl p-8 shadow-lg">
            <div className="flex justify-between items-center mb-5">
                <h2 className="text-2xl font-bold text-dark">📊 Résultats des QCM</h2>
                <button
                    className="btn btn-success"
                    onClick={exportResults}
                >
                    📥 Exporter en CSV
                </button>
            </div>
            <div className="overflow-x-auto">
                <table className="w-full border-collapse rounded-xl overflow-hidden">
                    <thead className="hero-gradient text-white">
                        <tr>
                            <th className="p-4 text-left">Étudiant</th>
                            <th className="p-4 text-left">QCM</th>
                            <th className="p-4 text-left">Date</th>
                            <th className="p-4 text-left">Score</th>
                            <th className="p-4 text-left">Note</th>
                            <th className="p-4 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {mockResults.map((result) => (
                            <React.Fragment key={result.id}>
                                <tr className="border-b border-light hover:bg-light transition-all">
                                    <td className="p-4 font-bold">{result.student}</td>
                                    <td className="p-4">{result.qcm}</td>
                                    <td className="p-4">{result.date}</td>
                                    <td className="p-4">{result.score}</td>
                                    <td className="p-4">
                                        <span className={`score-badge ${getScoreClass(result.grade)}`}>
                                            {result.gradeLabel}
                                        </span>
                                    </td>
                                    <td className="p-4">
                                        <button
                                            className="btn btn-outline py-2 px-4 text-sm"
                                            onClick={() => toggleDetails(result.id)}
                                        >
                                            {expandedRow === result.id ? 'Masquer' : 'Voir détails'}
                                        </button>
                                    </td>
                                </tr>
                                {expandedRow === result.id && (
                                    <tr>
                                        <td colSpan="6" className="p-0">
                                            <div className="bg-light rounded-lg p-5 m-2 animate-slideDown">
                                                <div className="grid grid-cols-4 gap-4 mb-4 md:grid-cols-2">
                                                    <div className="bg-white p-4 rounded-lg border-l-4 border-primary">
                                                        <div className="text-xs text-slate-500 uppercase font-semibold mb-1">
                                                            📝 Nombre de questions
                                                        </div>
                                                        <div className="text-lg font-bold text-dark">
                                                            {result.details.totalQuestions}
                                                        </div>
                                                    </div>
                                                    <div className="bg-white p-4 rounded-lg border-l-4 border-success">
                                                        <div className="text-xs text-slate-500 uppercase font-semibold mb-1">
                                                            ✅ Bonnes réponses
                                                        </div>
                                                        <div className="text-lg font-bold text-dark">
                                                            {result.details.correct}
                                                        </div>
                                                    </div>
                                                    <div className="bg-white p-4 rounded-lg border-l-4 border-danger">
                                                        <div className="text-xs text-slate-500 uppercase font-semibold mb-1">
                                                            ❌ Mauvaises réponses
                                                        </div>
                                                        <div className="text-lg font-bold text-dark">
                                                            {result.details.incorrect}
                                                        </div>
                                                    </div>
                                                    <div className="bg-white p-4 rounded-lg border-l-4 border-secondary">
                                                        <div className="text-xs text-slate-500 uppercase font-semibold mb-1">
                                                            ⏱️ Temps passé
                                                        </div>
                                                        <div className="text-lg font-bold text-dark">
                                                            {result.details.time}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div className="bg-white p-4 rounded-lg">
                                                    <h4 className="text-sm font-bold text-dark mb-3">📋 Détail des questions</h4>
                                                    {result.details.questions.map((q, idx) => (
                                                        <div key={idx} className="flex justify-between items-center py-2 border-b border-light last:border-0 text-sm">
                                                            <span>Question {idx + 1} : {q.text}</span>
                                                            <div className="flex items-center gap-1">
                                                                <span>{q.correct ? '✅' : '❌'}</span>
                                                                <span>{q.correct ? 'Correcte' : 'Incorrecte'}</span>
                                                            </div>
                                                        </div>
                                                    ))}
                                                </div>
                                                <button
                                                    className="btn btn-danger mt-4"
                                                    onClick={() => toggleDetails(result.id)}
                                                >
                                                    Fermer
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                )}
                            </React.Fragment>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}

export default ResultsTable;

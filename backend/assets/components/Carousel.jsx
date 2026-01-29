import React, { useRef } from 'react';
import { useAuth } from './AuthContext';

function Carousel({ title, icon, items, type }) {
    const carouselRef = useRef(null);
    const { isAuthenticated, isTeacher } = useAuth();

    const scroll = (amount) => {
        if (carouselRef.current) {
            carouselRef.current.scrollBy({ left: amount, behavior: 'smooth' });
        }
    };

    const handleGenerateQCM = (item) => {
        if (confirm(`Voulez-vous générer un QCM automatique pour ${type === 'video' ? 'cette vidéo' : 'ce document'} ?`)) {
            alert(`QCM généré avec succès ! 🎉\n\n• 10 questions créées\n• Difficulté progressive\n• Disponible pour les étudiants`);
        }
    };

    const handleTakeQCM = (item) => {
        alert(`🎓 Démarrage du QCM : ${item.title}\n\n✓ 10 questions\n✓ Durée : 20 minutes\n✓ Note sur 20\n\nBonne chance ! 🍀`);

        setTimeout(() => {
            const score = Math.floor(Math.random() * 10) + 10;
            const message = score >= 16 ? '🌟 Excellent travail !' :
                score >= 13 ? '👍 Bien joué !' :
                    score >= 10 ? '💪 Continuez vos efforts !' :
                        '📚 Révisez et réessayez !';

            alert(`✅ QCM Terminé !\n\nVotre score : ${score}/20\n${message}`);
        }, 1000);
    };

    const gradientClass = type === 'video' ? 'video-gradient' : 'document-gradient';
    const itemIcon = type === 'video' ? '🎬' : '📑';

    return (
        <div className="bg-white rounded-2xl p-8 mb-10 shadow-lg">
            <div className="flex justify-between items-center mb-5">
                <h2 className="text-2xl font-bold text-dark">
                    {icon} {title}
                </h2>
                <div className="flex gap-2">
                    <button
                        className="w-10 h-10 border-none bg-light rounded-full cursor-pointer flex items-center justify-center text-lg text-dark hover:bg-primary hover:text-white transition-all"
                        onClick={() => scroll(-300)}
                    >
                        ←
                    </button>
                    <button
                        className="w-10 h-10 border-none bg-light rounded-full cursor-pointer flex items-center justify-center text-lg text-dark hover:bg-primary hover:text-white transition-all"
                        onClick={() => scroll(300)}
                    >
                        →
                    </button>
                </div>
            </div>
            <div
                ref={carouselRef}
                className="flex gap-5 overflow-x-auto scroll-smooth py-2 carousel-scroll"
            >
                {items.map((item, index) => (
                    <div key={index} className="carousel-item">
                        <div className={`w-full h-44 ${gradientClass} flex items-center justify-center text-6xl text-white relative`}>
                            {itemIcon}
                            {type === 'video' && (
                                <div className="absolute w-14 h-14 bg-white/90 rounded-full flex items-center justify-center text-2xl text-primary">
                                    ▶
                                </div>
                            )}
                        </div>
                        <div className="p-5">
                            <div className="text-base font-bold text-dark mb-2">{item.title}</div>
                            <div className="text-sm text-slate-500 mb-3">
                                👨‍🏫 {item.professor} • {type === 'video' ? `⏱️ ${item.duration}` : `📄 ${item.pages} pages`}
                            </div>
                            {isAuthenticated && isTeacher && (
                                <button
                                    className="w-full py-2.5 bg-success text-white border-none rounded-lg font-semibold cursor-pointer transition-all flex items-center justify-center gap-2 hover:bg-emerald-600 mb-2"
                                    onClick={() => handleGenerateQCM(item)}
                                >
                                    🤖 Générer QCM (Prof)
                                </button>
                            )}
                            <button
                                className="w-full py-2.5 bg-primary text-white border-none rounded-lg font-semibold cursor-pointer transition-all flex items-center justify-center gap-2 hover:bg-secondary"
                                onClick={() => handleTakeQCM(item)}
                            >
                                ✏️ Passer le QCM
                            </button>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}

export default Carousel;

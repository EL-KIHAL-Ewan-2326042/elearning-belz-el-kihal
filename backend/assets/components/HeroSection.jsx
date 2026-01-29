import React from 'react';

function HeroSection() {
    return (
        <div className="hero-gradient text-white py-20 px-5 text-center mb-10">
            <h1 className="text-5xl font-extrabold mb-5 md:text-4xl">
                Plateforme d'Apprentissage Moderne
            </h1>
            <p className="text-xl opacity-90 max-w-xl mx-auto mb-8">
                Accédez à vos cours, regardez des vidéos, consultez des documents et passez des QCM générés par IA
            </p>
            <div className="flex gap-10 justify-center mt-10 md:flex-col md:gap-5">
                <div className="text-center">
                    <div className="text-4xl font-extrabold">150+</div>
                    <div className="text-sm opacity-80">Cours disponibles</div>
                </div>
                <div className="text-center">
                    <div className="text-4xl font-extrabold">2,500+</div>
                    <div className="text-sm opacity-80">Étudiants actifs</div>
                </div>
                <div className="text-center">
                    <div className="text-4xl font-extrabold">98%</div>
                    <div className="text-sm opacity-80">Taux de satisfaction</div>
                </div>
            </div>
        </div>
    );
}

export default HeroSection;

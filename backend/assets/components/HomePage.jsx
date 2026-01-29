import React from 'react';
import HeroSection from './HeroSection';
import Carousel from './Carousel';
import ResultsTable from './ResultsTable';

// Mock data for videos
const videoItems = [
    { title: 'Introduction à Symfony', professor: 'Prof. Martin', duration: '45 min' },
    { title: 'Security Bundle', professor: 'Prof. Dubois', duration: '60 min' },
    { title: 'API Platform', professor: 'Prof. Bernard', duration: '50 min' },
    { title: 'Doctrine ORM', professor: 'Prof. Laurent', duration: '55 min' },
    { title: 'Twig Templates', professor: 'Prof. Sophie', duration: '40 min' },
];

// Mock data for documents
const documentItems = [
    { title: 'Guide Symfony 7', professor: 'Prof. Martin', pages: 25 },
    { title: 'Architecture MVC', professor: 'Prof. Dubois', pages: 18 },
    { title: 'REST API Best Practices', professor: 'Prof. Bernard', pages: 30 },
    { title: 'Database Design', professor: 'Prof. Laurent', pages: 22 },
    { title: 'Frontend avec Twig', professor: 'Prof. Sophie', pages: 15 },
];

function HomePage() {
    return (
        <div>
            <HeroSection />

            <div className="max-w-6xl mx-auto px-5 py-10">
                <Carousel
                    title="Vidéos de Cours"
                    icon="📹"
                    items={videoItems}
                    type="video"
                />

                <Carousel
                    title="Documents de Cours"
                    icon="📄"
                    items={documentItems}
                    type="document"
                />

                <ResultsTable />
            </div>
        </div>
    );
}

export default HomePage;

<?php

namespace App\DataFixtures;

use App\Entity\Answer;
use App\Entity\Course;
use App\Entity\Document;
use App\Entity\Question;
use App\Entity\Quiz;
use App\Entity\Student;
use App\Entity\Teacher;
use App\Entity\Video;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // --- PROFESSORS ---
        $teacher1 = new Teacher();
        $teacher1->setEmail('samir.chtioui@example.com');
        $teacher1->setFirstName('Samir');
        $teacher1->setLastName('Chtioui');
        $teacher1->setPassword($this->userPasswordHasher->hashPassword($teacher1, 'password123'));
        $manager->persist($teacher1);

        $teacher2 = new Teacher();
        $teacher2->setEmail('see.tickets@example.com');
        $teacher2->setFirstName('See');
        $teacher2->setLastName('Tickets');
        $teacher2->setPassword($this->userPasswordHasher->hashPassword($teacher2, 'password123'));
        $manager->persist($teacher2);

        // --- STUDENTS ---
        $student1 = new Student();
        $student1->setEmail('matteo.belz@example.com');
        $student1->setFirstName('Matteo');
        $student1->setLastName('Belz');
        $student1->setPassword($this->userPasswordHasher->hashPassword($student1, 'password123'));
        $manager->persist($student1);

        $student2 = new Student();
        $student2->setEmail('ewan.elkihal@example.com');
        $student2->setFirstName('Ewan');
        $student2->setLastName('El Kihal');
        $student2->setPassword($this->userPasswordHasher->hashPassword($student2, 'password123'));
        $manager->persist($student2);

        // --- COURSE 1: "Introduction à la Mobilité" (Samir Chtioui) ---
        $course1 = new Course();
        $course1->setTitle('Introduction à la Mobilité');
        $course1->setDescription('Un cours complet sur les enjeux de la mobilité urbaine et durable.');
        $course1->setSummary('Découvrez les concepts clés de la mobilité moderne.');
        $course1->setTeacher($teacher1);
        $manager->persist($course1);

        // Document for Course 1
        $doc1 = new Document();
        $doc1->setTitle('Guide Mobilité 2023');
        $doc1->setDescription('Le guide complet pour comprendre la mobilité en 2023.');
        $doc1->setFileName('guide-mobilite-2023-web-6982104b3073a360958632.pdf');
        $doc1->setUploadedAt(new \DateTime());
        $doc1->setCourse($course1);
        $manager->persist($doc1);

        // Quiz for Course 1 "Introduction à la Mobilité"
        $quiz1 = new Quiz();
        $quiz1->setTitle('QCM - Introduction à la Mobilité');
        $quiz1->setDescription('Testez vos connaissances sur la mobilité urbaine.');
        $quiz1->setCourse($course1);
        $quiz1->setIsGeneratedByAI(true);
        $manager->persist($quiz1);

        // Questions for Quiz 1
        $q1_1 = new Question();
        $q1_1->setContent('Quel mode de transport est considéré comme le plus écologique en ville ?');
        $q1_1->setOrderNumber(1);
        $q1_1->setPoints(2);
        $q1_1->setQuiz($quiz1);
        $manager->persist($q1_1);

        $this->addAnswer($manager, $q1_1, 'La voiture individuelle', false);
        $this->addAnswer($manager, $q1_1, 'Le vélo', true);
        $this->addAnswer($manager, $q1_1, 'Le scooter thermique', false);

        $q1_2 = new Question();
        $q1_2->setContent('Qu\'est-ce que l\'intermodalité ?');
        $q1_2->setOrderNumber(2);
        $q1_2->setPoints(2);
        $q1_2->setQuiz($quiz1);
        $manager->persist($q1_2);

        $this->addAnswer($manager, $q1_2, 'Utiliser plusieurs modes de transport pour un même trajet', true);
        $this->addAnswer($manager, $q1_2, 'Ne jamais changer de moyen de transport', false);
        $this->addAnswer($manager, $q1_2, 'Utiliser uniquement les transports en commun', false);

        $q1_3 = new Question();
        $q1_3->setContent('Quel est l\'objectif principal des Zones à Faibles Émissions (ZFE) ?');
        $q1_3->setOrderNumber(3);
        $q1_3->setPoints(2);
        $q1_3->setQuiz($quiz1);
        $manager->persist($q1_3);

        $this->addAnswer($manager, $q1_3, 'Interdire tous les véhicules', false);
        $this->addAnswer($manager, $q1_3, 'Améliorer la qualité de l\'air en limitant les véhicules polluants', true);
        $this->addAnswer($manager, $q1_3, 'Augmenter la vitesse de circulation', false);

        $q1_4 = new Question();
        $q1_4->setContent('Parmi ces options, laquelle favorise le covoiturage ?');
        $q1_4->setOrderNumber(4);
        $q1_4->setPoints(2);
        $q1_4->setQuiz($quiz1);
        $manager->persist($q1_4);

        $this->addAnswer($manager, $q1_4, 'Avoir une voiture chacun', false);
        $this->addAnswer($manager, $q1_4, 'Les voies réservées au covoiturage', true);
        $this->addAnswer($manager, $q1_4, 'L\'augmentation du prix de l\'essence', false);

        $q1_5 = new Question();
        $q1_5->setContent('La mobilité douce inclut :');
        $q1_5->setOrderNumber(5);
        $q1_5->setPoints(2);
        $q1_5->setQuiz($quiz1);
        $manager->persist($q1_5);

        $this->addAnswer($manager, $q1_5, 'La marche et le vélo', true);
        $this->addAnswer($manager, $q1_5, 'Le camion et le bus', false);
        $this->addAnswer($manager, $q1_5, 'L\'avion et le train', false);


        // --- COURSE 2: "Projet de Groupe SAE 105" (See Tickets) ---
        $course2 = new Course();
        $course2->setTitle('Projet de Groupe SAE 105');
        $course2->setDescription('Présentation et analyse du projet de groupe SAE 105.');
        $course2->setSummary('Analyse détaillée du projet SAE 105.');
        $course2->setTeacher($teacher2);
        $manager->persist($course2);

        // Video for Course 2
        $vid2 = new Video();
        $vid2->setTitle('Vidéo SAE 105');
        $vid2->setDescription('Vidéo de présentation du projet réalisé par le groupe 2.');
        $vid2->setFileName('sae105-g2-f-curt-el-kihal-turmo-valente-698326255536c085745861.mp4');
        $vid2->setUploadedAt(new \DateTime());
        $vid2->setCourse($course2);
        $manager->persist($vid2);

        // Quiz for Course 2 "Projet de Groupe SAE 105"
        $quiz2 = new Quiz();
        $quiz2->setTitle('QCM - Projet SAE 105');
        $quiz2->setDescription('Évaluation des connaissances sur le projet SAE 105.');
        $quiz2->setCourse($course2);
        $quiz2->setIsGeneratedByAI(true);
        $manager->persist($quiz2);

        // Questions for Quiz 2
        $q2_1 = new Question();
        $q2_1->setContent('Quel est l\'objectif principal de la SAE 105 ?');
        $q2_1->setOrderNumber(1);
        $q2_1->setPoints(2);
        $q2_1->setQuiz($quiz2);
        $manager->persist($q2_1);

        $this->addAnswer($manager, $q2_1, 'Réaliser un site web statique', false);
        $this->addAnswer($manager, $q2_1, 'Produire et intégrer des contenus multimédias', true);
        $this->addAnswer($manager, $q2_1, 'Apprendre le langage C++', false);

        $q2_2 = new Question();
        $q2_2->setContent('Quels types de médias sont généralement intégrés dans ce projet ?');
        $q2_2->setOrderNumber(2);
        $q2_2->setPoints(2);
        $q2_2->setQuiz($quiz2);
        $manager->persist($q2_2);

        $this->addAnswer($manager, $q2_2, 'Du texte uniquement', false);
        $this->addAnswer($manager, $q2_2, 'Images, vidéos et sons', true);
        $this->addAnswer($manager, $q2_2, 'Des bases de données relationnelles', false);

        $q2_3 = new Question();
        $q2_3->setContent('Quelle compétence est essentielle pour réussir ce projet de groupe ?');
        $q2_3->setOrderNumber(3);
        $q2_3->setPoints(2);
        $q2_3->setQuiz($quiz2);
        $manager->persist($q2_3);

        $this->addAnswer($manager, $q2_3, 'Le travail en équipe et la communication', true);
        $this->addAnswer($manager, $q2_3, 'Savoir travailler seul dans son coin', false);
        $this->addAnswer($manager, $q2_3, 'Être un expert en cybersécurité', false);

        $q2_4 = new Question();
        $q2_4->setContent('Quel logiciel est souvent utilisé pour le montage vidéo dans ce contexte ?');
        $q2_4->setOrderNumber(4);
        $q2_4->setPoints(2);
        $q2_4->setQuiz($quiz2);
        $manager->persist($q2_4);

        $this->addAnswer($manager, $q2_4, 'Microsoft Excel', false);
        $this->addAnswer($manager, $q2_4, 'Adobe Premiere Pro ou DaVinci Resolve', true);
        $this->addAnswer($manager, $q2_4, 'Notepad++', false);

        $q2_5 = new Question();
        $q2_5->setContent('Comment est évalué le projet final ?');
        $q2_5->setOrderNumber(5);
        $q2_5->setPoints(2);
        $q2_5->setQuiz($quiz2);
        $manager->persist($q2_5);

        $this->addAnswer($manager, $q2_5, 'Sur la qualité technique et artistique', true);
        $this->addAnswer($manager, $q2_5, 'Uniquement sur la durée de la vidéo', false);
        $this->addAnswer($manager, $q2_5, 'Au poids du fichier rendu', false);


        $manager->flush();
    }

    private function addAnswer(ObjectManager $manager, Question $question, string $content, bool $isCorrect): void
    {
        $answer = new Answer();
        $answer->setContent($content);
        $answer->setIsCorrect($isCorrect);
        $answer->setQuestion($question);
        $manager->persist($answer);
    }
}

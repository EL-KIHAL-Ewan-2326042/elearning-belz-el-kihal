<?php

namespace App\Command;

use App\Entity\Quiz;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(name: 'debug:quiz-serialization')]
class DebugQuizCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private SerializerInterface $serializer
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $quiz = $this->em->getRepository(Quiz::class)->find(3);

        if (!$quiz) {
            $output->writeln("Quiz 3 not found");
            return Command::FAILURE;
        }

        $output->writeln("Quiz found: " . $quiz->getTitle());

        $json = $this->serializer->serialize($quiz, 'json', ['groups' => ['quiz:read']]);
        $data = json_decode($json, true);

        if (!empty($data['questions'])) {
            $q = $data['questions'][0];
            $output->writeln("First Question: " . $q['content']);
            $output->writeln("Answers Dump:");
            foreach ($q['answers'] as $a) {
                // Manually build string to avoid print_r formatting issues in some consoles
                $line = " - [id: " . $a['id'] . "] ";
                foreach ($a as $k => $v) {
                    $val = is_bool($v) ? ($v ? 'true' : 'false') : (is_string($v) ? $v : json_encode($v));
                    $line .= "$k=$val | ";
                }
                $output->writeln($line);
            }
        } else {
            $output->writeln("No questions found in serialization.");
        }

        return Command::SUCCESS;
    }
}

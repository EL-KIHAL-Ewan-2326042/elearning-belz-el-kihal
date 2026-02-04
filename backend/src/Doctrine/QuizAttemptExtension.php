<?php

namespace App\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\QuizAttempt;
use App\Entity\Student;
use App\Entity\Teacher;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

final class QuizAttemptExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, Operation $operation = null, array $context = []): void
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, Operation $operation = null, array $context = []): void
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        if (QuizAttempt::class !== $resourceClass) {
            return;
        }

        $user = $this->security->getUser();

        // If not logged in, filter to nothing
        if (!$user) {
            $rootAlias = $queryBuilder->getRootAliases()[0];
            $queryBuilder->andWhere(sprintf('%s.id IS NULL', $rootAlias));
            return;
        }

        // If Teacher or Admin, return all (or implemented logic later)
        if ($this->security->isGranted('ROLE_TEACHER') || $this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        // Detailed student check
        if ($user instanceof Student || in_array('ROLE_STUDENT', $user->getRoles())) {
            $rootAlias = $queryBuilder->getRootAliases()[0];
            // Use explicit join to ensure we match the Student entity correctly
            $queryBuilder->join(sprintf('%s.student', $rootAlias), 's');
            $queryBuilder->andWhere('s.id = :current_user_id');
            $queryBuilder->setParameter('current_user_id', $user->getId());
        }
    }
}

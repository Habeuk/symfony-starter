<?php
namespace App\Repository;

use App\Entity\User;
use Habeuk\HbkSymfony\Security\QueryFilter\EntityVisibilityFilter;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Habeuk\HbkSymfony\Repository\BaseRepository;

/**
 *
 * @extends BaseRepository<User>
 */
class UserRepository extends BaseRepository implements PasswordUpgraderInterface {

  public function __construct(ManagerRegistry $registry, EntityVisibilityFilter $entityVisibilityFilter) {
    parent::__construct($registry, User::class, $entityVisibilityFilter);
  }

  /**
   * Used to upgrade (rehash) the user's password automatically over time.
   */
  public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void {
    if (! $user instanceof User) {
      throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
    }
    $user->setHashedPassword($newHashedPassword);
    $this->getEntityManager()->persist($user);
    $this->getEntityManager()->flush();
  }
}

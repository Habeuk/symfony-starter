<?php
namespace App\Enum;

use Habeuk\HbkSymfony\Enum\RoleInterface;
use Habeuk\HbkSymfony\Enum\BaseEnumTrait;

/**
 * Rôles utilisateurs pour le système de facturation photographe
 */
enum Role: string implements RoleInterface {
  use BaseEnumTrait;

  /**
   * Possedent un compte.
   */
  case ROLE_ANONYMOUS = 'ROLE_ANONYMOUS';

  /**
   * Possedent un compte.
   */
  case ROLE_USER = 'ROLE_USER';

  /**
   * possedent un abonnement.
   */
  case ROLE_SUBSCRIBER = 'ROLE_SUBSCRIBER';

  case ROLE_MODERATOR = 'ROLE_MODERATOR';

  case ROLE_ADMIN = 'ROLE_ADMIN';

  case ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

  /**
   * Vérifie si un rôle est valide
   */
  public static function isValid(string $role): bool {
    return in_array($role, self::values(), true);
  }

  /**
   * Retourne le libellé lisible du rôle
   */
  public function getLabel(): string {
    return match ($this) {
      self::ROLE_ANONYMOUS => 'Utilisateur anonyme',
      self::ROLE_USER => 'Utilisateur standard',
      self::ROLE_SUBSCRIBER => 'Utilisateur premium (abonné)',
      self::ROLE_MODERATOR => 'Modérateur (support)',
      self::ROLE_ADMIN => 'Administrateur',
      self::ROLE_SUPER_ADMIN => 'Super administrateur'
    };
  }

  /**
   *
   * @return array<static>
   */
  public function inherits(): array {
    return match ($this) {
      self::ROLE_ANONYMOUS => [
        self::ROLE_ANONYMOUS
      ],
      self::ROLE_USER => [
        self::ROLE_USER
      ],
      self::ROLE_SUBSCRIBER => [
        self::ROLE_USER,
        self::ROLE_SUBSCRIBER
      ],
      self::ROLE_MODERATOR => [
        self::ROLE_USER,
        self::ROLE_SUBSCRIBER,
        self::ROLE_MODERATOR
      ],
      self::ROLE_ADMIN => [
        self::ROLE_USER,
        self::ROLE_SUBSCRIBER,
        self::ROLE_MODERATOR,
        self::ROLE_ADMIN
      ],
      self::ROLE_SUPER_ADMIN => self::cases()
    };
  }
}
<?php
// src/Enum/ScopeEnum.php
namespace App\Enum;

use Habeuk\HbkSymfony\Enum\ScopeEnumInterface;
use Habeuk\HbkSymfony\Enum\BaseEnumTrait;

/**
 * Portée des données
 */
enum ScopeEnum: string implements ScopeEnumInterface {
  use BaseEnumTrait;

  case PERSONAL = 'personal';

  // Uniquement les données de l'utilisateur
  case TEAM = 'team';

  // Données de l'équipe
  case GLOBAL = 'global';

  // Toutes les données (admin)
  case RESTRICTED = 'restricted';

  // Accès restreint spécifique
  public function getLabel(): string {
    return match ($this) {
      self::PERSONAL => 'Personnel',
      self::TEAM => 'Équipe',
      self::GLOBAL => 'Global',
      self::RESTRICTED => 'Restreint'
    };
  }

  public function getDescription(): string {
    return match ($this) {
      self::PERSONAL => 'L\'utilisateur ne voit que ses propres données',
      self::TEAM => 'Les membres de l\'équipe partagent les données',
      self::GLOBAL => 'Tous les utilisateurs voient toutes les données',
      self::RESTRICTED => 'Accès basé sur des règles spécifiques'
    };
  }

  public function isPersonal(): bool {
    return $this === self::PERSONAL;
  }

  public function isGlobal(): bool {
    return $this === self::GLOBAL;
  }

  public function isRestricted(): bool {
    return $this === self::RESTRICTED;
  }

  public function isTeam(): bool {
    return $this === self::TEAM;
  }

  public function getPersonal(): static {
    return self::PERSONAL;
  }

  public function getTeam(): static {
    return self::TEAM;
  }

  public function getGlobal(): static {
    return self::GLOBAL;
  }

  public function getRestricted(): static {
    return self::RESTRICTED;
  }
}

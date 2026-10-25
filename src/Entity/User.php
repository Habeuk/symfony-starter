<?php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Habeuk\HbkSymfony\Attribute\MenuFrontendConfig;
use App\Enum\Role;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Habeuk\HbkSymfony\Enum\PermissionEnum;
use Symfony\Component\ObjectMapper\Attribute\Map;
use App\DTO\UserDto;
use App\Enum\ScopeEnum;
use Habeuk\HbkSymfony\Shared\Doctrine\BaseUser;
use Doctrine\DBAL\Types\Types;
use Habeuk\HbkSymfony\Enum\RoleInterface;

#[Map(target: UserDto::class)]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
// Effectue une requette afin de verifier l'email.
#[UniqueEntity(fields: [
  'email'
], message: 'Un compte existe déjà avec cet email')]
// Creer une clée afin d'eviter les doublons meme via les imports.
#[ORM\UniqueConstraint(name: 'UNIQ_USER_EMAIL', fields: [
  'email'
])]
#[MenuFrontendConfig(enabled: true, label: "Utilisateurs", entity: "User", icon: "pi pi-user", order: 1, permissions: [
  PermissionEnum::VIEW,
  PermissionEnum::CREATE,
  PermissionEnum::EDIT,
  PermissionEnum::DELETE
], scope: ScopeEnum::GLOBAL, requireOwnership: false, roles: [
  "ROLE_ANONYMOUS",
  "ROLE_USER"
])]
#[ORM\HasLifecycleCallbacks]
class User extends BaseUser {

  /**
   *
   * @var array<Role>
   */
  #[ORM\Column(type: Types::SIMPLE_ARRAY, enumType: Role::class)]
  private array $roles = [
    Role::ROLE_USER
  ];

  /**
   *
   * @return array<string>
   */
  public function getRoles(): array {
    $roles = [];
    foreach ($this->roles as $role) {
      /** @var Role $role */
      $roles[] = $role->getValue();
    }
    return $roles;
  }

  /**
   *
   * @param array<string> $rolesString
   */
  public function setRoles(array $rolesString): self {
    $uniqueRoles = array_unique($rolesString);
    $this->roles = Role::createFromValues($uniqueRoles);
    return $this;
  }

  public function addRole(string $role_string): self {
    $role = $this->retriveRoleEnum($role_string);
    if (! $this->hasRole($role)) {
      $this->roles[] = $role;
    }
    return $this;
  }

  public function removeRole(string $role_string): self {
    $role = $this->retriveRoleEnum($role_string);
    $this->roles = array_values(array_filter($this->roles, fn ($r) => $r !== $role));
    return $this;
  }

  public function isModerator(): bool {
    return $this->hasRole(Role::ROLE_MODERATOR) || $this->hasRole(Role::ROLE_ADMIN) || $this->hasRole(Role::ROLE_SUPER_ADMIN);
  }

  public function isAdmin(): bool {
    return $this->hasRole(Role::ROLE_ADMIN) || $this->hasRole(Role::ROLE_SUPER_ADMIN);
  }

  public function isSuperAdmin(): bool {
    return $this->hasRole(Role::ROLE_SUPER_ADMIN);
  }

  public function hasRole(Role|RoleInterface $role): bool {
    foreach ($this->roles as $r) {
      if ($r->getValue() === $role->getValue()) {
        return true;
      }
    }
    return false;
  }

  public function retriveRoleEnum(string $role_string): Role {
    try {
      $role = Role::createFromValue($role_string);
      return $role;
    }
    catch (\Throwable $e) {}
    try {
      $role = Role::createFromLabel($role_string);
      return $role;
    }
    catch (\Throwable $e) {}
    throw new \InvalidArgumentException(sprintf('Valeur/libellé "%s" invalide. Valeurs acceptées : %s', $role_string, implode(', ', Role::values())));
  }
}

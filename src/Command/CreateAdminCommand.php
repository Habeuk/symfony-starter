<?php
namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Entity\User;
use App\Enum\Role;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:create-admin', description: 'Create admin user')]
class CreateAdminCommand extends Command {
  
  public function __construct(private EntityManagerInterface $entityManager, private UserPasswordHasherInterface $passwordHasher) {
    parent::__construct();
  }
  
  // protected function configure(): void {
  // $this->addArgument('email',
  // InputArgument::REQUIRED)->addArgument('password', InputArgument::REQUIRED);
  // }
  protected function execute(InputInterface $input, OutputInterface $output): int {
    $io = new SymfonyStyle($input, $output);
    $email = $io->ask('Admin email');
    $password = $io->askHidden('Password');
    
    $admin = new User();
    $admin->email = $email;
    $admin->setRoles([
      role::ROLE_USER->value,
      Role::ROLE_ADMIN->value,
      Role::ROLE_SUPER_ADMIN->value
    ]);
    $admin->setHashedPassword($this->passwordHasher->hashPassword($admin, $password));
    // Remplir les champs obligatoires
    $admin->firstName = 'StephaneK';
    $admin->lastName = 'Armand';
    $admin->setIsVerified(true);
    $admin->activate();
    $this->entityManager->persist($admin);
    $this->entityManager->flush();
    
    $io->success('Admin created!');
    
    return Command::SUCCESS;
  }
}

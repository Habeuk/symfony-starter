<?php
namespace App\Controller\Api\Form;

use App\Entity\User;
use Habeuk\HbkSymfony\Service\ApiDatasMapper;
use Habeuk\HbkSymfony\Service\EntityReferenceResolver;
use Habeuk\HbkSymfony\Service\ {
  FrontendFormConfig
};
use Habeuk\HbkSymfony\Controller\Api\BaseApiCrud;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Habeuk\HbkSymfony\Enum\PermissionEnum;
use Symfony\Component\HttpFoundation\Request;

#[IsGranted('ROLE_USER')]
#[Route('/api/form/{companyId}')]
final class FormDefinitionController extends BaseApiCrud {

  /**
   * Recuperer les entites qui peuvent etre gerer de maniere dynamique.
   */
  #[Route('/available', name: 'api_forms_available', methods: [
    'GET'
  ])]
  public function available(int $companyId, #[CurrentUser] User $user): JsonResponse {
    try {
      $forms = $this->frontendFormConfig->getAllEntitiesConfig($user);
    }
    catch (\Throwable $e) {
      return $this->error($e, $user);
    }
    return $this->json($forms);
  }

  /**
   * Recupere les champs pour l'affichage du tableau et aussi les configs de l'entité.
   */
  #[Route('/definition/{entityName}', name: 'api_form_definition', methods: [
    'GET'
  ])]
  public function definition(int $companyId, string $entityName, #[CurrentUser] User $user): JsonResponse {
    if (! $this->baseValidation($entityName)) {
      return $this->json([
        'error' => 'Entity/Form not found : ' . $entityName
      ], Response::HTTP_UNAUTHORIZED);
    }
    $reults = $this->baseEntityDefinition($companyId, $user);
    return $this->json($reults);
  }

  /**
   * Recupere le formulaire d'une entité en JSON.
   */
  #[Route('/fields/{entityName}/{action}', name: 'api_form_fields', methods: [
    'GET'
  ])]
  public function getFormDefinitions(int $companyId, string $entityName, string $action, Request $request, #[CurrentUser] User $user): JsonResponse {
    if (! $this->baseValidation($entityName)) {
      return $this->json([
        'error' => 'Entity/Form not found : ' . $entityName
      ], Response::HTTP_UNAUTHORIZED);
    }
    $datas = $this->baseFormEntityDefinition($action, $user, $companyId);
    return $this->json($datas);
  }
}

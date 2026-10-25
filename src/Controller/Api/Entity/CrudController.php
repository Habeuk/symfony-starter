<?php
namespace App\Controller\Api\Entity;

use App\Entity\User;
use Habeuk\HbkSymfony\Enum\PermissionEnum;
use Habeuk\HbkSymfony\Controller\Api\BaseApiCrud;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Habeuk\HbkSymfony\Repository\BaseRepository;
use Habeuk\HbkSymfony\Repository\Interfaces\BaseRepositoryInterface;
use Habeuk\HbkSymfony\Service\ {
  EntitySearchService,
  EntityReferenceResolver,
  FrontendFormConfig,
  ApiDatasMapper
};
use Habeuk\HbkSymfony\EntityPolicy\EntityAccessChecker;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

#[IsGranted('ROLE_USER')]
#[Route('/api/crud/{companyId}')]
final class CrudController extends BaseApiCrud {

  function __construct(EntityManagerInterface $entityManager, FrontendFormConfig $frontendFormConfig, EntityReferenceResolver $entityResolver,
    ApiDatasMapper $apiDatasMapper, EntityAccessChecker $entityAccessChecker, LoggerInterface $logger) {
    parent::__construct($entityManager, $frontendFormConfig, $entityResolver, $apiDatasMapper, $entityAccessChecker, $logger);
  }

  #[Route('/search/{entityName}', name: 'api_search_entities', methods: [
    'GET'
  ])]
  public function search(int $companyId, string $entityName, Request $request, #[CurrentUser] User $user, EntitySearchService $searchService): JsonResponse {
    if (! $this->baseValidation($entityName))
      return $this->json([
        'error' => 'Entity/Dto not found : ' . $entityName
      ], Response::HTTP_UNAUTHORIZED);
    if ($this->entityClass !== null && $this->entityDtoClass !== null) {
      $access = $this->entityAccessChecker->check($this->entityClass, $user, PermissionEnum::VIEW);
      if (! $access->granted) {
        return $this->json([
          'error' => $access->message
        ], $access->statusCode);
      }
      $query = $request->query->get('q', '');
      $limit = (int) $request->query->get('limit', 20);
      $searchFields = $request->query->get('search_fields', null);
      $displayFields = $request->query->get('display_fields', null);
      if ($query === '') {
        return $this->json([]);
      }
      $searchFieldsArray = $searchFields !== null ? explode(',', $searchFields) : [];
      $displayFieldsArray = $displayFields !== null ? explode(',', $displayFields) : [];
      $results = $searchService->search($this->entityClass, $user, $query, $searchFieldsArray, $displayFieldsArray, $limit);
      return $this->json($results);
    }
    throw new \Exception("Une erreur s'est produite");
  }

  #[Route('/entity/delete/{entityType}/{id}', name: 'api_entity_delete', methods: [
    'DELETE'
  ])]
  function deleteEntity(int $companyId, string $entityType, int|string $id, #[CurrentUser] User $user, ObjectMapperInterface $objectMapper): JsonResponse {
    if (! $this->baseValidation($entityType))
      return $this->json([
        'error' => 'Entity/Dto not found : ' . $entityType
      ], Response::HTTP_UNAUTHORIZED);
    if ($this->entityClass !== null && $this->entityDtoClass !== null) {
      $accessDefinition = $this->entityAccessChecker->canDeleteDefinition($this->entityClass, $user);
      if (! $accessDefinition->granted) {
        return $this->json([
          'error' => $accessDefinition->message
        ], $accessDefinition->statusCode);
      }
      $baseRepository = $this->entityManager->getRepository($this->entityClass);
      $entity = $baseRepository->find($id);
      if ($entity !== null) {
        $accessData = $this->entityAccessChecker->checkEntityData($entity, $user, PermissionEnum::DELETE);
        if (! $accessData->granted) {
          return $this->json([
            'error' => $accessData->message
          ], $accessData->statusCode);
        }
        try {
          $this->entityManager->remove($entity);
          $this->entityManager->flush();
          return $this->json([]);
        }
        catch (\Throwable $e) {
          return $this->error($e, $user);
        }
      }
    }
    throw new \Exception("Une erreur s'est produite");
  }

  #[Route('/entity/load-edit/{entityType}/{id}', name: 'api_entity_load', methods: [
    'GET'
  ])]
  function loadEntity(int $companyId, string $entityType, int $id, #[CurrentUser] User $user, ObjectMapperInterface $objectMapper): JsonResponse {
    if (! $this->baseValidation($entityType))
      return $this->json([
        'error' => 'Entity/Dto not found : ' . $entityType
      ], Response::HTTP_UNAUTHORIZED);
    // Verification des roles et permissions.
    if ($this->entityClass !== null && $this->entityDtoClass !== null) {
      $accessDefinition = $this->entityAccessChecker->canViewDefinition($this->entityClass, $user);
      if (! $accessDefinition->granted) {
        return $this->json([
          'error' => $accessDefinition->message
        ], $accessDefinition->statusCode);
      }
      // On charge le contenu et on applique des validations supplementaires.
      $baseRepository = $this->entityManager->getRepository($this->entityClass);
      if ($baseRepository instanceof BaseRepositoryInterface) {
        $entity = $baseRepository->findWithSecurity($id, $user);
        if ($entity !== null) {
          $accessData = $this->entityAccessChecker->checkEntityData($entity, $user, PermissionEnum::VIEW);
          if (! $accessData->granted) {
            return $this->json([
              'error' => $accessData->message
            ], $accessData->statusCode);
          }
          $entityArray = $this->loadContentArray($entity, $this->entityDtoClass);
          return $this->json($entityArray);
        }
        else {
          $e = new \Exception("Ce contenu n'est plus disponible ou vous ne disposez pas des droits nécessaires pour y accéder.");
          return $this->error($e, $user);
        }
      }
      else {
        throw new \LogicException(sprintf('Le repository de "%s" doit implémenter %s.', $this->entityClass, BaseRepositoryInterface::class));
      }
    }
    throw new \Exception("Une erreur s'est produite");
  }

  #[Route('/entity/save/{entityType}', name: 'api_entity_save', methods: [
    'POST',
    'OPTIONS'
  ])]
  function saveEntity(int $companyId, string $entityType, Request $request, #[CurrentUser] User $user, ObjectMapperInterface $objectMapper): JsonResponse {
    return $this->save($companyId, $entityType, $request, $user, $objectMapper);
  }

  /**
   * Sauvegarde en masse d'entités du même type.
   *
   * Payload accepté :
   * - {"items": [ {...}, {...} ]}
   * - [ {...}, {...} ]
   *
   * La sauvegarde est atomique : en cas d'échec d'un élément,
   * aucune entité n'est persistée.
   *
   * @return JsonResponse
   */
  #[Route('/entity/save-mass/{entityType}', name: 'api_entity_save_mass', methods: [
    'POST',
    'OPTIONS'
  ])]
  function saveEntitiesMass(int $companyId, string $entityType, Request $request, #[CurrentUser] User $user, ObjectMapperInterface $objectMapper): JsonResponse {
    return $this->saveEntities($companyId, $entityType, $request, $user, $objectMapper);
  }
}

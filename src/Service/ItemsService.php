<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Service;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tpg\HeadlessBundle\Exception\NotFoundException;
use Tpg\HeadlessBundle\Exception\ValidationException;
use Tpg\HeadlessBundle\Middleware\PageableContextBuilder;
use Tpg\HeadlessBundle\Query\Fields;
use Tpg\HeadlessBundle\Query\Page;
use Tpg\HeadlessBundle\Query\Pageable;
use Tpg\HeadlessBundle\Request\ModifyItemRequest;
use Tpg\HeadlessBundle\Security\Subject\AccessOperation;

final class ItemsService
{
    private SecuredAstFactory $astFactory;
    private ReadExecutor $astExecutorORM;
    private DataHydrator $dataExtractor;
    private ValidatorInterface $validator;
    private EntityManagerInterface $entityManager;
    private SecurityService $securityService;
    private SchemaService $schemaService;
    private const CREATE_VALIDATION_GROUP = 'headless:create';
    private const UPDATE_VALIDATION_GROUP = 'headless:update';

    public function __construct(
        SecuredAstFactory $astFactory, ReadExecutor $astExecutorORM, DataHydrator $dataExtractor,
        ValidatorInterface $validator, EntityManagerInterface $entityManager, SecurityService $securityService,
        SchemaService $schemaService
    )
    {
        $this->astFactory = $astFactory;
        $this->astExecutorORM = $astExecutorORM;
        $this->dataExtractor = $dataExtractor;
        $this->validator = $validator;
        $this->entityManager = $entityManager;
        $this->securityService = $securityService;
        $this->schemaService = $schemaService;
    }

    public function getMany(string $collection, Fields $fields, array $context=[]):array
    {
        return $this->astExecutorORM->many(
            $this->astFactory->createCollectionAstFromFields($collection,$fields,AccessOperation::READ),
            $context
        );
    }

    public function getOne(string $collection, string $id, Fields $fields, array $context=[]):array
    {
        return $this->astExecutorORM->one(
            $this->astFactory->createCollectionAstFromFields($collection,$fields,AccessOperation::READ),
            $id,
            $context
        );
    }

    public function getPage(string $collection, Fields $fields, Pageable $pageable,array $context=[]):Page
    {
        $context = PageableContextBuilder::create($context)->withPageable($pageable)->toArray();

        $result = $this->getMany($collection,$fields,$context);

        $count = $this->astExecutorORM->count(
            $this->astFactory->createCollectionAstFromFields($collection,$fields,AccessOperation::READ),
            $context
        );

        return new Page(
            $result,
            $pageable,
            $count
        );
    }


    public function create(string $collection, ModifyItemRequest $request):object
    {
        if(!$this->securityService->isCollectionGranted($collection,AccessOperation::CREATE)){
            throw new \RuntimeException('Access denied');
        }
        $item = $this->dataExtractor->createObject(
            $collection,
            $request->getData()
        );

        $violations = $this->validator->validate($item,null,[Constraint::DEFAULT_GROUP,self::CREATE_VALIDATION_GROUP]);

        ValidationException::assertValid($violations);;

        if(!$this->securityService->isItemGranted($collection, $item, AccessOperation::CREATE)){
            throw new \RuntimeException('Access denied');
        }

        $this->entityManager->persist($item);
        $this->entityManager->flush();
        return $item;
    }

    /**
     * @throws ValidationException
     */
    public function update(string $collection, string $id, array $data):object
    {
        if(!$this->securityService->isCollectionGranted($collection,AccessOperation::UPDATE)){
            throw new \RuntimeException('Access denied');
        }

        $item = $this->entityManager->find($this->schemaService->getCollection($collection)->class,$id);

        if($item===null){
            throw new NotFoundException();
        }

        $item = $this->dataExtractor->hydrateObject(
            $collection,
            $item,
            $this->securityService->filterEntityData($collection, $data, AccessOperation::UPDATE)
        );

        $violations = $this->validator->validate($item,null,[Constraint::DEFAULT_GROUP,self::UPDATE_VALIDATION_GROUP]);

        ValidationException::assertValid($violations);

        if(!$this->securityService->isItemGranted($collection, $item, AccessOperation::UPDATE)){
            throw new \RuntimeException('Access denied');
        }

        $this->entityManager->flush();
        return $item;
    }

    public function delete(string $collection, string $id):void
    {
        if(!$this->securityService->isCollectionGranted($collection,AccessOperation::DELETE)){
            throw new \RuntimeException('Access denied');
        }
        $item = $this->entityManager->find($this->schemaService->getCollection($collection)->class,$id);

        if($item===null){
            return;
        }

        if(!$this->securityService->isItemGranted($collection, $item, AccessOperation::DELETE)){
            throw new \RuntimeException('Access denied');
        }

        $this->entityManager->remove($item);
        $this->entityManager->flush();
    }

}
<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Tpg\HeadlessBundle\DependencyInjection\TpgHeadlessExtension;
use Tpg\HeadlessBundle\Extension\FiltersExtension;
use Tpg\HeadlessBundle\Extension\PageableExtension;
use Tpg\HeadlessBundle\Middleware\AttachReferencesToResultMiddleware;
use Tpg\HeadlessBundle\Middleware\FiltersMiddleware;
use Tpg\HeadlessBundle\Middleware\HydratorMiddleware;
use Tpg\HeadlessBundle\Middleware\PageableMiddleware;
use Tpg\HeadlessBundle\Middleware\QueryMiddlewareStack;
use Tpg\HeadlessBundle\Middleware\StripToManyRelationsOnListMiddleware;
use Tpg\HeadlessBundle\Request\FieldsResolver;
use Tpg\HeadlessBundle\Request\FiltersResolver;
use Tpg\HeadlessBundle\Request\ModifyItemRequestResolver;
use Tpg\HeadlessBundle\Request\PageableResolver;
use Tpg\HeadlessBundle\Schema\Schema;
use Tpg\HeadlessBundle\Security\Checker;
use Tpg\HeadlessBundle\Serializer\ItemDenormalizer;
use Tpg\HeadlessBundle\Serializer\PageNormalizer;
use Tpg\HeadlessBundle\Serializer\ValidationExceptionNormalizer;
use Tpg\HeadlessBundle\Service\AstFactory;
use Tpg\HeadlessBundle\Service\DataHydrator;
use Tpg\HeadlessBundle\Service\ReadExecutor;
use Tpg\HeadlessBundle\Service\HydratorORM;
use Tpg\HeadlessBundle\Service\ItemsService;
use Tpg\HeadlessBundle\Service\QueryService;
use Tpg\HeadlessBundle\Service\ResourceHydratorFactory;
use Tpg\HeadlessBundle\Service\SchemaService;
use Tpg\HeadlessBundle\Service\SecuredAstFactory;
use Tpg\HeadlessBundle\Service\SecurityChecker;
use Tpg\HeadlessBundle\Service\SecurityService;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container) {
    $services  = $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(ItemsService::class);
    $services->set(AstFactory::class);
    $services->set(Checker::class,SecurityChecker::class);

    $services->set(QueryMiddlewareStack::class)->autoconfigure(false);

    $services->set(FiltersMiddleware::class);
    $services->set(PageableMiddleware::class);
    $services->set(AttachReferencesToResultMiddleware::class)
        ->tag(TpgHeadlessExtension::QUERY_MIDDLEWARE_TAG,['priority'=>-2000]);
    $services->set(HydratorMiddleware::class)
        ->tag(TpgHeadlessExtension::QUERY_MIDDLEWARE_TAG,['priority'=>-1000]);

    $services->set(QueryService::class);
    $services->set(HydratorORM::class);
    $services->set(ReadExecutor::class);
    $services->set(ResourceHydratorFactory::class);

    $services->set(SecurityService::class);
    $services->set(PageNormalizer::class);
    $services->set(ItemDenormalizer::class)->autoconfigure(false);
    $services->set(ValidationExceptionNormalizer::class);

    $services->set('headless.item.denormalizer.object',ObjectNormalizer::class)
        ->autoconfigure(false)
        ->args([
            service('serializer.mapping.class_metadata_factory'),
            service('serializer.name_converter.metadata_aware'),
            service('serializer.property_accessor'),
            service('property_info')->ignoreOnInvalid(),
            service('serializer.mapping.class_discriminator_resolver')->ignoreOnInvalid(),
            null,
            [],
        ]);

    $services->set('headless.item.denormalizer.array', ArrayDenormalizer::class)
        ->autoconfigure(false);

    $services->set('headless.item.denormalizer',Serializer::class)
        ->autoconfigure(false)
        ->arg('$normalizers',[
            service(ItemDenormalizer::class),
            service('serializer.normalizer.datetime'),
            service('headless.item.denormalizer.object'),
            service('headless.item.denormalizer.array')
        ]);
    $services->set(DataHydrator::class)
        ->arg('$denormalizer',service('headless.item.denormalizer'));
    $services->set(SchemaService::class);
    $services->alias(Schema::class,SchemaService::class);
    $services->set(SecuredAstFactory::class);

    $services->set(PageableResolver::class);
    $services->set(FiltersResolver::class);
    $services->set(ModifyItemRequestResolver::class);
    $services->set(FieldsResolver::class);
    $services->load('Tpg\\HeadlessBundle\\Controller\\', '../../Controller/*')
        ->tag('controller.service_arguments');
};
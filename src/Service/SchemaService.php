<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Service;


use Tpg\HeadlessBundle\Exception\CompositeKeysAreNotSupported;
use Tpg\HeadlessBundle\Schema\Collection;
use Tpg\HeadlessBundle\Schema\Field;
use Tpg\HeadlessBundle\Schema\Relation;
use Tpg\HeadlessBundle\Schema\Schema;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

final class SchemaService implements Schema
{
    private EntityManagerInterface $entityManager;
    /**
     * @var array<string, class-string>
     */
    private array $collections;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param  string  $name
     * @param  class-string  $class
     */
    public function addCollection(string $name, string $class):void
    {
        $this->collections[$name] = $class;
    }


    /**
     * @param  class-string $class
     * @return bool
     */
    private function hasClass(string $class):bool
    {
        return in_array($class,$this->collections,true);
    }

    /**
     * @param  string  $collection
     * @return class-string
     */
    private function getClass(string $collection):string
    {
        return $this->collections[$collection];
    }

    private function getCollectionByClass(string $class):string
    {
        $collectionName = array_search($class,$this->collections,true);

        if($collectionName===false){
            throw new \LogicException('Unknown class');
        }

        return $collectionName;
    }

    private function getCollectionMetadata(string $collection):ClassMetadataInfo
    {
        return $this->entityManager->getClassMetadata($this->collections[$collection]);
    }

    private function getEmbeddedMetadata(string $class):ClassMetadataInfo
    {
        $classMd = $this->entityManager->getClassMetadata($class);
        assert($classMd->isEmbeddedClass);
        return $classMd;
    }

    public function hasCollection(string $collection):bool
    {
        return array_key_exists($collection,$this->collections);
    }

    public function getFields(string $collection):array
    {
        return [...$this->getNonRelationFields($collection),...$this->getRelationFields($collection)];
    }

    public function getRelationFields(string $collection):array
    {
        return $this->getCollectionMetadata($collection)->getAssociationNames();
    }

    public function getNonRelationFields(string $collection):array
    {
        return $this->getCollectionMetadata($collection)->getFieldNames();
    }

    public function hasRelation(string $collection,string $field):bool
    {
        return in_array($field,$this->getRelationFields($collection),true);
    }

    public function getRelation(string $collection,string $relation):Relation
    {
        $associationMapping = $this->getCollectionMetadata($collection)->getAssociationMapping($relation);

        if(!$this->hasClass($associationMapping['targetEntity'])){
            throw new \LogicException('Collection for class '.$associationMapping['targetEntity'].' is not registered');
        }

        $referencedCollection = $this->getCollectionByClass($associationMapping['targetEntity']);
        $rel = new Relation($associationMapping['fieldName'], $associationMapping['type'],$referencedCollection);

        if($rel->isToOne()) {
            if (count($associationMapping['joinColumns']) > 1) {
                throw new \RuntimeException('Composite keys are not supported');
            }

            $rel->joinColumn = $associationMapping['joinColumns'][0]['name'];
            $rel->referencedColumn = $associationMapping['joinColumns'][0]['referencedColumnName'];
        }else{
            //Get mapping from owning collection and swap
            $referencedCollectionMetadata= $this->getCollectionMetadata($referencedCollection);
            $owningSideMetadata = $referencedCollectionMetadata->getAssociationMapping($associationMapping['mappedBy']);
            if (count($owningSideMetadata['joinColumns']) > 1) {
                throw new CompositeKeysAreNotSupported();
            }
            $rel->joinColumn = $owningSideMetadata['joinColumns'][0]['referencedColumnName'];
            $rel->referencedColumn = $owningSideMetadata['joinColumns'][0]['name'];
        }
        return $rel;
    }

    public function getCollection(string $collection):Collection
    {
        $associationMapping = $this->getCollectionMetadata($collection);

        return new Collection($associationMapping->table['name'], $this->getClass($collection));
    }

    public function getField(string $collection, string $fieldName):Field
    {
        $fieldMapping = $this->getCollectionMetadata($collection)->getFieldMapping($fieldName);
        return $this->createFieldFromArray($fieldMapping);
    }

    public function getIdentifier(string $collection):Field
    {
        $metadataIdentifiers = $this->getCollectionMetadata($collection)->getIdentifier();
        if(count($metadataIdentifiers)===0){
            throw new \RuntimeException(sprintf('Collection "%s" must have identifier',$collection));
        }
        if(count($metadataIdentifiers)>1){
            throw new CompositeKeysAreNotSupported();
        }
        return $this->getField($collection,$metadataIdentifiers[0]);
    }

    private function createFieldFromArray(array $fieldMapping):Field
    {
        return new Field($fieldMapping['fieldName'],$fieldMapping['columnName'],$fieldMapping['type'], $fieldMapping['nullable']);
    }

    /**
     * @return string[]
     */
    public function getCollections():array
    {
        return array_keys($this->collections);
    }
}
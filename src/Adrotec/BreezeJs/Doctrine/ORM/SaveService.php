<?php

namespace Adrotec\BreezeJs\Doctrine\ORM;

use Doctrine\ORM\EntityManager;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\ParameterBag;

use Adrotec\BreezeJs\Metadata\Metadata;

use Adrotec\BreezeJs\Save\SaveBundle;

use Adrotec\BreezeJs\MetadataInterceptorInterface;

class SaveService {
    
    private $entityManager;
    private $metadata;
    private $interceptor;

    public function __construct(EntityManager $entityManager, Metadata $metadata = null, MetadataInterceptorInterface $interceptor) {
        $this->entityManager = $entityManager;
        $this->metadata = $metadata;
        $this->interceptor = $interceptor;
    }
    
    public function createSaveBundleFromString($saveBundleString){
        $saveBundleArr = json_decode($saveBundleString);
	usort($saveBundleArr->entities, function ($a, $b) {
            if ($a->entityAspect->entityState == 'Added') {
                $a = strtotime($a->createdAt);
            } else {
                $a = strtotime($a->updatedAt);
            }

            if ($b->entityAspect->entityState == 'Added') {
                $b = strtotime($b->createdAt);
            } else {
                $b = strtotime($b->updatedAt);
            }
            
            if ($a == $b) {
                return 0;
            }
            return ($a < $b) ? -1 : 1;
        });
        $saveBundle = new SaveBundle();
        $saveBundle->setEntities($saveBundleArr->entities);
        return $saveBundle;
    }
    
    public function saveChanges($saveBundle){
        if(!$saveBundle instanceof SaveBundle){
            $saveBundle = $this->createSaveBundleFromString($saveBundle);
        }
        $context = new SaveContextProvider($this->entityManager, $this->metadata, $this->interceptor);
        $saveResult = $context->saveChanges($saveBundle);
        return $saveResult;
    }
}

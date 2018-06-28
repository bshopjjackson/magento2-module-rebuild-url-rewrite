<?php
declare(strict_types=1);
/**
 * Copyright © 2018 Stämpfli AG. All rights reserved.
 * @author marcel.hauri@staempfli.com
 */

namespace Staempfli\RebuildUrlRewrite\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\UrlRewrite\Model\UrlPersistInterface;

class UrlRewrite implements UrlRewriteInterface
{
    /**
     * @var UrlPersistInterface
     */
    private $urlPersist;
    /**
     * @var int|null
     */
    private $storeId;
    /**
     * @var string|null
     */
    private $entity;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection|null
     */
    private $collection;
    /**
     * @var null
     */
    private $rewriteGenerator;

    public function __construct(
        UrlPersistInterface $urlPersist
    ) {
        $this->urlPersist = $urlPersist;
    }

    public function setStoreId(int $storeId)
    {
        $this->storeId = $storeId;
        return $this;
    }

    /**
     * @param string $entity
     * @return $this
     */
    public function setEntity(string $entity)
    {
        $this->entity = $entity;
        return $this;
    }

    /**
     * @param $rewriteGenerator
     * @return $this
     */
    public function setRewriteGenerator($rewriteGenerator)
    {
        $this->rewriteGenerator = $rewriteGenerator;
        return $this;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection $collection
     * @return $this
     */
    public function setCollection(\Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection $collection)
    {
        $this->collection = $collection;
        return $this;
    }

    /**
     * @return void
     */
    public function rebuild()
    {
        foreach ($this->collection as $item) {
            try {
                $this->deleteByEntity((int) $item->getId());
                $this->urlPersist->replace(
                    $this->getRewriteGenerator()->generate($item)
                );
            } catch (\Exception $e) {
                //
            }
        }
    }

    private function getStoreId()
    {
        if (!$this->storeId) {
            throw new \LogicException('Store ID not set!');
        }
        return $this->storeId;
    }

    private function getEntity()
    {
        if (!$this->entity) {
            throw new \LogicException('Entity type not set!');
        }
        return $this->entity;
    }

    private function getCollection()
    {
        if (!$this->collection) {
            throw new \LogicException('Collection not set!');
        }
        return $this->collection;
    }

    private function getRewriteGenerator()
    {
        if (!$this->rewriteGenerator) {
            throw new \LogicException('URL Rewrite Generator not set!');
        }
        return $this->rewriteGenerator;
    }

    private function deleteByEntity(int $entityId)
    {
        $this->urlPersist->deleteByData([
            \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::ENTITY_ID => $entityId,
            \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::ENTITY_TYPE => $this->getEntity(),
            \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::STORE_ID => $this->getStoreId(),
            \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::REDIRECT_TYPE => 0,
            \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::IS_AUTOGENERATED => 1,
        ]);
    }
}
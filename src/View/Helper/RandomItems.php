<?php
namespace RandomItemsBlock\View\Helper;

use Doctrine\ORM\EntityManager;
use Omeka\Api\Adapter\Manager as ApiAdapterManager;
use Omeka\Entity\Item;
use Laminas\View\Helper\AbstractHelper;

class RandomItems extends AbstractHelper
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var ApiAdapterManager
     */
    protected $apiAdapterManager;

    public function __construct(EntityManager $entityManager, ApiAdapterManager $apiAdapterManager)
    {
        $this->entityManager = $entityManager;
        $this->apiAdapterManager =  $apiAdapterManager;
    }

    /**
     * @return \Omeka\Api\Representation\ItemRepresentation[]
     */
    public function __invoke(int $count, int $res_template_id = null): array
    {
        $em = $this->entityManager;

        $itemAdapter = $this->apiAdapterManager->get('items');

        $items = $em->getRepository(Item::class)->findBy(['id' => $this->getRandomItemIds($count, $res_template_id)]);
        $itemRepresentations = [];
        foreach ($items as $item) {
            $itemRepresentations[] = $itemAdapter->getRepresentation($item);
        }

        return $itemRepresentations;
    }

    protected function getRandomItemIds($count, $res_template_id, $useCache = true)
    {
        $cacheKey = "omeka:RandomItems:randomItemIds:$count";
        $itemIds = $useCache ? $this->getFromCache($cacheKey) : false;
        if (false === $itemIds) {
            $em = $this->entityManager;
            $conn = $em->getConnection();

            $sql = 'SELECT id FROM resource';
            $sql .= ' WHERE resource_type = :resourceType';

            if (isset($res_template_id)) {
              $sql .= ' AND resource_template_id = :resourceTemplateId';
            }

            // Limit to public items so we don't have to check for user permissions
            // (anyone can see public items even anonymous users)
            $sql .= ' AND is_public = 1';

            $sql .= ' ORDER BY RAND()';
            $sql .= " LIMIT $count";

            $stmt = $conn->prepare($sql);

            if (isset($res_template_id)) {
              $stmt->execute(['resourceType' => Item::class, 'resourceTemplateId' => $res_template_id]);
            }
            else {
              $stmt->execute(['resourceType' => Item::class]);
            }
            $result = $stmt->fetchAll();
            $itemIds = array_column($result, 'id');

            $this->storeInCache($cacheKey, $itemIds);
        }

        return $itemIds;
    }

    protected function getFromCache(string $key)
    {
        return extension_loaded('apcu') && apcu_enabled() ? apcu_fetch($key) : false;
    }

    protected function storeInCache(string $key, $value, int $ttl = 3600)
    {
        if (extension_loaded('apcu') && apcu_enabled()) {
            apcu_store($key, $value, $ttl);
        }
    }
}

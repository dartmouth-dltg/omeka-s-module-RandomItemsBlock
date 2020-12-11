<?php
namespace RandomItemsBlock\View\Helper;

use Doctrine\ORM\EntityManager;
use Omeka\Api\Adapter\Manager as ApiAdapterManager;
use Omeka\Entity\Item;
use Zend\View\Helper\AbstractHelper;

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
    public function __invoke(int $count): array
    {
        $em = $this->entityManager;

        $conn = $em->getConnection();

        $sql = 'SELECT id FROM resource';
        $sql .= ' WHERE resource_type = :resourceType';

        // Limit to public items so we don't have to check for user permissions
        // (anyone can see public items even anonymous users)
        $sql .= ' AND is_public = 1';

        $sql .= ' ORDER BY RAND()';
        $sql .= " LIMIT $count";

        $stmt = $conn->prepare($sql);
        $stmt->execute(['resourceType' => Item::class]);
        $result = $stmt->fetchAll();
        $itemIds = array_column($result, 'id');

        $itemAdapter = $this->apiAdapterManager->get('items');

        $items = $em->getRepository(Item::class)->findBy(['id' => $itemIds]);
        $itemRepresentations = [];
        foreach ($items as $item) {
            $itemRepresentations[] = $itemAdapter->getRepresentation($item);
        }

        return $itemRepresentations;
    }
}

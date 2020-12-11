<?php
namespace RandomItemsBlock\View\Helper;

use Doctrine\ORM\EntityManager;
use Omeka\Api\Adapter\Manager as ApiAdapterManager;
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

    public function __invoke(int $count)
    {
        $em = $this->entityManager;

        $conn = $em->getConnection();

        # Limit to public items so we don't have to check for user permissions
        $stmt = $conn->prepare("SELECT id FROM resource WHERE resource_type = :resourceType AND is_public = 1 ORDER BY RAND() LIMIT $count");
        $stmt->execute(['resourceType' => 'Omeka\\Entity\\Item']);
        $result = $stmt->fetchAll();
        $itemIds = array_column($result, 'id');

        $itemAdapter = $this->apiAdapterManager->get('items');

        $items = [];
        $query = $em->createQuery("SELECT i FROM Omeka\Entity\Item i WHERE i.id IN (:itemIds)");
        $query->setParameter('itemIds', $itemIds);
        foreach ($query->getResult() as $item) {
            $items[] = $itemAdapter->getRepresentation($item);
        }

        return $items;
    }
}

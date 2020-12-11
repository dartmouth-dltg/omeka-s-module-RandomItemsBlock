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
        $stmt = $conn->prepare('SELECT COUNT(id) totalCount FROM item');
        $stmt->execute();
        $result = $stmt->fetch();
        $totalCount = $result['totalCount'];

        $randomOffsets = [];
        while (count($randomOffsets) < $count) {
            $randomOffset = mt_rand(0, $totalCount - 1);
            if (!in_array($randomOffset, $randomOffsets, true)) {
                $randomOffsets[] = $randomOffset;
            }
        }

        $itemAdapter = $this->apiAdapterManager->get('items');

        $items = [];
        foreach ($randomOffsets as $randomOffset) {
            $query = $em->createQuery("SELECT i FROM Omeka\Entity\Item i ORDER BY i.id ASC");
            $query->setFirstResult($randomOffset);
            $query->setMaxResults(1);
            $item = $query->getSingleResult();
            if ($item) {
                $items[] = $itemAdapter->getRepresentation($item);
            }
        }

        return $items;
    }
}

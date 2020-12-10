<?php
namespace RandomItemsBlock\View\Helper;

use Doctrine\ORM\EntityManager;
use Zend\View\Helper\AbstractHelper;

class RandomItems extends AbstractHelper
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(int $count)
    {
        $em = $this->entityManager;
        $api = $this->getView()->api();

        $query = $em->createQuery("SELECT r.id FROM Omeka\Entity\Item r ORDER BY RAND()");
        $query->setMaxResults($count);

        $items = [];
        foreach ($query->getResult() as $result) {
            $item = $api->read('items', $result['id'])->getContent();
            if ($item) {
                $items[] = $item;
            }
        }

        return $items;
    }
}

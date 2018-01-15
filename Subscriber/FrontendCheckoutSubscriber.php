<?php declare(strict_types=1);

namespace MettwochOrder\Subscriber;

use Doctrine\DBAL\Connection;
use Enlight\Event\SubscriberInterface;
use Shopware\Components\Model\ModelManager;

class FrontendCheckoutSubscriber implements SubscriberInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @param Connection $connection
     * @param ModelManager $modelManager
     */
    public function __construct(
        Connection $connection,
        ModelManager $modelManager
    ) {
        $this->connection = $connection;
        $this->modelManager = $modelManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => 'addMettwochShippingDate',
        ];
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function addMettwochShippingDate(\Enlight_Event_EventArgs $args)
    {
        /** @var \Shopware_Controllers_Frontend_Checkout $subject */
        $subject = $args->getSubject();

        $request = $subject->Request();

        if ($request->getActionName() !== 'finish' || !($shippingDate = $request->getParam('shippingDate'))) {
            return;
        }

        if (preg_match("[a-zA-Z]", $shippingDate)) {
            return;
        }

        $orderNumber = $subject->View()->getAssign('sOrderNumber');

        $order = $this->modelManager->getRepository('Shopware\Models\Order\Order')->findOneBy([
            'number' => $orderNumber
        ]);

        $this->connection->executeQuery('
            UPDATE 
                s_order
            SET
              ordertime = :shippingDate
            WHERE
              ordernumber = :orderNumber
            ',
            [
                'shippingDate' => $shippingDate,
                'orderNumber' => $order->getNumber()
            ]
        );

        // Abo-Commerce changes
        $aboOrders = $this->connection->createQueryBuilder()
            ->select('id')
            ->from('s_plugin_swag_abo_commerce_orders')
            ->where('order_id = :orderId')
            ->setParameter('orderId', $order->getId())
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);

        if (!$aboOrders) {
            return;
        }

        $aboManager = $this->modelManager->getRepository('Shopware\CustomModels\SwagAboCommerce\Order');

        $actualDate = \DateTime::createFromFormat('Y-m-d', $shippingDate);

        foreach ($aboOrders as $aboOrder) {
            $abo = $aboManager->find($aboOrder);

            $abo->setCreated($actualDate);

            $dueDate = clone $actualDate;
            $dueDate->modify('+' . $abo->getDeliveryInterval() . ' ' . $this->getDateClass($abo->getDeliveryIntervalUnit()));

            $abo->setDueDate($dueDate);

            $lastDate = clone $actualDate;
            $lastDate->modify('+' . $abo->getDuration() . ' ' . $this->getDateClass($abo->getDurationUnit()));
            $abo->setLastRun($lastDate);

            $this->modelManager->persist($abo);
        }

        $this->modelManager->flush();
    }

    /**
     * @param string $type
     * @return string
     */
    private function getDateClass(string $type): string
    {
        switch ($type) {
            case 'weeks':
            case 'week':
                return 'week';
            case 'months':
            case 'month':
                return 'month';
            default:
                return 'day';
        }
    }
}

<?php declare(strict_types=1);

namespace MettwochOrder\Subscriber;

use Doctrine\DBAL\Connection;
use Enlight\Event\SubscriberInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Order\Order;

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
            'Shopware_Controllers_Frontend_Checkout::finishAction::replace' => 'replaceFinishAction',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => 'onPostDispatchCheckout',
        ];
    }

    public function onPostDispatchCheckout(\Enlight_Event_EventArgs $args)
    {
        /** @var \Shopware_Controllers_Frontend_Checkout $subject */
        $subject = $args->getSubject();

        $request = $subject->Request();

        if ($request->getParam('mwOrderStop')) {
            $subject->View()->assign('mwOrderStop', true);
        }
    }

    /**
     * @param \Enlight_Hook_HookArgs $args
     */
    public function replaceFinishAction(\Enlight_Hook_HookArgs $args)
    {
        /** @var \Shopware_Controllers_Frontend_Checkout $subject */
        $subject = $args->getSubject();

        $request = $subject->Request();

        $shippingDate = $request->getParam('shippingDate');

        if($shippingDate && $this->checkOrderStop($shippingDate)) {
            $subject->redirect('checkout/confirm?mwOrderStop=1');
            return;
        }

        if ($shippingDate) {
            $subject->executeParent(
                $args->getMethod(),
                $args->getArgs()
            );

            $this->afterCreateOrder($shippingDate, (int) $subject->View()->getAssign('sOrderNumber'));
            return;
        }


        $subject->executeParent(
            $args->getMethod(),
            $args->getArgs()
        );
    }

    /**
     * @param string $shippingDate
     * @return bool
     */
    private function checkOrderStop(string $shippingDate): bool
    {
        $orderStopDate = $this->connection->createQueryBuilder()
            ->select('order_stop_date')
            ->from('mw_order')
            ->where('order_stop_date = :orderStopDate')
            ->setParameter('orderStopDate', $shippingDate)
            ->execute()
            ->fetchColumn();

        return $orderStopDate === false ? false : true;
    }

    /**
     * @param string $shippingDate
     * @param int $orderNumber
     */
    private function afterCreateOrder(string $shippingDate, int $orderNumber)
    {
        if (preg_match("[a-zA-Z]", $shippingDate)) {
            return;
        }

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

        $this->aboOrder($shippingDate, $order);
    }

    /**
     * @param string $shippingDate
     * @param Order $order
     */
    private function aboOrder(string $shippingDate, Order $order)
    {
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

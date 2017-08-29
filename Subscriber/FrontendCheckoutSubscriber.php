<?php declare(strict_types=1);

namespace MettwochOrder\Subscriber;

use Doctrine\DBAL\Connection;
use Enlight\Event\SubscriberInterface;

class FrontendCheckoutSubscriber implements SubscriberInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * FrontendCheckoutSubscriber constructor.
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
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

        $this->connection->executeQuery('
            UPDATE 
                s_order_attributes as attributes
            INNER JOIN
              s_order
            ON
              attributes.orderID = s_order.id
            SET
              attributes.mettwoch_order_date = :shippingDate
            WHERE
              s_order.ordernumber = :orderNumber
            ',
            [
                'shippingDate' => $shippingDate,
                'orderNumber' => $subject->View()->getAssign('sOrderNumber')
            ]
        );
    }
}

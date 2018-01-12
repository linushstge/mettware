<?php declare(strict_types=1);

class Shopware_Controllers_Frontend_MettwochOrder extends Enlight_Controller_Action
{
    public function indexAction()
    {
        if (!($shippingDate = $this->request->getParam('shippingDate'))) {
            $shippingDate = (new DateTime())->format('Y-m-d');
        }

        $query = $this->get('dbal_connection')->createQueryBuilder();

        $orderIds = $query->select('id')
            ->from('s_order')
            ->where('ordertime LIKE :orderTime')
            ->setParameter('orderTime', '%'.$shippingDate.'%')
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);

        $orderRepo = $this->get('models')->getRepository('Shopware\Models\Order\Order');

        $orders = [];
        $sumAmount = 0;
        $quantityTotal = 0;
        foreach ($orderIds as $orderId) {
            $order = $orderRepo->find($orderId);
            $orders[] = $order;

            $sumAmount += $order->getInvoiceAmount();

            /** @var \Shopware\Models\Order\Detail $detail */
            foreach ($order->getDetails() as $detail) {
                $quantityTotal += $detail->getQuantity();
            }
        }

        $this->View()->assign('mettwoch', [
            'orders' => $orders,
            'sumAmount' => $sumAmount,
            'quantityTotal' => $quantityTotal,
            'shippingDate' => $shippingDate,
        ]);
    }
}

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
            ->andWhere('ordernumber != 0')
            ->andWhere('status != -1')
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
                if ($detail->getMode() === 0) {
                    $quantityTotal += $detail->getQuantity();
                }
            }
        }

        $this->View()->assign('mettwoch', [
            'orders' => $orders,
            'sumAmount' => $sumAmount,
            'quantityTotal' => $quantityTotal,
            'shippingDate' => $shippingDate,
            'mwOrderStop' => $this->request->getParam('mwOrderStop'),
            'mwOrderReset' => $this->request->getParam('mwOrderReset'),
            'mwOrderResetFailure' => $this->request->getParam('mwOrderResetFailure'),
        ]);
    }

    public function stopOrdersAction()
    {
        $connection = $this->get('dbal_connection');

        try {
            $connection->insert(
                'mw_order',
                ['order_stop_date' => (new \DateTime())->format('Y-m-d')]
            );
        } catch (\Exception $e) {
            //nth
        }

        $this->redirect('mettwochorder/index?mwOrderStop=1');
    }

    public function freeOrdersAction()
    {
        $mettKey = $this->Request()->getParam('mettKey');

        if (!$mettKey || !in_array($mettKey, $this->getBackendUserKeys())) {
            $this->redirect('mettwochorder/index?mwOrderResetFailure=1');
            return;
        }

        $connection = $this->get('dbal_connection');

        try {
            $connection->delete(
                'mw_order',
                ['order_stop_date' => (new \DateTime())->format('Y-m-d')]
            );
        } catch (\Exception $e) {
            //nth
        }

        $this->redirect('mettwochorder/index?mwOrderReset=1');
    }

    private function getBackendUserKeys(): array
    {
        return $this->get('dbal_connection')
            ->createQueryBuilder()
            ->select('apiKey')
            ->from('s_core_auth')
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function saveDateAction()
    {
        Shopware()->Session()->offsetSet('mettwochDate', $this->Request()->getParam('mettwochDate', (new \DateTime())->format('Y-m-d')));
        exit();
    }
}

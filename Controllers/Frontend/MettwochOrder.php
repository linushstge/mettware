<?php

use Shopware\Components\Model\ModelManager;

class Shopware_Controllers_Frontend_MettwochOrder extends Enlight_Controller_Action
{
    public function indexAction()
    {
        if (!($shippingDate = $this->request->getParam('shippingDate'))) {
            $shippingDate = (new DateTime())->format('Y-m-d');
        }

        /** @var ModelManager $modelManager */
        $modelManager = $this->get('models');

        $orderAttrRepo = $modelManager->getRepository('Shopware\Models\Attribute\Order');

        $orderAttributes = $orderAttrRepo->findBy([
            'mettwochOrderDate' => $shippingDate
        ]);

        $orders = [];
        $sumAmount = 0;
        $quantityTotal = 0;
        foreach ($orderAttributes as $attribute) {
            $order = $attribute->getOrder();
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

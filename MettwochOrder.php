<?php

namespace MettwochOrder;

use Doctrine\ORM\Tools\SchemaTool;
use Shopware\Components\Plugin;

class MettwochOrder extends Plugin
{
    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure' => 'addTemplateDir',
            'Enlight_Controller_Front_StartDispatch' => 'onStartDispatch',
        ];
    }

    public function onStartDispatch()
    {
        $this->container->get('loader')->registerNamespace('Shopware\Components', $this->getPath() . '/Components/');
    }

    /**
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function addTemplateDir(\Enlight_Controller_ActionEventArgs $args)
    {
        $args->getSubject()->View()->addTemplateDir(__DIR__ . '/Resources/views');
    }

    /**
     * @inheritdoc
     */
    public function install(Plugin\Context\InstallContext $context)
    {
        $attributeService = $this->container->get('shopware_attribute.crud_service');
        $attributeService->update('s_order_attributes', 'mettwoch_order_date', 'date');

        $this->container->get('models')->generateAttributeModels();
    }
}

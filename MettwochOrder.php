<?php

namespace MettwochOrder;

use Doctrine\ORM\Tools\SchemaTool;
use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\UpdateContext;

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
        return true;
    }

    public function update(UpdateContext $context)
    {
        if ($context->getCurrentVersion() < '1.3.0') {
            $this->createMettwochTable();
        }

        parent::update($context);
    }

    private function createMettwochTable()
    {
        $connection = $this->container->get('dbal_connection');
        $connection->executeQuery('
                CREATE TABLE `mw_order` (
                  `order_stop_date` DATE,
                  UNIQUE (`order_stop_date`)
                );
            ');
    }


}

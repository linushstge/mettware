<?php

namespace MettwochOrder;

use Doctrine\Common\Collections\ArrayCollection;
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
            'Theme_Compiler_Collect_Plugin_JavaScript' => 'getJavaScriptCollection',
        ];
    }

    public function getJavaScriptCollection()
    {
        return new ArrayCollection([
            __DIR__ . '/Resources/views/frontend/_public/src/js/date-save.js',
        ]);
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
        $view = $args->getSubject()->View();
        $view->addTemplateDir(__DIR__ . '/Resources/views');

        $view->assign('mettwochDate', Shopware()->Session()->get('mettwochDate', (new \DateTime())->format('Y-m-d')));
    }

    /**
     * @inheritdoc
     */
    public function install(Plugin\Context\InstallContext $context)
    {
        $this->createMettwochTable();

        return true;
    }

    /**
     * @inheritdoc
     */
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
                CREATE TABLE IF NOT EXISTS `mw_order` (
                  `order_stop_date` DATE,
                  UNIQUE (`order_stop_date`)
                );
            ');
    }
}

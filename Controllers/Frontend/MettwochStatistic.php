<?php declare(strict_types=1);

class Shopware_Controllers_Frontend_MettwochStatistic extends Enlight_Controller_Action
{
    public function indexAction()
    {
        $conenction = $this->get('dbal_connection');
        $month = $this->Request()->get('month');
        $year = $this->Request()->get('year');
        $positions = $conenction->fetchAll(
            'SELECT sod.name, SUM(sod.quantity) as quantity
             FROM s_order so
             INNER JOIN s_order_details sod ON sod.orderID = so.id
                                
             WHERE YEAR(ordertime) = :year 
             AND MONTH(ordertime) = :month
             GROUP BY sod.name
             ORDER BY sod.name',
            [
                ':month' => $month,
                ':year' => $year,
            ]
        );

        $this->View()->assign('positions', $positions);

        $customers = $conenction->fetchAll(
            'SELECT su.firstname, su.lastname, SUM(so.invoice_amount) as amount
             FROM s_order so
             INNER JOIN s_user su ON so.userID = su.id
            
             WHERE YEAR(ordertime) = :year AND MONTH(ordertime) = :month
             GROUP BY su.id
             ORDER BY su.firstname, su.lastname',
            [
                ':month' => $month,
                ':year' => $year,
            ]
        );

        $this->View()->assign('customers', $customers);
    }
}

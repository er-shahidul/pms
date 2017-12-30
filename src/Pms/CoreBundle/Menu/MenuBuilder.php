<?php


namespace Pms\CoreBundle\Menu;

use Knp\Menu\FactoryInterface;

use Pms\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAware;

class MenuBuilder extends ContainerAware
{
    public function mainMenu(FactoryInterface $factory, array $options)
    {
      //  var_dump($options);die;
        $menu = $factory->createItem('root');
        $menu = $this->singleMenu($menu);
        $menu = $this->reportMenu($menu);
//        $menu = $this->itemReport($menu);
        $menu = $this->pettyCashMenu($menu);
        $menu = $this->inventoryMenu($menu);
        $menu = $this->settingMenu($menu);
        $menu = $this->usersMenu($menu);
        $this->container->get('xiidea.easy_menu_acl.access_filter')->apply($menu);

        return $menu;
    }

    public function pettyCashMenu($menu){
        $menu
            ->addChild('Patty Cash')
            ->setAttribute('icon','fa fa-bookmark')
            ->setAttribute('dropdown', true);

        $menu['Patty Cash']->addChild('Bank', array('route' => 'bank_amount'));
        $menu['Patty Cash']->addChild('Transaction', array('route' => 'transaction_history'));
//        $menu['Patty Cash']->addChild('Current Status', array('route' => 'report_history'));
        $menu['Patty Cash']->addChild('Petty Cash Report', array('route' => 'petty_cash_report'));

        return $menu;
    }

    public function inventoryMenu($menu){
        $menu
            ->addChild('Inventory')
            ->setAttribute('icon','fa fa-bookmark')
            ->setAttribute('dropdown', true);

        $menu['Inventory']->addChild('Delivery Item', array('route' => 'delivery_list'));
        $menu['Inventory']->addChild('Delivery item Project', array('route' => 'delivery_project_to_project_list'));
        $menu['Inventory']->addChild('Stock Item', array('route' => 'stock_list'));
        $menu['Inventory']->addChild('Issue Report', array('route' => 'delivery_report'));
        $menu['Inventory']->addChild('Inventory Valuation Report', array('route' => 'stock_report'));
//        $menu['Inventory']->addChild('Inventory Item Wise Report', array('route' => 'item_wise_report'));

        return $menu;
    }

    public function settingMenu($menu){
        $menu
            ->addChild('Setting')
            ->setAttribute('icon','fa fa-cogs')
            ->setAttribute('dropdown', true);

        $menu['Setting']->addChild('Item', array('route' => 'item'));
        $menu['Setting']->addChild('Item Type', array('route' => 'item_type'));
        $menu['Setting']->addChild('Project', array('route' => 'project'));
        $menu['Setting']->addChild('Company Type', array('route' => 'project_type'));
        $menu['Setting']->addChild('Category', array('route' => 'category'));
        $menu['Setting']->addChild('Sub Category', array('route' => 'sub_category'));
        $menu['Setting']->addChild('Area', array('route' => 'area'));

        $menu['Setting']->addChild('Cost Header', array('route' => 'cost_header'));
        $menu['Setting']->addChild('T&C', array('route' => 'terms_and_conditions'));
        $menu['Setting']->addChild('Purchase Type', array('route' => 'purchase_type'));
        $menu['Setting']->addChild('Company Setting', array('route' => 'company'));
        $menu['Setting']->addChild('Related Bank', array('route' => 'relatedbank'));

        $menu['Setting']->addChild('Database Backup', array('route' => 'database_backUp'));

        return $menu;
    }

    public function usersMenu($menu){
        $menu
            ->addChild('Users')
            ->setAttribute('icon','fa fa-cogs')
            ->setAttribute('dropdown', true);

        $menu['Users']->addChild('Users', array('route' => 'pms_user_manager'));
        $menu['Users']->addChild('Group', array('route' => 'fos_user_group_list'));

        return $menu;
    }

    public function reportMenu($menu){
        $menu
            ->addChild('Report')
            ->setAttribute('icon','fa fa-bookmark')
            ->setAttribute('dropdown', true);

        $menu['Report']->addChild('Item Report')
            ->setAttribute('icon','fa fa-bookmark')
            ->setAttribute('dropdown', true);
        $menu['Report']['Item Report']->addChild('Item Report', array('route' => 'report_item_report_bundle'));
        $menu['Report']['Item Report']->addChild('Item Price Overview', array('route' => 'report_item_over_view_report_bundle'));

        $menu['Report']->addChild('PR Report')
            ->setAttribute('icon','fa fa-bookmark')
            ->setAttribute('dropdown', true);
        $menu['Report']['PR Report']->addChild('PR Report', array('route' => 'report_requisition'));
        $menu['Report']['PR Report']->addChild('Daily Basis PR', array('route' => 'report_daily_requisition'));
        $menu['Report']['PR Report']->addChild('PR Activities', array('route' => 'report_activities_requisition'));

        $menu['Report']->addChild('PO Report')
            ->setAttribute('icon','fa fa-bookmark')
            ->setAttribute('dropdown', true);
        $menu['Report']['PO Report']->addChild('PO Report', array('route' => 'report_purchase_order'));
        $menu['Report']['PO Report']->addChild('Daily Basis PO', array('route' => 'report_daily_purchase_order'));
//        $menu['Report']['PO Report']->addChild('PO Activities', array('route' => 'report_activities_purchase'));

        $menu['Report']->addChild('Spend Report')
            ->setAttribute('icon','fa fa-bookmark')
            ->setAttribute('dropdown', true);
        $menu['Report']['Spend Report']->addChild('PurchaseType Wise ', array('route' => 'report_project_spend_purchase_type_wise'));
        $menu['Report']['Spend Report']->addChild('SubCategory Wise', array('route' => 'report_project_spend_sub_category_wise'));
        $menu['Report']['Spend Report']->addChild('Purchase Count ', array('route' => 'report_project_purchase_type_wise_count'));
        $menu['Report']['Spend Report']->addChild('Budget Vs Spend', array('route' => 'report_budget_vs_spend'));

        $menu['Report']->addChild('Payment Report')
            ->setAttribute('icon','fa fa-bookmark')
            ->setAttribute('dropdown', true);
        $menu['Report']['Payment Report']->addChild('Supplier Payment Report', array('route' => 'report_supplier_payment'));
        $menu['Report']['Payment Report']->addChild('Payment Report', array('route' => 'report_payment'));
        $menu['Report']['Payment Report']->addChild('Vendor Payment Overview', array('route' => 'report_vendor_status_two'));
        $menu['Report']['Payment Report']->addChild('Performance Overview', array('route' => 'report_vendor_status'));
        $menu['Report']['Payment Report']->addChild('Payment Summary', array('route' => 'payment_company_report_bundle'));

        $menu['Report']->addChild('Price Report')
            ->setAttribute('icon','fa fa-bookmark')
            ->setAttribute('dropdown', true);
        $menu['Report']['Price Report']->addChild('Price Directory', array('route' => 'report_purchase_price_directory'));
        $menu['Report']['Price Report']->addChild('Price Directory Old', array('route' => 'report_purchase_price_directory_old'));
        $menu['Report']['Price Report']->addChild('Compare Price Directory', array('route' => 'report_compare_price_directory'));
        $menu['Report']['Price Report']->addChild('Purchase Lowest Price', array('route' => 'report_purchase_lowest_price'));
        $menu['Report']['Price Report']->addChild('Lowest Two Month Price', array('route' => 'report_lowest_two_month_price'));
        $menu['Report']['Price Report']->addChild('Price Compare', array('route' => 'report_price_compare'));

        $menu['Report']->addChild('Budget Report')
            ->setAttribute('icon','fa fa-bookmark')
            ->setAttribute('dropdown', true);
        $menu['Report']['Budget Report']->addChild('Budget Report Yearly', array('route' => 'report_budget_yearly'));
        $menu['Report']['Budget Report']->addChild('Budget Report Monthly', array('route' => 'report_budget'));
//        $menu['Report']['Budget Report']->addChild('PR & PO Savings', array('route' => 'report_requisition_vs_order_saving'));

        $menu['Report']->addChild('User Report')
            ->setAttribute('icon','fa fa-bookmark')
            ->setAttribute('dropdown', true);
        $menu['Report']['User Report']->addChild('PR creator list', array('route' => 'report_user_activities_pr_creator'));
        $menu['Report']['User Report']->addChild('PR item claim list', array('route' => 'report_user_activities_claim'));
        $menu['Report']['User Report']->addChild('PO creator list', array('route' => 'report_user_activities_po_creator'));
        $menu['Report']['User Report']->addChild('Receive User list', array('route' => 'report_user_activities_receive'));
        $menu['Report']['User Report']->addChild('Gate Pass user list', array('route' => 'report_user_activities_gate_pass'));
        $menu['Report']['User Report']->addChild('User Activities', array('route' => 'report_user_activities'));

        $menu['Report']->addChild('Receive Report')
            ->setAttribute('icon','fa fa-bookmark')
            ->setAttribute('dropdown', true);
        $menu['Report']['Receive Report']->addChild('Receive Item Report', array('route' => 'receive_item_report_bundle'));
        $menu['Report']['Receive Report']->addChild('Receive Report', array('route' => 'report_receive_report_bundle'));

        $menu['Report']->addChild('Trend Report')
            ->setAttribute('icon','fa fa-bookmark')
            ->setAttribute('dropdown', true);
        $menu['Report']['Trend Report']->addChild('Item Trend Report', array('route' => 'report_trend_report'));

        $menu['Report']->addChild('System Usages Summary', array('route' => 'system_usages_summary'));
        $menu['Report']->addChild('Purchase Officer', array('route' => 'report_purchase_officer'));
//        $menu['Report']->addChild('Account Report ', array('route' => 'report_account'));
        $menu['Report']->addChild('Vendor Overview ', array('route' => 'report_vendor_overview'));
        $menu['Report']->addChild('Three Sixty Degree ', array('route' => 'report_three_sixty_degree'));

//        $menu['Report']->addChild('Vendor Payment Status 1', array('route' => 'vendor_payment_status_one_report_bundle'));
//        $menu['Report']->addChild('Vendor Payment Status 2', array('route' => 'vendor_payment_status_two_report_bundle'));
//        $menu['Report']->addChild('Regular Payment ', array('route' => 'regular_payment_report_bundle'));

        return $menu ;
    }

    /**
     * @param $menu
     */
    public function singleMenu($menu)
    {
     //   $user = $this->get('security.token_storage')->getToken()->getUser()->getRoles();

        $menu->setChildrenAttributes(array('class' => 'page-sidebar-menu'));
        $menu
            ->addChild('Dashboard', array('route' => 'homepage'))
            ->setAttribute('icon', 'fa fa-home');
        /*$menu
            ->addChild('My Task', array('route' => 'my_task'))
            ->setAttribute('icon', 'fa fa-calendar');*/
        $menu
            ->addChild('Purchase Requisition', array('route' => 'purchase_requisition'))
            ->setAttribute('icon', 'fa  fa-road');
        $menu
            ->addChild('Purchase Order', array('route' => 'purchase_order'))
            ->setAttribute('icon', 'fa fa-shopping-cart');

        $menu
            ->addChild('Vendor Database', array('route' => 'vendor'))
            ->setAttribute('icon', 'fa fa-shopping-cart');

        $menu
            ->addChild('Budget', array('route' => 'budget'))
            ->setAttribute('icon', 'fa fa-suitcase');
        $menu
            ->addChild('File Upload', array('route' => 'document'))
            ->setAttribute('icon', 'fa fa-cloud-upload');
        $menu
            ->addChild('Received Item', array('route' => 'receive'))
            ->setAttribute('icon', 'fa fa-download');
        $menu
            ->addChild('Payment', array('route' => 'payment'))
            ->setAttribute('icon', 'fa fa-download');
        $menu
            ->addChild('Advance Payment', array('route' => 'payment_advance'))
            ->setAttribute('icon', 'fa fa-download');
        $menu
            ->addChild('New Vendor Sourcing', array('route' => 'pms_supplier_homepage'))
            ->setAttribute('icon', 'fa fa-download');

        return $menu;
    }

    public function get($id)
    {
        return $this->container->get($id);
    }
}
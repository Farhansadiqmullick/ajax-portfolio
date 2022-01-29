<?php
if (!class_exists("WP_List_Table")) {
    require_once ABSPATH . '/wp-admin/includes/class-wp-list-table.php';
}

class DBTableUsers extends WP_List_Table
{
    function __construct($data)
    {
        parent::__construct();
        $this->items = $data;

       
    }

    function get_columns()
    {
        return [
            'cb'     => '<input type="checkbox">',
            'name'   => __('Name', 'ajax_port'),
            'thumbnail' => __('Feature Image', 'ajax_port'),
            'id' => __('Portfolio ID', 'ajax_port'),
        ];
    }

    function column_default($item, $column_name)
    {
        return $item[$column_name];
    }

    function prepare_items()
    {
        $this->_column_headers = array($this->get_columns(), [], []);
    }

}

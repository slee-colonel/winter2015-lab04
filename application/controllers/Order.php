<?php

/**
 * Order handler
 * 
 * Implement the different order handling usecases.
 * 
 * controllers/welcome.php
 *
 * ------------------------------------------------------------------------
 */
class Order extends Application {

    function __construct() {
        parent::__construct();
    }

    // start a new order
    function neworder() {
        // order number is incremented from last one
        $order_num = $this->orders->highest() + 1;
        
        // create a new order and fill with default values,
        // 'create' is from MY_Model.php and creates a new object with
        // variables named after the columns in the table it references
        $neworder = $this->orders->create();
        $neworder->num = $order_num;
        $neworder->date = date();
        $neworder->status = 'a';
        $neworder->total = 0;
        
        // actually add the new order to the orders table,
        // 'add' is from MY_Model.php
        $this->orders->add($neworder);
        
        redirect('/order/display_menu/' . $order_num);
    }

    // add to an order
    function display_menu($order_num = null) {
        if ($order_num == null)
            redirect('/order/neworder');

        $this->data['pagebody'] = 'show_menu';
        $this->data['order_num'] = $order_num;
        
        // changes the title of the page according to order number
        $this->data['title'] = "Order # " . $order_num . ' (' .
                number_format($this->orders->total($order_num), 2) . ')';
        
        // Make the columns
        $this->data['meals'] = $this->make_column('m');
        $this->data['drinks'] = $this->make_column('d');
        $this->data['sweets'] = $this->make_column('s');

	// Bit of a hokey patch here, to work around the problem of the template
	// parser no longer allowing access to a parent variable inside a
	// child loop - used for the columns in the menu display.
	// this feature, formerly in CI2.2, was removed in CI3 because
	// it presented a security vulnerability.
	// 
	// This means that we cannot reference order_num inside of any of the
	// variable pair loops in our view, but must instead make sure
	// that any such substitutions we wish make are injected into the 
	// variable parameters
	// Merge this fix into your origin/master for the lab!
	$this->hokeyfix($this->data['meals'],$order_num);
	$this->hokeyfix($this->data['drinks'],$order_num);
	$this->hokeyfix($this->data['sweets'],$order_num);
	// end of hokey patch
	
        $this->render();
    }

    // inject order # into nested variable pair parameters
    function hokeyfix($varpair,$order) {
	foreach($varpair as &$record)
	    $record->order_num = $order;
    }
    
    // make a menu ordering column
    function make_column($category) {
        // gets the menu items according to category
        return $this->menu->some('category', $category);
    }

    // add an item to an order
    function add($order_num, $item) {
        // call add_item in Orders.php from models,
        // this controller function is merely an interface to add_item
        $this->orders->add_item($order_num,$item);
        redirect('/order/display_menu/' . $order_num);
    }

    // checkout
    function checkout($order_num) {
        $this->data['title'] = 'Checking Out';
        $this->data['pagebody'] = 'show_order';
        $this->data['order_num'] = $order_num;
        
        // get the total price of the order, accurate to 2 places
        // after decimal point
        $this->data['total'] = number_format($this->orders->total($order_num), 2);
        
        // get the list of items in the order
        $items = $this->orderitems->group($order_num);
        foreach ($items as $item)
        {
            $menuitem = $this->menu->get($item->item);
            // get the actual name of the current item
            $item->code = $menuitem->name;
        }
        $this->data['items'] = $items;
        
        // the original code was supposed to be able to disable 
        // the Proceed button but didn't, this is the fixed version
        $this->data['okornot'] = $this->orders->validate($order_num) ?
            '' : 'disabled';
        
        $this->render();
    }

    // proceed with checkout
    function commit($order_num) {
        
        // a failsafe for if the Proceed button is supposed
        // to be disabled but isn't, this way the order won't
        // go through incorrectly
        if (!$this->orders->validate($order_num))
            redirect('/order/display_menu/' . $order_num);
        
        // update order in the orders table with new datetime,
        // status, and total value
        $record = $this->orders->get($order_num);
        $record->date = date(DATE_ATOM);
        $record->status = 'c';
        $record->total = $this->orders->total($order_num);
        $this->orders->update($record);
        
        redirect('/');
    }

    // cancel the order
    function cancel($order_num) {
        // deletes order items in the order
        $this->orderitems->delete_some($order_num);
        
        // set the current order to cancelled status ('x') but
        // don't delete it from the orders table
        $record = $this->orders->get($order_num);
        $record->status = 'x';
        $this->orders->update($record);
        redirect('/');
    }

}

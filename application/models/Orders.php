<?php

/**
 * Data access wrapper for "orders" table.
 *
 * @author jim
 */
class Orders extends MY_Model {

    // constructor
    function __construct() {
        parent::__construct('orders', 'num');
    }

    // add an item to an order
    function add_item($num, $code) 
    {
        // get access to the models loaded into the app
        $CI = &get_instance();
        
        // if item (determined by 'code') already exists in order
        // (determined by 'num'), then increment quantity of the item
        if ($CI->orderitems->exists($num, $code))
        {
            $record = $CI->orderitems->get($num, $code);
            $record->quantity++;
            $CI->orderitems->update($record);
        }
        // if item (determined by 'code') does not exist in order
        // (determined by 'num'), then create new order item, set
        // quantity to 1, and add it to the orderitems table
        else
        {
            $record = $CI->orderitems->create();
            $record->order = $num;
            $record->item = $code;
            $record->quantity = 1;
            $CI->orderitems->add($record);
        }
    }

    // calculate the total for an order
    function total($num) 
    {
        // get access to the tables loaded into the app
        $CI = &get_instance();
        
        // num in this instance is order number
        $items = $CI->orderitems->group($num);
        $result = 0;
        
        // sum up the total for an order
        if (count($items) > 0)
            foreach ($items as $item)
            {
                $menu = $CI->menu->get($item->item);
                $result += $item->quantity * $menu->price;
            }
        return $result;
    }

    // retrieve the details for an order
    function details($num) {
        
    }

    // cancel an order
    function flush($num) {
        
    }

    // validate an order
    // it must have at least one item from each category
    function validate($num) 
    { 
        // get access to the tables loaded into the app
        $CI = &get_instance();
        
        // get the list of items in the order
        $items = $CI->orderitems->group($num);
        $gotem = array();
        if (count($items) > 0)
            foreach ($items as $item)
            {
                // for each category detected in the order,
                // set the detection value to 1
                $menu = $CI->menu->get($item->item);
                $gotem[$menu->category] = 1;
            }
        
        // returns true only if all 3 categories present
        return isset($gotem['m']) && isset($gotem['d']) && isset($gotem['s']);
    }

}

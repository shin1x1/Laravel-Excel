<?php namespace Maatwebsite\Excel\Classes;

use \PHPExcel_Worksheet;

/**
 * PHPExcel
 *
 * Copyright (c) 2006 - 2014 PHPExcel
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPExcel
 * @package    PHPExcel_Worksheet
 * @copyright  Copyright (c) 2006 - 2014 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 * @version    ##VERSION##, ##DATE##
 */


/**
 * PHPExcel_Worksheet
 *
 * @category   PHPExcel
 * @package    PHPExcel_Worksheet
 * @copyright  Copyright (c) 2006 - 2014 PHPExcel (http://www.codeplex.com/PHPExcel)
 */
class LaravelExcelWorksheet extends PHPExcel_Worksheet
{

    /**
     * Parent
     * @var [type]
     */
    public $_parent;

    /**
     * Data
     * @var [type]
     */
    public $data = array();

    /**
     * Allowed page setup
     * @var array
     */
    public $allowedPageSetup = array(
        'orientation', 'paperSize', 'scale', 'fitToPage', 'fitToHeight', 'fitToWidth', 'columnsToRepeatAtLeft', 'rowsToRepeatAtTop', 'horizontalCentered', 'verticalCentered', 'printArea', 'firstPageNumber'
    );

    /**
     * Create a new worksheet
     *
     * @param PHPExcel        $pParent
     * @param string        $pTitle
     */
    public function __construct(PHPExcel $pParent = null, $pTitle = 'Worksheet')
    {
        parent::__construct($pParent, $pTitle);
        $this->setParent($pParent);
    }

    /**
     * Set default page setup
     */
    public function setDefaultPageSetup()
    {
        $pageSetup = $this->getPageSetup();

        foreach($this->allowedPageSetup as $setup)
        {
            // set the setter
            list($setter, $set) = $this->_setSetter($setup);

            // get the value
            $value = \Config::get('excel::sheets.pageSetup.' . $setup, NULL);

            // Set the page setup value
            if(!is_null($value))
                $pageSetup->{$setter}($value);
        }
    }

    // /**
    //  * Set the view
    //  * @param [type] $name      [description]
    //  * @param array  $data      [description]
    //  * @param array  $mergeData [description]
    //  */
    // public function setView($name, $data = array(), $mergeData = array())
    // {
    //     $this->getParent()->view = $name;
    //     $this->getParent()->data = $data;
    //     $this->getParent()->mergeData = $mergeData;
    //     return $this;
    // }

    // /**
    //  * Get the view
    //  * @return [type] [description]
    //  */
    // public function getView()
    // {
    //     return array(
    //         $this->getParent()->view,
    //         $this->getParent()->data,
    //         $this->getParent()->mergeData
    //     );
    // }

    /**
     * Set data for the current sheet
     * @param  [type]  $keys  [description]
     * @param  boolean $value [description]
     * @return [type]         [description]
     */
    public function with($key, $value = false)
    {
        // Add the vars
        $this->_addVars($key, $value);
    }

    /**
     * Add vars to the data array
     * @param [type]  $key   [description]
     * @param boolean $value [description]
     */
    protected function _addVars($key, $value = false)
    {
        // Add array of data
        if(is_array($key))
        {
            $this->data = array_merge($this->data, $key);
            $this->fromArray($this->data);
        }

        // Add seperate values
        else
        {
            $this->data[$key] = $value;
        }
    }

    /**
     * Set attributes
     * @param [type] $key    [description]
     * @param [type] $params [description]
     */
    public function _setAttributes($setter, $params)
    {
        // Set the setter and the key
        list($setter, $key) = $this->_setSetter($setter);

        // If is page setup
        if(in_array($key, $this->allowedPageSetup))
        {
            // Set params
            $params = is_array($params) ? $params : array($params);

            // Call the setter
            return call_user_func_array(array($this->getPageSetup(), $setter), $params);
        }
    }

    /**
     * Set the setter
     * @param [type] $setter [description]
     */
    protected function _setSetter($setter)
    {
        if(starts_with($setter, 'set'))
        {
            $key = lcfirst(str_replace('set', '', $setter));
        }
        else
        {
            $key = $setter;
            $setter = 'set' . ucfirst($key);
        }

        // Return the setter method and the key
        return array($setter, $key);
    }

     /**
     * Set the parent (excel object)
     * @param [type] $parent [description]
     */
    public function setParent($parent)
    {
        $this->_parent = $parent;
    }

    /**
     * Get the parent excel obj
     * @return [type] [description]
     */
    public function getParent()
    {
        return $this->_parent;
    }

    /**
     * Autosize column for document
     *
     * @return int
     */
    public function setAutoSize($columns = false)
    {
        if(!is_array($columns) && $columns)
        {
            $toCol = $this->getHighestColumn();

            $toCol++;
            for ($i = 'A'; $i !== $toCol; $i++) {
                $this->getColumnDimension($i)->setAutoSize(true);
            }
        }
        elseif(is_array($columns))
        {
            foreach($columns as $column)
            {
                $this->getColumnDimension($column)->setAutoSize(true);
            }
        }

        $this->calculateColumnWidths();

    }

    /**
     * Dynamically call methods
     * @param  [type] $method [description]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    public function __call($method, $params)
    {

        // If the dynamic call starts with "with", add the var to the data array
        if(starts_with($method, 'with'))
        {
            $key = lcfirst(str_replace('with', '', $method));
            $this->_addVars($key, reset($params));
        }

        // If it's a stter
        elseif(starts_with($method, 'set') )
        {
            // set the attribute
            $this->_setAttributes($method, $params);
        }

        return $this;
    }

    /**
     * Reset data on class destruct
     */
    public function __destruct()
    {
        $this->data = array();
    }

}
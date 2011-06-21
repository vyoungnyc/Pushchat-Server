<?php
/* SVN FILE: $Id: app_model.php 7945 2008-12-19 02:16:01Z gwoo $ */
/**
 * Application model for Cake.
 *
 * This file is application-wide model file. You can put all
 * application-wide model-related methods here.
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) :  Rapid Development Framework (http://www.cakephp.org)
 * Copyright 2005-2008, Cake Software Foundation, Inc. (http://www.cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright     Copyright 2005-2008, Cake Software Foundation, Inc. (http://www.cakefoundation.org)
 * @link          http://www.cakefoundation.org/projects/info/cakephp CakePHP(tm) Project
 * @package       cake
 * @subpackage    cake.cake.libs.model
 * @since         CakePHP(tm) v 0.2.9
 * @version       $Revision: 7945 $
 * @modifiedby    $LastChangedBy: gwoo $
 * @lastmodified  $Date: 2008-12-18 18:16:01 -0800 (Thu, 18 Dec 2008) $
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */
/**
 * Application model for Cake.
 *
 * This is a placeholder class.
 * Create the same file in app/app_model.php
 * Add your application-wide methods to the class, your models will inherit them.
 *
 * @package       cake
 * @subpackage    cake.cake.libs.model
 */
class AppModel extends Model {
//modified find() to support HAVING
public function find($conditions = null, $fields = array(), $order = null, $recursive = null) {
	if (is_array($fields) && !empty($fields['having']) && !empty($fields['group'])) {
		if (!is_array($fields['group'])) {
			$fields['group'] = array($fields['group']);
		}
		
		$ds = $this->getDataSource();
		$having = $ds->conditions($fields['having'], true, false);
		$fields['group'][count($fields['group']) - 1] .= " HAVING $having";
	}
	
	return parent::find($conditions, $fields, $order, $recursive);
}


}
?>

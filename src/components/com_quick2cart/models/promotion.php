<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2016 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

/**
 * Quick2cart model.
 *
 * @since  1.6
 */
class Quick2cartModelPromotion extends JModelAdmin
{
	/**
	 * @var      string    The prefix to use with controller messages.
	 * @since    1.6
	 */
	protected $text_prefix = 'COM_QUICK2CART';

	/**
	 * @var   	string  	Alias to manage history control
	 * @since   3.2
	 */
	public $typeAlias = 'com_quick2cart.promotion';

	/**
	 * @var null  Item data
	 * @since  1.6
	 */
	protected $item = null;

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return    JTable    A database object
	 *
	 * @since    1.6
	 */
	public function getTable($type = 'Promotion', $prefix = 'Quick2cartTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm  A JForm object on success, false on failure
	 *
	 * @since    1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm(
			'com_quick2cart.promotion', 'promotion',
			array('control' => 'jform',
				'load_data' => $loadData
			)
		);

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return   mixed  The data for the form.
	 *
	 * @since    1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_quick2cart.edit.promotion.data', array());

		if (empty($data))
		{
			if ($this->item === null)
			{
				$this->item = $this->getItem();
			}

			$data = $this->item;
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since    1.6
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk))
		{
			// Do any procesing on fields here if needed
		}

		return $item;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @param   JTable  $table  Table Object
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');

		if (empty($table->id))
		{
			// Set ordering to the last item if not set
			if (@$table->ordering === '')
			{
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__kart_promotions');
				$max             = $db->loadResult();
				$table->ordering = $max + 1;
			}
		}
	}

	/**
	 * Function to save conditions for promotion rule
	 *
	 * @param   ARRAY  $data  data to be saved
	 *
	 * @return true/false
	 *
	 * @since  2.5
	 *
	 * */
	public function save($data)
	{
		$input = JFactory::getApplication()->input;
		$conditionData = $discountdata = array();
		$conditionData['condition'] = $input->post->get('rule', '', 'array');
		$conditionData['conditions_compulsory'] = $input->post->get('conditions_compulsory', '', 'STRING');

		// Remove data of clone div
		unset($conditionData['condition']['conditions']['primary']);

		$discountdata['qtc_discount'] = $input->post->get('qtc_discount', '', 'array');
		$discountdata['multi_cur'] = $input->post->get('multi_cur', '', 'array');

		// Dont save coupon code, max use and max per user if coupon required is disabled
		if ($data['coupon_required'] == '0')
		{
			$data['coupon_code'] = "";
			$data['max_use'] = "";
			$data['max_per_user'] = "";
		}

		if (empty($data['from_date']))
		{
			$date = JFactory::getDate();
			$data['from_date'] = $date->format('Y-m-d H:i:s', false);
		}

		if (empty($conditionData['condition']['conditions']))
		{
			return false;
		}
		else
		{
			$promotionInfo = $data;

			if (parent::save($data))
			{
				// To get id of inserted/updated last record
				$promotionId = (int) $this->getState($this->getName() . '.id');
				$promotionInfo['id'] = $promotionId;
				$this->saveDiscountPrice($discountdata, $promotionInfo, $data['discount_type']);

				foreach ($conditionData['condition']['conditions'] as $key => $condition)
				{
					$record = new stdclass;

					$record->id = (!empty($condition['id']))?$condition['id']:'';

					$record->condition_on = strtolower((!empty($condition['condition_on']))?$condition['condition_on']:'');

					$record->condition_on_attribute = $condition['condition_on_attribute'];
					$record->promotion_id = $promotionId;

					if (is_array($condition['condition_attribute_value']))
					{
						$record->condition_attribute_value = json_encode($condition['condition_attribute_value']);
					}
					else
					{
						$record->condition_attribute_value = json_encode(explode(",", $condition['condition_attribute_value']));
					}

					$record->operation = (!empty($condition['operation']))?$condition['operation']:'';

					$record->is_compulsary = (!empty($conditionData['conditions_compulsory']))?$conditionData['conditions_compulsory']:'';

					$record->quantity = (!empty($condition['quantity']))?$condition['quantity']:null;

					// If option already exist then update the record else insert new record
					if (!empty($record->id))
					{
						$result = JFactory::getDbo()->updateObject('#__kart_promotions_rules', $record, 'id', true);
					}
					else
					{
						$result = JFactory::getDbo()->insertObject('#__kart_promotions_rules', $record);
					}
				}

				if ($result)
				{
					return true;
				}
				else
				{
					return false;
				}
			}
		}
	}

	/**
	 * Function to get rule conditions
	 *
	 * @param   INT  $rule_id  rule id
	 *
	 * @return true/false
	 *
	 * @since  2.5
	 *
	 * */
	public function getRuleConditions($rule_id)
	{
		if (!empty($rule_id))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from('#__kart_promotions_rules');
			$query->where('promotion_id' . ' = ' . $rule_id);
			$db->setQuery($query);
			$conditionList = $db->loadObjectList();

			return $conditionList;
		}
		else
		{
			return new stdclass;
		}
	}

	/**
	 * Function to get max condition id
	 *
	 * @param   INT  $rule_id  rule id
	 *
	 * @return true/false
	 *
	 * @since  2.5
	 *
	 * */
	public function getConditionsMax($rule_id)
	{
		if (!empty($rule_id))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('MAX(id)');
			$query->from('#__kart_promotions_rules');
			$query->where('promotion_id' . ' = ' . $rule_id);
			$db->setQuery($query);
			$conditionMax = $db->loadResult();

			return $conditionMax;
		}
		else
		{
			return new stdclass;
		}
	}

	/**
	 * Function to delete promotion code
	 *
	 * @param   INT  $cid  condition id
	 *
	 * @return true/false
	 *
	 * @since  2.5
	 *
	 * */
	public function qtc_delete_promotion_condition($cid)
	{
		if (!empty($cid))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__kart_promotions_rules'));
			$query->where($db->quoteName('id') . " = " . $cid);
			$db->setQuery($query);

			if ($db->execute())
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Function to delete promotion discounts
	 *
	 * @param   INT  $pid  promotion id
	 *
	 * @return true/false
	 *
	 * @since  2.5
	 *
	 * */
	public function deletePromotionDiscounts($pid)
	{
		if (!empty($pid))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__kart_promotion_discount'));
			$query->where($db->quoteName('promotion_id') . " = " . $pid);
			$db->setQuery($query);

			if ($db->execute())
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * Function to generate promotion description
	 *
	 * @param   INT  $promotion_info  promotion info
	 *
	 * @return true/false
	 *
	 * @since  2.5
	 *
	 * */
	public function generatePromotionDescription($promotion_info)
	{
		$conditionList = $this->getRuleConditions($promotion_info->id);
		$description = "";
		$conditionCount = 0;

		foreach ($conditionList as $condition)
		{
			$conditionCount++;
		}

		if ($conditionCount == "0")
		{
			return $description;
		}

		if (!empty($conditionList))
		{
			$description .= JText::_("COM_QUICK2CART_BUY") . " ";
		}

		foreach ($conditionList as $key => $condition)
		{
			$array = explode(',', implode(',', (array) json_decode($condition->condition_attribute_value)));

			$quick2cartHelper = new Comquick2cartHelper;

			if ($condition->condition_on_attribute == 'category' || $condition->condition_on_attribute == 'item_id')
			{
				if (!empty($condition->quantity))
				{
					if ($condition->operation == '=')
					{
						$description .= $condition->quantity . " " . JText::_("COM_QUICK2CART_PROMOTION_FROM") . " ";
					}
					elseif ($condition->operation == '<')
					{
						$description .= JText::_("COM_QUICK2CART_LESS_THAN");
						$description .= $condition->quantity . " ";
					}
					elseif ($condition->operation == '>')
					{
						$description .= JText::_("COM_QUICK2CART_MORE_THAN");
						$description .= " " . $condition->quantity . " ";
					}
					elseif ($condition->operation == '<=')
					{
						$description .= JText::_("COM_QUICK2CART_LESS_THAN_OR_EQUAL");
						$description .= $condition->quantity . " ";
					}
					elseif ($condition->operation == '>=')
					{
						$description .= JText::_("COM_QUICK2CART_MORE_THAN_OR_EQUAL");
						$description .= " " . $condition->quantity . " ";
					}
				}

				if ($condition->condition_on_attribute == 'category')
				{
					$description .= " " . JText::_("FROM_DATE") . " ";

					$description .= " " . JText::_("COM_QUICK2CART_CAT") . " ";

					if (!empty($condition->condition_attribute_value))
					{
						$description .= (count($array) > 1)?" (":"";

						$flag = 1;

						foreach ($array as $a)
						{
							$description .= $quick2cartHelper->getCatName($a);

							if ($flag < count($array))
							{
								$description .= " " . JText::_("COM_QUICK2CART_PROMOTION_CONDITION_OR") . " ";
							}

							$flag++;
						}

						$description .= (count($array) > 1)?" )":"";
					}
				}

				if ($condition->condition_on_attribute == 'item_id')
				{
					if (!empty($condition->condition_attribute_value))
					{
						$description .= (count($array) > 1)?" (":"";
						$flag = 1;

						foreach ($array as $a)
						{
							// Load model file
							$path = JPATH_SITE . "/components/com_quick2cart/models/cart.php";

							if (!class_exists("Quick2cartModelcart"))
							{
								JLoader::register("Quick2cartModelcart", $path);
								JLoader::load("Quick2cartModelcart");
							}

							$cartModel = new Quick2cartModelcart;

							$itemDetails = $cartModel->getItemRec($a);

							if (!empty($itemDetails->name))
							{
								$description .= $itemDetails->name;
							}

							if ($flag < count($array))
							{
								$description .= " " . JText::_("COM_QUICK2CART_PROMOTION_CONDITION_OR") . " ";
							}

							$flag++;
						}

						$description .= (count($array) > 1)?" )":"";
					}
				}
			}

			if ($condition->condition_on_attribute == 'cart_amount' || $condition->condition_on_attribute == 'quantity_in_store_cart')
			{
				$description .= " " . JText::_("COM_QUICK2CART_PROMOTION_CONDITION_IF") . " ";

				if (!empty($condition->condition_on_attribute))
				{
					$description .= str_replace('_', ' ', ($condition->condition_on_attribute));
				}

				$description .= " " . strtolower(JText::_("COM_QUICK2CART_PROMOTION_CONDITION_IS")) . " ";

				if ($condition->operation == '=')
				{
					$description .= " " . JText::_("COM_QUICK2CART_PROMOTION_CONDITION_EQUAL_TO") . " ";
				}
				elseif ($condition->operation == '<')
				{
					$description .= " " . JText::_("COM_QUICK2CART_PROMOTION_CONDITION_LESS_THAN") . " ";
				}
				elseif ($condition->operation == '>')
				{
					$description .= " " . JText::_("COM_QUICK2CART_PROMOTION_CONDITION_GREATER_THAN") . " ";
				}

				$array = (array) json_decode($condition->condition_attribute_value);

				$flag = 1;

				if ($condition->condition_on_attribute == 'cart_amount')
				{
					foreach ($array as $curr => $val)
					{
						$description .= (count($array) > 1)?" (":"";
						$description .= " " . $curr . " " . $val;

						if ($flag < count($array))
						{
							$description .= " " . JText::_("COM_QUICK2CART_PROMOTION_CONDITION_OR") . " ";
						}

						$description .= (count($array) > 1)?" )":"";
					}
				}

				if ($condition->condition_on_attribute == 'quantity_in_store_cart')
				{
					if (isset($array[0]))
					{
						$description .= ($array[0]);
					}
				}

				$flag++;
			}

			// Hide compulsary text for last record
			if ($conditionCount > $key + 1)
			{
				$description .= " " . $condition->is_compulsary . ' ';
			}
		}

		$description .= " " . JText::_("COM_QUICK2CART_PROMOTION_CONDITION_THEN") . " " . JText::_("COM_QUICK2CART_PROMOTION_CONDITION_GET") . " ";

		if ($promotion_info->discount_type == "percentage")
		{
			$disountInfo = $this->getDiscountRecords($promotion_info->id);
			$description .= " " . $disountInfo[0]['discount'] . " % " . JText::_("COM_QUICK2CART_PRICE_DISCOUNT");
		}

		if ($promotion_info->discount_type == "flat")
		{
			$disountInfo = $this->getDiscountRecords($promotion_info->id);

			$description .= " " . ucfirst($promotion_info->discount_type) . " " . JText::_("COM_QUICK2CART_PROMOTION_CONDITION_DISCOUNT_OF");

			$flag = 1;

			$description .= (count($disountInfo) > 1)?" (":"";

			foreach ($disountInfo as $info)
			{
				$description .= " " . $info['currency'] . " " . $info['discount'];

				if ($flag < count($disountInfo))
				{
					$description .= ", ";
					$flag++;
				}
			}

			$description .= (count($disountInfo) > 1)?" )":"";

			$flag++;
		}

		return $description;
	}

	/**
	 * Function to save currency wise discount price
	 *
	 * @param   ARRAY   $discountdata   discount data
	 * @param   INT     $promotionInfo  promotion info
	 * @param   STRING  $discountType   discount type
	 *
	 * @return true/false
	 *
	 * @since  2.8
	 *
	 * */
	public function saveDiscountPrice($discountdata, $promotionInfo, $discountType)
	{
		if (!empty($discountdata['qtc_discount']))
		{
			if ($promotionInfo['discount_type'] == "flat")
			{
				if (!empty($promotionInfo['id']))
				{
					$this->deletePromotionDiscounts($promotionInfo['id']);
				}

				foreach ($discountdata['qtc_discount']['flat'] as $key => $value)
				{
					$record = new stdclass;
					$record->promotion_id = $promotionInfo['id'];
					$record->discount = $value;

					$record->currency = $key;
					$record->discount_type = $discountType;

					// Insert record in promotion discount table
					$result = JFactory::getDbo()->insertObject('#__kart_promotion_discount', $record);
				}
			}
			else
			{
				if (!empty($promotionInfo['id']))
				{
					$this->deletePromotionDiscounts($promotionInfo['id']);
				}

				if ($promotionInfo['discount_type'] == 'percentage')
				{
					foreach ($discountdata['multi_cur'] as $key => $value)
					{
						$record = new stdclass;
						$record->promotion_id = $promotionInfo['id'];
						$record->discount = $discountdata['qtc_discount']['percent'];

						if (!empty($discountdata['multi_cur'][$key]))
						{
							$record->max_discount = $discountdata['multi_cur'][$key];
						}

						$record->currency = $key;
						$record->discount_type = $discountType;

						// Insert record in promotion discounts table
						$result = JFactory::getDbo()->insertObject('#__kart_promotion_discount', $record);
					}
				}
				else
				{
					$record = new stdclass;
					$record->promotion_id = $promotionInfo['id'];
					$record->discount = $discountdata['qtc_discount']['percent'];
					$record->discount_type = $discountType;

					// Insert record in promotion discounts table
					$result = JFactory::getDbo()->insertObject('#__kart_promotion_discount', $record);
				}
			}
		}
	}

	/**
	 * Function to get currency wise discount price for promotion
	 *
	 * @param   INT  $promotionId  promotion id
	 *
	 * @return true/false
	 *
	 * @since  2.8
	 *
	 * */
	public function getDiscountRecords($promotionId)
	{
		if (!empty($promotionId))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*');
			$query->from('#__kart_promotion_discount');
			$query->where('promotion_id' . ' = ' . $promotionId);
			$db->setQuery($query);
			$discountRecords = $db->loadAssocList();

			return $discountRecords;
		}
		else
		{
			return false;
		}
	}
}

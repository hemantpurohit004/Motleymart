<?php
/**
 * @version    SVN: <svn_id>
 * @package    Quick2cart
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

// No direct access.
defined('_JEXEC') or die();

$document = JFactory::getDocument();
?>
<div class="<?php echo Q2C_WRAPPER_CLASS; ?>" >
<?php
//$document->addStyleSheet(JUri::base().'components/com_quick2cart/assets/css/quick2cart.css' );//aniket

//$checkout='index.php?option=com_quick2cart&view=cartcheckout';
//$itemid=comquick2cartHelper::getitemid($checkout);

//$checkout=JUri::root().substr(JRoute::_('index.php?option=com_quick2cart&view=cartcheckout&Itemid='.$itemid),strlen(JUri::base(true))+1);
$link=JUri::base();
?>

<div class="well" >
<div class="alert alert-danger">
<span ><?php echo JText::_('QTC_OPERATION_CANCELLED'); ?> </span>
</div>
<a class="btn" href="<?php echo $link; ?>"><?php echo JText::_('QTC_BACK'); ?></a>

</div>

</div><!-- eoc techjoomla-bootstrap -->

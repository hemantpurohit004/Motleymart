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

?>
<div class="row-fluid " >

	<div style="float:left;">
		<?php
		if (!empty($this->siteInvoiceInfo['companyName']) )
		{ ?>
			<h2><?php echo $this->siteInvoiceInfo['companyName'] . '<br/>'; ?></h2>
		<?php
		}
		?>

	</div>
	<div style="float:right;">
			<b><i><?php echo JText::_('QTC_INVOICE_CONT_INFO'); ?></i></b> <br/>
		<?php

		if (!empty($this->siteInvoiceInfo['address']) )
		{ ?>
			<b><?php echo JText::_('QTC_INVOICE_ADDR');?></b> :
			<?php echo nl2br($this->siteInvoiceInfo['address']) . '<br/>';
		}

		if (!empty($this->siteInvoiceInfo['contactNumber']) )
		{ ?>
			<b><?php echo JText::_('COM_QUICK2CART_INVOICE_SITE_CONTACT_NO');?></b> :
			<?php echo $this->siteInvoiceInfo['contactNumber'] . '<br/>';
		}

		if (!empty($this->siteInvoiceInfo['fax']) )
		{ ?>
			<b><?php echo JText::_('COM_QUICK2CART_INVOICE_SITE_FAX');?></b> :
			<?php echo $this->siteInvoiceInfo['fax'] . '<br/>';
		}

		if (!empty($this->siteInvoiceInfo['email']) )
		{ ?>
			<b><?php echo JText::_('QTC_INVOICE_EMAIL');?></b> :
			<?php echo $this->siteInvoiceInfo['email'] . '<br/>';
		}

		if (!empty($this->siteInvoiceInfo['vat_num']))
		{
		?>
			<b><?php echo JText::_('QTC_INVOICE_VAT');?></b> :
			<?php echo $this->siteInvoiceInfo['vat_num'] . '<br/>';
		}
		?>
	</div>

</div>
<div style="clear:both;"></div>

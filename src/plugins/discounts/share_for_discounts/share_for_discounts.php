<?php
/**
 * @version    SVN: <svn_id>
 * @package    Share_For_Discounts
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

jimport('joomla.plugin.plugin');
$lang = JFactory::getLanguage();
$lang->load('plg_discounts_share_for_discounts', JPATH_ADMINISTRATOR);

/**
 * Plg_share_for_discounts
 *
 * @package     Plgshare_For_Discounts
 * @subpackage  site
 * @since       1.0
 */
class PlgDiscountsShare_For_Discounts extends JPlugin
{
	/**
	 * Method to get facebook sdk js
	 *
	 * @return  null
	 *
	 * @since   1.0
	 */
	public function get_facebook_sdk_js()
	{
		ob_start();
		?>
		<script>
		<?php
		if ($this->params->get('load_fb_sdk_js') == 1)
		{
		?>
			(function(d, s, id){
				var js, fjs = d.getElementsByTagName(s)[0];
				if (d.getElementById(id)) {return;}
				js = d.createElement(s); js.id = id;
				js.src = "//connect.facebook.net/en_US/sdk.js";
				fjs.parentNode.insertBefore(js, fjs);
			}(document, 'script', 'facebook-jssdk'));
		<?php
		}
		?>

		window.fbAsyncInit = function()
			{
				FB.init({
					appId      : '<?php echo $this->params->get('facebook_app_id');?>',
					xfbml      : true,
					version    : 'v2.3'
				});
			};
		function sendFBRequest(productUrl)
		{
			FB.login(function(response) {
			if (response.authResponse) {
			FB.api('/me', function(response) {
					FB.ui({
					method: 'share',
					app_id:'<?php echo $this->params->get('facebook_app_id');?>',
					href: productUrl,
				}, function(response){

					if (response.post_id)
					{
						techjoomla.jQuery('#tj-sd-coupon').text('<?php echo $this->params->get('coupon_code')?>');
						techjoomla.jQuery("#tj-sd-coupon").removeClass("code-blur-wrapper");
					}
					else
					{
						alert("<?php echo JText::_("PLG_SHARE_FOR_DISCOUNTS_ERROR_MSG");?>");
					}
					});
				});
				}
				else
				{
					alert("<?php echo JText::_("PLG_SHARE_FOR_DISCOUNTS_AUTHENTICATION_ERROR_MSG");?>");
				}
			},{scope: 'publish_actions',return_scopes: true});
		}
		</script>
		<?php
		$script = ob_get_contents();
		ob_end_clean();

		return $script;
	}

	/**
	 * [Method to get CSS and HTML for plugin]
	 *
	 * @param   [type]  $productUrl  [description]
	 *
	 * @return  [type]               [description]
	 */
	public function getDiscountHtml($productUrl)
	{
		JHtml::stylesheet(JUri::root() . 'plugins/discounts/share_for_discounts/assets/css/sharefordiscounts.css', array(), true);

		$content = '<div><div class="center discount-content-wrapper">'
					. JText::_($this->params->get('box_text')) . '</div>'
					. '<br><br><div class="center"><a class="btn btn-info" onclick=sendFBRequest("' . $productUrl . '")>FB Share</a></div><hr>'
					. '<div class="discount-text-wrapper">'
					. '<div class="code-coupon-wrapper code-blur-wrapper" id="tj-sd-coupon">' . JText::_("PLG_SHARE_FOR_DISCOUNTS_SHARE_MSG") . '</div>'
					. '</div></div>';

		echo $this->get_facebook_sdk_js();
		ob_start();
		?>
		<script>
			techjoomla.jQuery(function () {
					jQuery('#share_discount_bt').popover({
					placement : 'top',
					html : true,
					title : '<div class="discount-box-title-wrapper"><?php echo JText::_($this->params->get('box_title'));?></div>',
					content : '<?php echo $content;?>'
				});
			});
		</script>
		<?php
		$script = ob_get_contents();
		ob_end_clean();

		echo $script;

		$buttonText = JText::_($this->params->get('button_text'));
		$buttonHtml = '<div class="' . $this->params->get('parent_css_class')
		. '"><button id="share_discount_bt" rel="popover"  class="btn '
		. $this->params->get('button_css_class')
		. '" type="button">' . JText::_($buttonText)
		. '</button></div>';

		return $buttonHtml;
	}
}

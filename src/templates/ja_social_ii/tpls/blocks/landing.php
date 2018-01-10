<?php
/*
 * ------------------------------------------------------------------------
 * JA Social II template
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
*/

defined('_JEXEC') or die;
$app = JFactory::getApplication();
$template = $app->getTemplate();
$bodybackground = $this->params->get('bodybackground', 'default');
$backgroundimage = $bodybackground == 'image' ? $this->params->get('bodybackgroundimage', T3Path::getUrl('images/bg/bg-1.jpg', '', true)) : '';
?>
<?php if($bodybackground=='image') { ?>
<div id="features-bg" class="features-bg" style="background: url('<?php echo $backgroundimage; ?>') no-repeat; background-size: cover; background-attachment: fixed;">
  <div class="mask"></div>
</div>
<?php } ?>
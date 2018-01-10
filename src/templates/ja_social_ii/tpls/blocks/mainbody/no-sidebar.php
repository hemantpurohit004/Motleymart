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

/**
 * Mainbody 1 columns, content only
 */
?>

<div id="t3-mainbody" class="container t3-mainbody no-sidebar">
	<div class="row">

		<!-- MAIN CONTENT -->
		<div id="t3-content" class="t3-content col-xs-12 col-sm-12 col-md-12">
			<?php if ($this->countModules('content-mass-top')) : ?>
				<!-- Content Mass Top -->
				<div class="content-mass-top <?php $this->_c('content-mass-top') ?>">
					<jdoc:include type="modules" name="<?php $this->_p('content-mass-top') ?>" style="T3Section" />
				</div>
				<!-- //Content Mass Top -->
			<?php endif ?>
			<?php if($this->hasMessage()) : ?>
			<jdoc:include type="message" />
			<?php endif ?>
			<jdoc:include type="component" />
		</div>
		<!-- //MAIN CONTENT -->

	</div>
</div> 
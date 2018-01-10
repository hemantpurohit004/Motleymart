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
?>

<!-- MAIN NAVIGATION -->
<nav id="t3-mainnav" class="wrap navbar navbar-default t3-mainnav">
  <div class="container">
    <!-- Brand and toggle get grouped for better mobile display -->

    <?php if ($this->getParam('navigation_collapse_enable')) : ?>
      <div class="t3-navbar-collapse navbar-collapse collapse"></div>
    <?php endif ?>

    <div class="t3-navbar navbar-collapse collapse">
      <jdoc:include type="<?php echo $this->getParam('navigation_type', 'megamenu') ?>" name="<?php echo $this->getParam('mm_type', 'mainmenu') ?>" />
    </div>
  </div>
</nav>
<!-- //MAIN NAVIGATION -->
--
-- Structure for table `#__kart_weights`
--
CREATE TABLE IF NOT EXISTS `#__kart_weights` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `unit` varchar(4) NOT NULL,
  `value` decimal(15,8) NOT NULL,
  `state` tinyint(1) NOT NULL,
  `store_id` int(10) NOT NULL,
  `ordering` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `checked_out` int(11) NOT NULL,
  `checked_out_time` datetime NOT NULL,
  `id_default` tinyint(2) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `#__kart_weights`
--

INSERT IGNORE INTO `#__kart_weights` (`id`, `title`, `unit`, `value`, `state`, `store_id`, `ordering`, `created_by`, `checked_out`, `checked_out_time`,`id_default`) VALUES
(1, 'Kilogram', 'kg', '1.00000000', 1, 0, 0, 0, 0, '0000-00-00 00:00:00',1),
(2, 'Gram', 'g', '1000.00000000', 1, 0, 0, 0, 0, '0000-00-00 00:00:00',0),
(3, 'Ounce', 'oz', '35.27400000', 1, 0, 0, 0, 0, '0000-00-00 00:00:00',0);

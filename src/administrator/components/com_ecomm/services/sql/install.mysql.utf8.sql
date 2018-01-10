DROP TABLE IF EXISTS `#__picker_drivers`;
DROP TABLE IF EXISTS `#__picker_history`;
DROP TABLE IF EXISTS `#__picker_stops`;
DROP TABLE IF EXISTS `#__picker_users`;
DROP TABLE IF EXISTS `#__picker_vehicles`;

CREATE TABLE IF NOT EXISTS `#__picker_drivers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(1000) NOT NULL,
  `address` varchar(1000) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `date_of_birth` date NOT NULL,
  `registration_date` date NOT NULL,
  `mobile_no` varchar(10) NOT NULL,
  `profile_img` varchar(1000) NOT NULL,
  `identification_imgs` varchar(1000) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mobile_no` (`mobile_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `#__picker_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `#__picker_stops` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(1000) NOT NULL,
  `address` varchar(1000) NOT NULL,
  `map_coordinates` varchar(1000) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `#__picker_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mobile_no` varchar(10) NOT NULL,
  `login_count` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mobile_no` (`mobile_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `#__picker_vehicles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `model` varchar(100) NOT NULL,
  `type` varchar(100) NOT NULL,
  `no_of_wheels` int(11) NOT NULL,
  `no_of_seats` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;




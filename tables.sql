CREATE TABLE `login_attempts` (
  `ip` varchar(15) NOT NULL,
  `date` int(10) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `login_blocks` (
  `ip` varchar(15) NOT NULL,
  `expire` int(10) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `login_log` (
  `ip` varchar(15) NOT NULL,
  `date` int(10) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

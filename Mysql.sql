CREATE TABLE `typecho_talk` (
  `talk_id` int(10) unsigned NOT NULL auto_increment COMMENT 'Talk表主键',
  `talk_created` varchar(200) default NULL COMMENT '发布时间',
  `talk_text` varchar(2000) default NULL COMMENT '说说内容',
  `sort` varchar(200) default NULL COMMENT '媒体分类',
  `talk_media` varchar(1000) default NULL COMMENT '插入链接',
  `order` int(10) unsigned default '0' COMMENT 'Talk排序',
  PRIMARY KEY  (`talk_id`)
) ENGINE=MYISAM  DEFAULT CHARSET=%charset%;

<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * 说说插件
 *
 * @package Talk
 * @author 南玖
 * @version 1.0.0
 * @link https://ztongyang.cn
 * 
 * 由寒泥的Links插件魔改而来
 * 呸呸呸
 * 太不要脸了
 * 嗯要悄悄的，打枪的不要
 */
class Talk_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        $info = Talk_Plugin::TalkInstall();
		Helper::addPanel(3, 'Talk/manage-talk.php', '说说', '说说管理', 'administrator');
		Helper::addAction('talk-edit', 'Talk_Action');
		return _t($info);
       
    }
    
    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate(){
    	Helper::removeAction('talk-edit');
    	Helper::removePanel(3, 'Talk/manage-talk.php');
    	
    }
    
    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form){}
    
    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}
    
    /**
     * 插件实现方法
     * 
     * @access public
     * @return void
     */
    
    public static function TalkInstall()
	{
		$installDb = Typecho_Db::get();
		$type = explode('_', $installDb->getAdapterName());
		$type = array_pop($type);
		$prefix = $installDb->getPrefix();
		$scripts = file_get_contents('usr/plugins/Talk/'.$type.'.sql');
		$scripts = str_replace('typecho_', $prefix, $scripts);
		$scripts = str_replace('%charset%', 'utf8', $scripts);
		$scripts = explode(';', $scripts);
		try {
			foreach ($scripts as $script) {
				$script = trim($script);
				if ($script) {
					$installDb->query($script, Typecho_Db::WRITE);
				}
			}
            return '建立说说数据表,插件启用成功！';
        } catch (Typecho_Db_Exception $e) {
            $code = $e->getCode();
            if(('Mysql' == $type && 1050 == $code) || ('Mysql' == $type && '42S01' == $code) ||
                ('SQLite' == $type && ('HY000' == $code || 1 == $code))) {
                try {
                    $script = 'SELECT `talk_id`, `talk_created`, `talk_text`, `sort`, `talk_media` ,`order` from `' . $prefix . 'talk`';
                    $installDb->query($script, Typecho_Db::READ);
                    return '检测说说数据表,说说插件启用成功！';
                } catch (Typecho_Db_Exception $e) {
                    $code = $e->getCode();
                    if(('Mysql' == $type && 1054 == $code) ||
							('SQLite' == $type && ('HY000' == $code || 1 == $code))) {
						return Talk_Plugin::talkUpdate($installDb, $type, $prefix);
					}
                    throw new Typecho_Plugin_Exception('数据表检测失败,说说插件启用失败。错误号：'.$code);
                }
            } else {
                throw new Typecho_Plugin_Exception('数据表建立失败,说说插件启用失败。错误号：'.$code);
            }
        }
		
		
	}
	
    public static function TalkUpdate($installDb, $type, $prefix)
	{
		$scripts = file_get_contents('usr/plugins/Talk/Update_'.$type.'.sql');
		$scripts = str_replace('typecho_', $prefix, $scripts);
		$scripts = str_replace('%charset%', 'utf8', $scripts);
		$scripts = explode(';', $scripts);
		try {
			foreach ($scripts as $script) {
				$script = trim($script);
				if ($script) {
					$installDb->query($script, Typecho_Db::WRITE);
				}
			}
			return '检测到旧版本说说数据表，升级成功';
		} catch (Typecho_Db_Exception $e) {
			$code = $e->getCode();
			if(('Mysql' == $type && 1060 == $code) ) {
				return '说说据表已经存在，插件启用成功';
			}
			throw new Typecho_Plugin_Exception('说说插件启用失败。错误号：'.$code);
		}
	}
    
	public static function form($action = NULL)
	{
		/** 构建表格 */
		$options = Typecho_Widget::widget('Widget_Options');
		$form = new Typecho_Widget_Helper_Form(Typecho_Common::url('/action/talk-edit', $options->index),
		Typecho_Widget_Helper_Form::POST_METHOD);
		
		/** 发布时间 */
		$talk_created = new Typecho_Widget_Helper_Form_Element_Text('talk_created', NULL, date("Y-m-d H:i:s",time()), _t('发布时间*'));
		$form->addInput($talk_created);
		
		/** 说说内容 */
		$talk_text = new Typecho_Widget_Helper_Form_Element_Textarea('talk_text', NULL, NULL, _t('说说内容*'));
		$form->addInput($talk_text);
		
		/** 插入媒体类型 */
		$sort = new Typecho_Widget_Helper_Form_Element_Radio('sort', 
	    array('text' => _t('文字'),
	    'image' => _t('图片'), 
	    'video' => _t('视频')),'text' 
	    ,_t('请选择说说插入媒体类型'),_t('图片支持多张并以回车分隔，视频仅限一个'));
	    $form->addInput($sort);		
		
		/** 插入媒体url */
		$talk_media = new Typecho_Widget_Helper_Form_Element_Textarea('talk_media', NULL, NULL, _t('媒体URL'),  _t('请填写插入的图片(支持多张,以$开头)或视频URL,格式如下:图片:$http://***.jpg，视频:$http://***.mp4(视频链接)$http://***.jpg(封面)'));
		$form->addInput($talk_media);
		

		/** 链接动作 */
		$do = new Typecho_Widget_Helper_Form_Element_Hidden('do');
		$form->addInput($do);
		
		/** 链接主键 */
		$talk_id = new Typecho_Widget_Helper_Form_Element_Hidden('talk_id');
		$form->addInput($talk_id);
		
		/** 提交按钮 */
		$submit = new Typecho_Widget_Helper_Form_Element_Submit();
		$submit->input->setAttribute('class', 'btn primary');
		$form->addItem($submit);
		$request = Typecho_Request::getInstance();

        if (isset($request->talk_id) && 'insert' != $action) {
            /** 更新模式 */
			$db = Typecho_Db::get();
			$prefix = $db->getPrefix();
            $talk = $db->fetchRow($db->select()->from($prefix.'talk')->where('talk_id = ?', $request->talk_id));
            if (!$talk) {
                throw new Typecho_Widget_Exception(_t('说说不存在'), 404);
            }
            
            $talk_created->value($talk['talk_created']);
            $talk_text->value($talk['talk_text']);
            $sort->value($talk['sort']);
            $talk_media->value($talk['talk_media']);
            $do->value('update');
            $talk_id->value($talk['talk_id']);
            $submit->value(_t('编辑说说'));
            $_action = 'update';
        } else {
            $do->value('insert');
            $submit->value(_t('发表说说'));
            $_action = 'insert';
        }
        
        if (empty($action)) {
            $action = $_action;
        }
		
        return $form;
	}
	

	// 输出
	public static function output_talks($talk_num=0)
	{
		$options = Typecho_Widget::widget('Widget_Options');
		if (!isset($options->plugins['activated']['Talk'])) {
			return '说说插件未激活';
		}

		$db = Typecho_Db::get();
		$prefix = $db->getPrefix();
		$options = Typecho_Widget::widget('Widget_Options');
		$sql = $db->select()->from($prefix.'talk');
		$sql = $sql->order($prefix.'talk.order', Typecho_Db::SORT_ASC);
		$talk_num = intval($talk_num);
		if ($talk_num > 0) {
			$sql = $sql->limit($talk_num);
		}
		$talks = $db->fetchAll($sql);
		$talks = $preserve=array_reverse($talks,false);
		return $talks;  
	}
    
}

	
<?php
class Talk_Action extends Typecho_Widget implements Widget_Interface_Do
{
	private $db;
	private $options;
	private $prefix;
			
	public function inserttalk()
	{
		if (Talk_Plugin::form('insert')->validate()) {
			$this->response->goBack();
		}
		/** 取出数据 */
		$talk = $this->request->from('talk_created', 'talk_text', 'sort', 'talk_media');
		$talk['order'] = $this->db->fetchObject($this->db->select(array('MAX(order)' => 'maxOrder'))->from($this->prefix.'talk'))->maxOrder + 1;

		/** 插入数据 */
		$talk['talk_id'] = $this->db->query($this->db->insert($this->prefix.'talk')->rows($talk));

		/** 设置高亮 */
		$this->widget('Widget_Notice')->highlight('talk-'.$talk['talk_id']);

		/** 提示信息 */
		$this->widget('Widget_Notice')->set(_t('说说已发布',
		$talk['talk_created'], $talk['talk_text']), NULL, 'success');

		/** 转向原页 */
		$this->response->redirect(Typecho_Common::url('extending.php?panel=Talk%2Fmanage-talk.php', $this->options->adminUrl));
	}



	public function updateTalk()
	{
		if (Talk_Plugin::form('update')->validate()) {
			$this->response->goBack();
		}

		/** 取出数据 */
		$talk = $this->request->from('talk_id','talk_created', 'talk_text', 'sort', 'talk_media');

		/** 更新数据 */
		$this->db->query($this->db->update($this->prefix.'talk')->rows($talk)->where('talk_id = ?', $talk['talk_id']));

		/** 设置高亮 */
		$this->widget('Widget_Notice')->highlight('talk-'.$talk['talk_id']);

		/** 提示信息 */
		$this->widget('Widget_Notice')->set(_t('说说已经被更新',
		$talk['talk_created'], $talk['talk_text']), NULL, 'success');

		/** 转向原页 */
		$this->response->redirect(Typecho_Common::url('extending.php?panel=Talk%2Fmanage-talk.php', $this->options->adminUrl));
	}

    public function deleteTalk()
    {
        $talk_ids = $this->request->filter('int')->getArray('talk_id');
        $deleteCount = 0;
        if ($talk_ids && is_array($talk_ids)) {
            foreach ($talk_ids as $talk_id) {
                if ($this->db->query($this->db->delete($this->prefix.'talk')->where('talk_id = ?', $talk_id))) {
                    $deleteCount ++;
                }
            }
        }
        /** 提示信息 */
        $this->widget('Widget_Notice')->set($deleteCount > 0 ? _t('说说已经删除') : _t('没有说说被删除'), NULL,
        $deleteCount > 0 ? 'success' : 'notice');
        
        /** 转向原页 */
        $this->response->redirect(Typecho_Common::url('extending.php?panel=Talk%2Fmanage-talk.php', $this->options->adminUrl));
    }
    
    public function sorttalk()
    {
        $talks = $this->request->filter('int')->getArray('talk_id');
        if ($talks && is_array($talks)) {
			foreach ($talkks as $sort => $talk_id) {
				$this->db->query($this->db->update($this->prefix.'talk')->rows(array('order' => $sort + 1))->where('talk_id = ?', $talk_id));
			}
        }
    }



	public function action()
	{
		$user = Typecho_Widget::widget('Widget_User');
		$user->pass('administrator');
		$this->db = Typecho_Db::get();
		$this->prefix = $this->db->getPrefix();
		$this->options = Typecho_Widget::widget('Widget_Options');
		$this->on($this->request->is('do=insert'))->insertTalk();
		$this->on($this->request->is('do=update'))->updateTalk();
		$this->on($this->request->is('do=delete'))->deleteTalk();
		$this->on($this->request->is('do=sort'))->sorttalk();
		$this->response->redirect($this->options->adminUrl);
	}
}

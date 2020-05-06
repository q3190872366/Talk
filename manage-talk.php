<?php
include 'common.php';
include 'header.php';
include 'menu.php';
?>

<style>
@media screen and (max-width:767px){#talk-pc{display:none;}}@media screen and (min-width:768px){#talk-mobile{display:none;}}.typecho-page-main .typecho-option textarea{height:auto;}td{max-width:100px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}.pageInit{padding:5px 10px;text-decoration:none;color:#ffffff;background:#777;margin-left:5px;}.pageSelected{padding:5px 10px;text-decoration:none;background:#6776ef;margin-left:5px;}
</style>
<div class="main">
    <div class="body container">
        <?php include 'page-title.php'; ?>
        <div class="row typecho-page-main manage-metas">
                <div class="col-mb-12">
                    <ul class="typecho-option-tabs clearfix">
                        <li class="current"><a href="<?php $options->adminUrl('extending.php?panel=Talk%2Fmanage-talk.php'); ?>"><?php _e('新建说说'); ?></a></li>
                    </ul>
                </div>
				
				<div id="talk-mobile" class="col-mb-12 col-tb-4" role="form">
                    <?php Talk_Plugin::form()->render(); ?>
                </div>
				
                <div class="col-mb-12 col-tb-8" role="main">                  
                    <?php
						$prefix = $db->getPrefix();
						$talks = $db->fetchAll($db->select()->from($prefix.'talk')->order($prefix.'talk.order', Typecho_Db::SORT_ASC));
                    ?>
                    <form method="post" name="manage_categories" class="operate-form">
                    <div class="typecho-list-operate clearfix">
                        <div class="operate">
                            <label><i class="sr-only"><?php _e('全选'); ?></i><input type="checkbox" class="typecho-table-select-all" /></label>
                            <div class="btn-group btn-drop">
                                <button class="btn dropdown-toggle btn-s" type="button"><i class="sr-only"><?php _e('操作'); ?></i><?php _e('选中项'); ?> <i class="i-caret-down"></i></button>
                                <ul class="dropdown-menu">
                                    <li><a lang="<?php _e('你确认要删除这些说说吗?'); ?>" href="<?php $options->index('/action/talk-edit?do=delete'); ?>"><?php _e('删除'); ?></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="typecho-table-wrap">
                        <table id="pagingTable" class="typecho-list-table">
                            <colgroup>
                                <col width="5"/>
								<col width="22%"/>
								<col width=""/>
								<col width="15%"/>
                            </colgroup>
                            <thead>
                                <tr>
                                    <th> </th>
									<th><?php _e('时间'); ?></th>
									<th><?php _e('内容'); ?></th>
									<th><?php _e('类型'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
								<?php if(!empty($talks)): $alt = 0;?>
								<?php $talks = $preserve=array_reverse($talks,false); ?>
								<?php foreach ($talks as $talk): ?>
                                <tr id="talk-id-<?php echo $talk['talk_id']; ?>">
                                    <td><input type="checkbox" value="<?php echo $talk['talk_id']; ?>" name="talk_id[]"/></td>
									<td><?php echo $talk['talk_created']; ?></td>
									<td><a href="<?php echo $request->makeUriByRequest('talk_id=' . $talk['talk_id']); ?>" title="点击编辑该条说说"><?php echo $talk['talk_text']; ?></a></td>
									<td><?php echo $talk['sort']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="5"><h6 class="typecho-list-table-title"><?php _e('尚未发表说说'); ?></h6></td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    </form>
				</div>
			
                       
            <div id="talk-pc" class="col-mb-12 col-tb-4" role="form">
                    <?php Talk_Plugin::form()->render(); ?>
            </div>
        </div>
    </div>
</div>

<?php
include 'copyright.php';
include 'common-js.php';
?>
<script type="text/javascript">
$(function(){simplePaging($('#pagingTable'),15)});function simplePaging(table,pageSize){var $selectedPage;var $table=$(table);var currentPage=0;var sumRows=$table.find('tbody tr').length;var sumPages=Math.ceil(sumRows/pageSize);$table.next("div[tablePagingDiv]").remove();if(sumPages>1){$table.bind('paging',function(){$table.find('tbody tr').hide().slice(currentPage*pageSize,(currentPage+1)*pageSize).show()});var $pager=$('<div tablePagingDiv="" style="height:40px;line-height:40px;">页码</div>');for(var pageIndex=0;pageIndex<sumPages;pageIndex++){$('<a href="#"><span>'+(pageIndex+1)+'</span></a>').bind('click',{'newPage':pageIndex},function(event){currentPage=event.data['newPage'];$selectedPage=$(this);$(this).addClass('pageSelected').siblings().removeClass('pageSelected');$table.trigger('paging')}).appendTo($pager);$pager.append(" ")}$('a',$pager).addClass('pageInit');$('a:first',$pager).addClass('pageSelected');$('a',$pager).hover(function(){$(this).addClass('pageSelected')},function(){$(this).removeClass('pageSelected');if($selectedPage==null){$('a:first',$pager).addClass('pageSelected')}else{$selectedPage.addClass('pageSelected')}});$pager.insertAfter($table);$table.trigger('paging')}}(function(){$(document).ready(function(){var table=$('.typecho-list-table').tableDnD({onDrop:function(){var ids=[];$('input[type=checkbox]',table).each(function(){ids.push($(this).val())});$.post('<?php $options->index('/action/talk-edit?do=sort'); ?>',$.param({talk_id:ids}));$('tr',table).each(function(i){if(i%2){$(this).addClass('even')}else{$(this).removeClass('even')}})}});table.tableSelectable({checkEl:'input[type=checkbox]',rowEl:'tr',selectAllEl:'.typecho-table-select-all',actionEl:'.dropdown-menu a'});$('.btn-drop').dropdownMenu({btnEl:'.dropdown-toggle',menuEl:'.dropdown-menu'});$('.dropdown-menu button.merge').click(function(){var btn=$(this);btn.parents('form').attr('action',btn.attr('rel')).submit()});<?php if(isset($request->talk_id)):?>$('.typecho-mini-panel').effect('highlight','#AACB36');<?php endif;?>})})();$("[name='talk_text']").attr("rows","8");$("[name='talk_media']").attr("rows","5");
</script>

<?php include 'footer.php'; ?>

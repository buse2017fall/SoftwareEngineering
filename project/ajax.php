<?php

set_error_handler('myErrorHandler');
set_exception_handler('myExceptionHandler');

require_once('./init.php');

$db = DBConnection::instance();

if(isset($_GET['loadLists']))
{
	if($needAuth && !is_logged()) $sqlWhere = 'WHERE published=1';
	else $sqlWhere = '';
	$t = array();
	$t['total'] = 0;
	$q = $db->dq("SELECT * FROM {$db->prefix}lists $sqlWhere ORDER BY ow ASC, id ASC");
	while($r = $q->fetch_assoc($q))
	{
		$t['total']++;
		$t['list'][] = prepareList($r);
	}
	jsonExit($t);
}
elseif(isset($_GET['deleteTask']))
{
	$id = (int)_post('id');
	$deleted = deleteTask($id);
	$t = array();
	$t['total'] = $deleted;
	$t['list'][] = array('id'=>$id);
	jsonExit($t);
}
elseif(isset($_GET['completeTask']))
{
	check_write_access();
	$id = (int)_post('id');
	$compl = _post('compl') ? 1 : 0;
	$listId = (int)$db->sq("SELECT list_id FROM {$db->prefix}todolist WHERE id=$id");
	if($compl) 	$ow = 1 + (int)$db->sq("SELECT MAX(ow) FROM {$db->prefix}todolist WHERE list_id=$listId AND compl=1");
	else $ow = 1 + (int)$db->sq("SELECT MAX(ow) FROM {$db->prefix}todolist WHERE list_id=$listId AND compl=0");
	$dateCompleted = $compl ? time() : 0;
	$db->dq("UPDATE {$db->prefix}todolist SET compl=$compl,ow=$ow,d_completed=?,d_edited=? WHERE id=$id",
				array($dateCompleted, time()) );
	$t = array();
	$t['total'] = 1;
	$r = $db->sqa("SELECT * FROM {$db->prefix}todolist WHERE id=$id");
	$t['list'][] = prepareTaskRow($r);
	jsonExit($t);
}
elseif(isset($_GET['addList']))
{
	check_write_access();
	stop_gpc($_POST);
	$t = array();
	$t['total'] = 0;
	$name = str_replace(array('"',"'",'<','>','&'),array('','','','',''),trim(_post('name')));
	$ow = 1 + (int)$db->sq("SELECT MAX(ow) FROM {$db->prefix}lists");
	$db->dq("INSERT INTO {$db->prefix}lists (uuid,name,ow,d_created,d_edited) VALUES (?,?,?,?,?)",
				array(generateUUID(), $name, $ow, time(), time()) );
	$id = $db->last_insert_id();
	$t['total'] = 1;
	$r = $db->sqa("SELECT * FROM {$db->prefix}lists WHERE id=$id");
	$t['list'][] = prepareList($r);
	jsonExit($t);
}
elseif(isset($_GET['renameList']))
{
	check_write_access();
	stop_gpc($_POST);
	$t = array();
	$t['total'] = 0;
	$id = (int)_post('list');
	$name = str_replace(array('"',"'",'<','>','&'),array('','','','',''),trim(_post('name')));
	$db->dq("UPDATE {$db->prefix}lists SET name=?,d_edited=? WHERE id=$id", array($name, time()) );
	$t['total'] = $db->affected();
	$r = $db->sqa("SELECT * FROM {$db->prefix}lists WHERE id=$id");
	$t['list'][] = prepareList($r);
	jsonExit($t);
}
elseif(isset($_GET['deleteList']))
{
	check_write_access();
	stop_gpc($_POST);
	$t = array();
	$t['total'] = 0;
	$id = (int)_post('list');
	$db->ex("BEGIN");
	$db->ex("DELETE FROM {$db->prefix}lists WHERE id=$id");
	$t['total'] = $db->affected();
	if($t['total']) {
		$db->ex("DELETE FROM {$db->prefix}tag2task WHERE list_id=$id");
		$db->ex("DELETE FROM {$db->prefix}todolist WHERE list_id=$id");
	}
	$db->ex("COMMIT");
	jsonExit($t);
}


###################################################################################################

function prepareTaskRow($r)
{
	$lang = Lang::instance();
	$dueA = prepare_duedate($r['duedate']);
	$formatCreatedInline = $formatCompletedInline = Config::get('dateformatshort');
	if(date('Y') != date('Y',$r['d_created'])) $formatCreatedInline = Config::get('dateformat2');
	if($r['d_completed'] && date('Y') != date('Y',$r['d_completed'])) $formatCompletedInline = Config::get('dateformat2');

	$dCreated = timestampToDatetime($r['d_created']);
	$dCompleted = $r['d_completed'] ? timestampToDatetime($r['d_completed']) : '';

	return array(
		'id' => $r['id'],
		'title' => escapeTags($r['title']),
		'listId' => $r['list_id'],
		'date' => htmlarray($dCreated),
		'dateInt' => (int)$r['d_created'],
		'dateInline' => htmlarray(formatTime($formatCreatedInline, $r['d_created'])),
		'dateInlineTitle' => htmlarray(sprintf($lang->get('taskdate_inline_created'), $dCreated)),
		'dateEditedInt' => (int)$r['d_edited'],
		'dateCompleted' => htmlarray($dCompleted),
		'dateCompletedInline' => $r['d_completed'] ? htmlarray(formatTime($formatCompletedInline, $r['d_completed'])) : '',
		'dateCompletedInlineTitle' => htmlarray(sprintf($lang->get('taskdate_inline_completed'), $dCompleted)),
		'compl' => (int)$r['compl'],
		'prio' => $r['prio'],
		'note' => nl2br(escapeTags($r['note'])),
		'noteText' => (string)$r['note'],
		'ow' => (int)$r['ow'],
		'tags' => htmlarray($r['tags']),
		'tags_ids' => htmlarray($r['tags_ids']),
		'duedate' => $dueA['formatted'],
		'dueClass' => $dueA['class'],
		'dueStr' => htmlarray($r['compl'] && $dueA['timestamp'] ? formatTime($formatCompletedInline, $dueA['timestamp']) : $dueA['str']),
		'dueInt' => date2int($r['duedate']),
		'dueTitle' => htmlarray(sprintf($lang->get('taskdate_inline_duedate'), $dueA['formatted'])),
	);
}


function check_write_access($listId = null)
{
	if(have_write_access($listId)) return;
	jsonExit( array('total'=>0, 'list'=>array(), 'denied'=>1) );
}


function prepareTags($tagsStr)
{
	$tags = explode(',', $tagsStr);
	if(!$tags) return 0;

	$aTags = array('tags'=>array(), 'ids'=>array());
	foreach($tags as $tag)
	{
		$tag = str_replace(array('"',"'",'<','>','&','/','\\','^'),'',trim($tag));
		if($tag == '') continue;

		$aTag = getOrCreateTag($tag);
		if($aTag && !in_array($aTag['id'], $aTags['ids'])) {
			$aTags['tags'][] = $aTag['name'];
			$aTags['ids'][] = $aTag['id'];
		}
	}
	return $aTags;
}

function getOrCreateTag($name)
{
	$db = DBConnection::instance();
	$tagId = $db->sq("SELECT id FROM {$db->prefix}tags WHERE name=?", array($name));
	if($tagId) return array('id'=>$tagId, 'name'=>$name);

	$db->ex("INSERT INTO {$db->prefix}tags (name) VALUES (?)", array($name));
	return array('id'=>$db->last_insert_id(), 'name'=>$name);
}

function getTagId($tag)
{
	$db = DBConnection::instance();
	$id = $db->sq("SELECT id FROM {$db->prefix}tags WHERE name=?", array($tag));
	return $id ? $id : 0;
}



function addTaskTags($taskId, $tagIds, $listId)
{
	$db = DBConnection::instance();
	if(!$tagIds) return;
	foreach($tagIds as $tagId)
	{
		$db->ex("INSERT INTO {$db->prefix}tag2task (task_id,tag_id,list_id) VALUES (?,?,?)", array($taskId,$tagId,$listId));
	}
}

function deleteTask($id)
{
	check_write_access();
	$db = DBConnection::instance();
	$db->ex("BEGIN");
	$db->ex("DELETE FROM {$db->prefix}tag2task WHERE task_id=$id");
	//TODO: delete unused tags?
	$db->dq("DELETE FROM {$db->prefix}todolist WHERE id=$id");
	$affected = $db->affected();
	$db->ex("COMMIT");
	return $affected;
}

function moveTask($id, $listId)
{
	check_write_access();
	$db = DBConnection::instance();

	// Check task exists and not in target list
	$r = $db->sqa("SELECT * FROM {$db->prefix}todolist WHERE id=?", array($id));
	if(!$r || $listId == $r['list_id']) return false;

	// Check target list exists
	if(!$db->sq("SELECT COUNT(*) FROM {$db->prefix}lists WHERE id=?", $listId))
		return false;

	$ow = 1 + (int)$db->sq("SELECT MAX(ow) FROM {$db->prefix}todolist WHERE list_id=? AND compl=?", array($listId, $r['compl']?1:0));
	
	$db->ex("BEGIN");
	$db->ex("UPDATE {$db->prefix}tag2task SET list_id=? WHERE task_id=?", array($listId, $id));
	$db->dq("UPDATE {$db->prefix}todolist SET list_id=?, ow=?, d_edited=? WHERE id=?", array($listId, $ow, time(), $id));
	$db->ex("COMMIT");
	return true;
}

function prepareList($row)
{
	$taskview = (int)$row['taskview'];
	return array(
		'id' => $row['id'],
		'name' => htmlarray($row['name']),
		'sort' => (int)$row['sorting'],
		'published' => $row['published'] ? 1 :0,
		'showCompl' => $taskview & 1 ? 1 : 0,
		'showNotes' => $taskview & 2 ? 1 : 0,
		'hidden' => $taskview & 4 ? 1 : 0,
	);
}

function getUserListsSimple()
{
	$db = DBConnection::instance();
	$a = array();
	$q = $db->dq("SELECT id,name FROM {$db->prefix}lists ORDER BY id ASC");
	while($r = $q->fetch_row()) {
		$a[$r[0]] = $r[1];
	}
	return $a;
}

?>

<?php
namespace Bednarik\Cooluri\Manager;

/**
	This file is part of CoolUri.

    CoolUri is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    CoolUri is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with CoolUri. If not, see <http://www.gnu.org/licenses/>.
*/

class Main {

  private $db;
  private $file;

  private $enable;
  private $table = 'link_';
  private $lt;

  public function __construct($file = false, $conf = false, $res = '') {
    $this->db = \Bednarik\Cooluri\Core\DB::getInstance();
    $this->file = $file;
    $this->resPath = $res;

    if ($conf !== false) {
        $this->lt = \Bednarik\Cooluri\Core\Translate::getInstance($conf);
        $this->enable = (!empty(\Bednarik\Cooluri\Core\Translate::$conf->cache) && !empty(\Bednarik\Cooluri\Core\Translate::$conf->cache->usecache) && \Bednarik\Cooluri\Core\Translate::$conf->cache->usecache==1);
        if (!empty(\Bednarik\Cooluri\Core\Translate::$conf->cache->tablesprefix)) {
            $this->table = \Bednarik\Cooluri\Core\Translate::$conf->cache->tablesprefix;
        }
    } else {
        $this->enable = false;
    }
  }

  public function main() {

    if (!$this->enable) return $this->noCache();

    $content = '<div class="container-fluid-off">';

//    if (empty($_GET['mod'])) {
//      $content .= $this->welcome();
//    } else {
      switch ($_GET['mod']) {
        case 'cache': $content .= $this->cache(); break;
        case 'old': $content .= $this->old(); break;
        case 'link': $content .= $this->link(); break;
        case 'update': $content .= $this->update(); break;
        case 'delete': $content .= $this->delete(); break;
        case 'all': $content .= $this->all(); break;
        case 'sticky': $content .= $this->sticky(); break;
        case 'redirect': $content .= $this->redirect(); break;
        default: $content .= $this->cache();
      }
//    }

    $content .= '</div>';

    return $content;
  }

  public function all() {
    $c = '';
    if (!empty($_POST)) {
      if (isset($_POST['refresh'])) {
        $this->db->query('UPDATE '.$this->table.'cache SET tstamp=0');
      } elseif (isset($_POST['delete'])) {
         if (isset($_POST['sticky']) && $_POST['sticky'] == 1) {
             $this->db->query('TRUNCATE '.$this->table.'cache');
             $this->db->query('TRUNCATE '.$this->table.'oldlinks');
         } else {
            $this->db->query('DELETE FROM '.$this->table.'cache WHERE sticky != 1');
            $this->db->query('DELETE FROM '.$this->table.'oldlinks WHERE sticky != 1');
         }
      }
      $c .= '<div class="text-center"><p class="text-success bg-success" style="padding: 10px;">Done.</p></div>';
    }
    $c .= '
    <div class="row">
       <div class="col-md-6">
        <div class="panel panel-default">
          <div class="panel-heading">Force all links to update upon next hit</div>
          <div class="panel-body">
            <p>Upon next page hit, all links will be regenerated and if changed, any old link will be moved to "oldlinks".</p>
              <form method="post" action="'.$this->file.'mod=all">
                <input type="submit" name="refresh" value="FORCE UPDATE OF ALL LINKS" class="btn btn-warning">
              </form>
          </div>
        </div>

        <div class="panel panel-default">
          <div class="panel-heading">Start again</div>
          <div class="panel-body">
            <p>Delete everything - cache and oldlinks.</p>
          <form method="post" action="'.$this->file.'mod=all">
          <div class="form-group">
            <label>
              <input type="checkbox" name="sticky" value="1"> Delete sticky too
            </label>
            </div>
            <input type="submit" name="delete" value="DELETE EVERYTHING AND START AGAIN" class="btn btn-danger">
          </form>
          </div>
        </div>
    </div></div>
    ';
    return $c;
  }

  private function serializedArrayToQueryString($ar) {
    $ar = \Bednarik\Cooluri\Core\Functions::cache2params($ar);
    $pars = Array();
    foreach ($ar as $k=>$v) {
      $pars[] = $k.'='.$v;
    }
    return implode('&amp;',$pars);
  }

  public function delete() {
    if (empty($_GET['lid'])) {
        $id = 0;
    }
    else {
        $id = (int)$_GET['lid'];
    }

    if (isset($_GET['old'])) {
        $old = true;
    }
    else {
        $old = false;
    }

    if (!empty($id)) {
      $q = $this->db->query('DELETE FROM '.$this->table.($old?'oldlinks':'cache').' WHERE id='.$id.' LIMIT 1');
      if (!$q || $this->db->affected_rows()==0) {
          $c = '<div class="error"><p>The link hasn\'t been deleted because it doesn\'t exist (or maybe a DB error).</p></div>';
      } else {
        $c = '<div class="succes"><p>The link has been deleted.</p></div>';
      }
    }
    $c .= $this->getBackLink();
    return $c;
  }

  public function sticky() {
    if (empty($_GET['lid'])) $id = 0;
    else $id = (int)$_GET['lid'];

    if (isset($_GET['old'])) {
        $old = true;
    }
    else {
        $old = false;
    }

    if (!empty($id)) {
      $q = $this->db->query('UPDATE '.$this->table.($old?'oldlinks':'cache').' SET sticky=not(sticky) WHERE id='.$id.' LIMIT 1');
      if (!$q || $this->db->affected_rows()==0) {
          $c = '<div class="error"><p>The sticky value hasn\'t been changed because the link doesn\'t exist (or maybe a DB error).</p></div>';
      } else {
        $c = '<div class="bg-success text-center"><p class="text-success" style="padding:15px;">The sticky value has been changed.</p></div>';
      }
    }
    $c .= $this->getBackLink();
    return $c;
  }

  public function update() {
    $c = '';
    if (empty($_GET['lid'])) $id = 0;
    else $id = (int)$_GET['lid'];

    if (!empty($id)) {
      $q = $this->db->query('SELECT * FROM '.$this->table.'cache WHERE id='.$id);
      $oldlink = $this->db->fetch($q);
      if ($oldlink) {

        $params = \Bednarik\Cooluri\Core\Functions::cache2params($oldlink['params']);
        $this->lt->params2cool($params,'',true,false,true);

        // multidomain option - it's a Typo3 hack, but what the heck
        // it may be used even without Typo3
        $md = explode('@',$oldlink['url']);
        if (count($md)>1) {
          // now it's required to add the prefix back
          // domain change won't be supported
          $q = $this->db->query('UPDATE '.$this->table.'cache set url=CONCAT(\''.$md[0].'\',\'@\',url) WHERE id='.$id);
        }

        $q = $this->db->query('SELECT * FROM '.$this->table.'cache WHERE id='.$id);
        $newlink = $this->db->fetch($q);

        if ($newlink['url']==$oldlink['url']) {
          $c .= '<div class="error"><p>The link hasn\'t been changed.</p></div>';
        } else {
          $c .= '<div class="succes"><p>The link has been updated from '.$oldlink['url'].' to '.$newlink['url'].'.</p></div>';
        }

      } else {
        $c .= '<div class="error"><p>Link with this ID is not in the cache.</p></div>';
      }
    }

    $c .= $this->getBackLink();
    return $c;
  }

  private function getBackLink() {
    if (!empty($_GET['from'])) $from = explode(':',$_GET['from']);
    return '<p><a class="btn btn-primary" href="'.$this->file.(empty($from[0])?'':'mod='.$from[0].(empty($from[1])?'':'&amp;l='.$from[1])).'">&lt;&lt; Back</a></p>';
  }

  public function cache() {

    if (empty($_REQUEST['l'])) {
      $let = '';
    } else {
      $let = $_REQUEST['l'];
    }

    $c = '<h1>Cached links</h1>';

    $c .= '<p>';
    $c .= '<a class="badge badge-bprimary" href="'.$this->file.'mod=cache&amp;l='.urlencode('%').'">All</a>
    ';
    for ($i=ord('A');$i<=ord('Z');$i++) {
      $c .= '<a class="badge" href="'.$this->file.'mod=cache&amp;l='.strtoupper(chr($i)).'">'.strtoupper(chr($i)).'</a>
      ';
    }
    $c .= '</p> <hr>';
    $c .= '<div class="row">
        <div class="col-md-6">
        <form method="post" action="'.$this->file.'">
      <label>Link starts with:</label>
      <div class="form-group">
          <div class="input-group">
            <input type="text" name="l" class="a form-control" value="'.htmlspecialchars($let).'">
            <input type="hidden" name="mod" value="cache">
            <span class="input-group-btn">
                <input type="submit" value="Search" class="submit btn btn-primary">
            </span>
          </div>
      </div>
      <div class="form-group">
        <label><input type="checkbox" name="domain" value="1" '.(!empty($_REQUEST['domain'])?' checked="checked"':'').'> Ignore domain</label>
      </div>
    </form></div></div>';

    if (!empty($let)) {

    	if (!empty($_REQUEST['domain'])) {
    		$let = str_replace('%','.*',$let);
    		$q = $this->db->query('SELECT * FROM '.$this->table.'cache WHERE LOWER(url) RLIKE '.$this->db->escape('^[^@]*@?'.strtolower($let).'.*').' ORDER BY url');
    	} else {
    		$q = $this->db->query('SELECT * FROM '.$this->table.'cache WHERE LOWER(url) LIKE '.$this->db->escape(strtolower($let).'%').' ORDER BY url');
    	}
	    $num = $this->db->num_rows($q);
	    if ($num>0) {
	      $c .= '<p class="center text-info">Records found: '.$num.'</p>';
	      $c .= '<form method="post" action="'.$this->file.'mod=cache">';
	      $c .= '<table id="list" class="table table-striped" style="table-layout: fixed;"><thead><tr><th class="left" style="width: 30%;">Cached URI</th><th style="width: 30%;">Parameters</th><th style="width: 10%;">Cached</th><th style="width: 10%;">Last check</th><th style="width: 5%;">Sticky</th><th style="width: 20%;">Action</th></tr></thead>';
	      while ($row = $this->db->fetch($q)) {
	        $c .= '<tr>
	          <td class="left" style="word-wrap: break-word;">'.htmlspecialchars($row['url']).'</td>
	          <td style="word-wrap: break-word;">'.htmlspecialchars($this->serializedArrayToQueryString($row['params'])).'</td>
	          <td>'.$row['crdatetime'].'</td>
	          <td>'.$row['tstamp'].'</td>
	          <td>'.($row['sticky']?'YES':'NO').'</td>
	          <td class="nowrap">
	            <div class="btn-group">
	              <a href="'.$this->file.'mod=link&amp;lid='.$row['id'].'" class="btn btn-default"><img src="'.$this->resPath.'img/button_edit.gif" alt="Edit" title="Edit"></a>
	              <a href="'.$this->file.'mod=update&amp;lid='.$row['id'].'&amp;from=cache:'.$let.'" class="btn btn-default"><img src="'.$this->resPath.'img/button_refresh.gif" alt="Update" title="Update"></a>
	              <a href="'.$this->file.'mod=delete&amp;lid='.$row['id'].'&amp;from=cache:'.$let.'" class="btn btn-default" onclick="return confirm(\'Are you sure?\');"><img src="'.$this->resPath.'img/button_garbage.gif" alt="Delete" title="Delete"></a>
	              <a href="'.$this->file.'mod=sticky&amp;lid='.$row['id'].'&amp;from=cache:'.$let.'" class="btn btn-default"><img src="'.$this->resPath.'img/button_sticky.gif" alt="Sticky on/off" title="Sticky on/off"></a>
	            </div>
	          </td>
	        </tr>';
	      }
	      $c .= '</table></form>';
	    } else {
	      $c .= '<p class="text-info">No cached links found.</p>';
	    }

    } else {
    	$c .= '<p class="text-muted">Input any filter. Use "%" to get all links.</p>';
    }
    return $c;
  }

  public function old() {
    $let = $_REQUEST['l'];

    $c = '<h1>Old links</h1>';

    $c .= '<p>';
    $c .= '<a class="badge badge-bprimary" href="'.$this->file.'mod=old&amp;l=%">all</a>
    ';
    for ($i=ord('A');$i<=ord('Z');$i++) {
      $c .= '<a class="badge" href="'.$this->file.'mod=old&amp;l='.strtoupper(chr($i)).'">'.strtoupper(chr($i)).'</a>
      ';
    }
    $c .= '</p> <hr>';
    $c .= '<div class="row">
                   <div class="col-md-6">
    <form method="post" action="'.$this->file.'mod=old">
          <label>Link starts with:</label>
          <div class="input-group">
            <input type="text" name="l" class="a form-control" value="'.htmlspecialchars($let).'">
            <input type="hidden" name="mod" value="cache">
            <span class="input-group-btn">
                <input type="submit" value="Search" class="submit btn btn-primary">
            </span>
          </div></div></div>
    </form>';

    if (!empty($let)) {
        $q = $this->db->query('SELECT o.id, o.url AS ourl, l.url AS lurl, o.tstamp, o.sticky FROM '.$this->table.'oldlinks AS o LEFT JOIN '.$this->table.'cache AS l
                                ON l.id=o.link_id WHERE LOWER(o.url) LIKE '.$this->db->escape(strtolower($let).'%').' ORDER BY o.url');

        $num = $this->db->num_rows($q);
        if ($num>0) {
          $c .= '<p class="center text-info">Records found: '.$num.'</p>';
          $c .= '<form method="post" action="'.$this->file.'mod=cache">';
          $c .= '<table id="list" class="table table-striped"><tr><th class="left">Old URI</th><th class="left">Cached URI</th><th>Moved to olds</th><th>Sticky</th><th>Action</th>';
          while ($row = $this->db->fetch($q)) {
            $c .= '<tr>
              <td class="left">'.htmlspecialchars($row['ourl']).'</td>
              <td class="left">'.htmlspecialchars($row['lurl']).'</td>
              <td>'.$row['tstamp'].'</td>
              <td>'.($row['sticky']?'YES':'NO').'</td>
              <td class="nowrap">
                <div class="btn-group">
                    <a href="'.$this->file.'mod=delete&amp;old&amp;lid='.$row['id'].'&amp;from=old:'.$let.'" class="btn btn-default" onclick="return confirm(\'Are you sure?\');"><img src="'.$this->resPath.'img/button_garbage.gif" alt="Delete" title="Delete" ></a>
                    <a href="'.$this->file.'mod=sticky&amp;old&amp;lid='.$row['id'].'&amp;from=old:'.$let.'" class="btn btn-default"><img src="'.$this->resPath.'img/button_sticky.gif" alt="Sticky on/off" title="Sticky on/off"></a>
                </div>
              </td>
            </tr>';
          }
          $c .= '</table></form>';
        } else {
          $c .= '<p class="text-info">No old links found.</p>';
        }
    } else {
        $c .= '<p class="text-muted">Input any filter. Use "%" to get all links.</p>';
    }
    return $c;
  }

  public function noCache() {
    $c = '<h1>Welcome to the CoolUris\' project\'s LinkManager</h1>
    <p>To be able to work with this LinkManager, you have to have the cache enabled.</p>';
    return $c;
  }

  public function welcome() {
    $c = '<h1>Welcome to the CoolUris\' LinkManager</h1>
    <p>This manager is part of the URI Transformer project.</p>
    <dl>
      <dt>Author:</dt>
      <dd>Jan Bednařík</dd>
      <dt>Release date:</dt>
      <dd>March, 2007</dd>
      <dt>Author contact:</dt>
      <dd><a href="mailto:info@bednarik.org">info@bednarik.org</a></dd>
      <dt>Official website:</dt>
      <dd><a href="http://uri.bednarik.org">http://uri.bednarik.org</a></dd>
    </dl>
    ';
    return $c;
  }

  public function link() {
    if (empty($_GET['lid'])) {
      $c = '<h1>Create new CoolUri</h1>';
      $new = true;
    } else {
      $c = '<h1>Update this CoolUri</h1>';
      $new = false;
      $id = (int)$_GET['lid'];
    }

    if (!$new) {
      $q = $this->db->query('SELECT * FROM '.$this->table.'cache WHERE id='.$id);
      $data = $this->db->fetch($q);
      $data['params'] = str_replace('&amp;','&',$this->serializedArrayToQueryString($data['params']));
    }

    if (!empty($_POST)) {
      $data = $_POST;
      $data = array_map('trim',$data);
      if (empty($data['url']) || empty($data['params'])) {
        $c .= '<div class="error"><p class="text-danger">You must fill all inputs.</p></div>';
      } else {
        $params = \Bednarik\Cooluri\Core\Functions::convertQuerystringToArray($data['params']);
        $cp = \Bednarik\Cooluri\Core\Functions::prepareParamsForCache($params);

        $ok = true;
        $olq = $this->db->query('SELECT COUNT(*) FROM '.$this->table.'cache WHERE params='.$cp.($new?'':' AND id<>'.$id));
        $num = $this->db->fetch_row($olq);
        if ($num[0]>0) {
          $c .= '<div class="error"><p>A different link with such parameters exists already.</p></div>';
          $ok = false;
        }
        $temp = preg_replace('~/$~','',$data['url']);
        if ($temp==$data['url']) $temp .= '/';
        $olq = $this->db->query('SELECT COUNT(*) FROM '.$this->table.'cache WHERE (url='.$this->db->escape($temp).' OR url='.$this->db->escape($data['url']).')'.($new?'':' AND id<>'.$id));
        $num = $this->db->fetch_row($olq);
        if ($num[0]>0) {
          $c .= '<div class="error"><p>A different link with such URI exists already.</p></div>';
          $ok = false;
        }

        if ($new && $ok) {
          $q = $this->db->query('INSERT INTO '.$this->table.'cache(url,params,sticky,crdatetime)
                                        VALUES('.$this->db->escape($data['url']).',
                                        '.$cp.',
                                        '.(!empty($data['sticky']) && $data['sticky']==1?1:0).',
                                        NOW())');
          $this->db->query('DELETE FROM '.$this->table.'oldlinks WHERE url='.$this->db->escape($data['url']));
          if ($q) {
            $c .= '<div class="succes"><p>The new link was saved successfully.</p></div>';
            $c .= '<p class="center"><a href="'.$this->file.'mod=cache&l='.htmlspecialchars($data['url']).'">Show &gt;&gt;</a></p>';
            $data = Array();
          }
          else $c .= '<div class="error"><p>Could not save the link.</p></div>';
        } elseif (!empty($id) && $ok) {
          $oldq = $this->db->query('SELECT * FROM '.$this->table.'cache WHERE id='.$id);
          $old = $this->db->fetch($oldq);
          if ($data['url']!=$old['url']) {
            $q = $this->db->query('INSERT INTO '.$this->table.'oldlinks(link_id,url)
                                        VALUES('.$id.',
                                        \''.$old['url'].'\')');
          }
          $qq = $this->db->query('UPDATE '.$this->table.'cache SET
                                  url='.$this->db->escape($data['url']).',
                                  params='.$cp.',
                                  sticky='.(!empty($data['sticky']) && $data['sticky']==1?1:0).'
                                  WHERE id='.$id.' LIMIT 1
                                  ');
          $this->db->query('DELETE FROM '.$this->table.'oldlinks WHERE url='.$this->db->escape($data['url']));
          if ($qq) {
            $c .= '<div class="succes"><p>The link was updated successfully.</p></div>';
            $c .= '<p class="center"><a href="'.$this->file.'mod=cache&l='.htmlspecialchars($data['url']).'">Show &gt;&gt;</a></p>';
          }
          else $c .= '<div class="error"><p>Could not update the link.</p></div>';
        }
      }
    }

    $c .= '
    <div class="row">
       <div class="col-md-6">
           <form method="post" action="'.$this->file.'mod=link'.($new?'':'&amp;lid='.$id).'">
            <fieldset>
            <legend>URI details</legend>
            <div class="form-group">
                <label for="url">URI:</label>
                <input type="text" name="url" id="url" class="form-control" value="'.(empty($data['url'])?'':htmlspecialchars($data['url'])).'">
            </div>
            <div class="form-group">
                <label for="params">Parameters (query string: id=1&amp;type=2):</label>
                <input type="text" name="params" id="params" class="form-control" value="'.(empty($data['params'])?'':htmlspecialchars($data['params'])).'">
            </div>
            <div class="form-group">
                <label for="sticky">
                    <input type="checkbox" class="check" name="sticky" id="sticky" value="1" '.(empty($data['sticky'])?'':' checked="checked"').'>
                    Sticky (won\'t be updated)
                </label>
            </div>
            </fieldset>
            <input type="submit" value=" '.($new?'Save new URI':'Update this URI').' " class="submit btn btn-primary">
        </form>
        </div>
    </div>
    ';
    return $c;
  }


  public function redirect() {

    $c = '<h1>Set new redirect</h1>';

    if (!empty($_POST)) {
      $id = (int)$_POST['to'];
      if (empty($id) || empty($_POST['url'])) {
        $c .= '<div class="error"><p class="text-danger">All fields are required.</p></div>';
      } else {
        $this->db->query('INSERT INTO '.$this->table.'oldlinks(link_id,url,sticky)
                                        VALUES('.$id.',
                                        '.$this->db->escape($_POST['url']).',
                                        '.(!empty($_POST['sticky']) && $_POST['sticky']==1?1:0).')');
        $c .= '<div class="succes"><p>The redirect was saved successfully.</p></div>';
      }
    }

    $allq = $this->db->query('SELECT * FROM '.$this->table.'cache ORDER BY url');

    $c .= '
    <div class="row">
       <div class="col-md-6">
        <form method="post" action="'.$this->file.'mod=redirect">
        <fieldset>
        <legend>Redirect details</legend>
        <div class="form-group">
            <label for="url">From:</label>
            <input type="text" name="url" id="url" class="form-control">
        </div>
        <div class="form-group">
        <label for="to">To:</label>
        <select name="to" id="to" class="form-control">
        ';
        while ($row = $this->db->fetch($allq)) {
          $c .= '<option value="'.$row['id'].'">'.$row['url'].'</option>
          ';
        }
        $c .= '</select>
        </div>
        <div class="form-group">
            <label for="sticky">
                <input type="checkbox" class="check" name="sticky" id="sticky" value="1" '.(empty($data['sticky'])?'':' checked="checked"').'>
                Sticky (won\'t be deleted upon Delete all action)
            </label>
        </div>
        </fieldset>
        <input type="submit" value="Submit this redirect" class="submit btn btn-primary">
        </form>
        </div>
    </div>
    ';
    return $c;
  }

  public function menu() {
    if ($this->enable)
      $mods = Array('cache'=>'Cached links','old'=>'Old links','link'=>'New link','redirect'=>'New redirect','all'=>'Delete/Update all');
    else
      $mods = Array(''=>'Home');
    $cm = '';
    if (!empty($_GET['mod'])) $cm = $_GET['mod'];
    if (empty($cm)) {
        $cm = 'cache';
    }
    $c = '<ul class="nav nav-tabs" style="margin-bottom: 20px">';
    foreach ($mods as $k=>$v) {
      $c .= '<li'.($cm==$k?' class="active"':'').'><a href="'.$this->file.($k?'mod='.$k:'').'">'.$v.'</a></li>';
    }
    $c .= '</ul>';
    return $c;
  }

}

?>

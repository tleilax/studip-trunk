<!-- Dynamische Links mit Icons -->
<div id='header'>
	<!--<div id='barTopLogo'>
		<img src="<?=$GLOBALS['ASSETS_URL']?>images/logoneu.jpg" alt="Logo Uni G�ttingen">
	</div>
	 -->
	<div id="barTopFont">
	<?=$GLOBALS['UNI_NAME']?>
	</div>
	<div id="barTopMenu">
		<ul>
		<?
		$accesskey = 0;
		foreach (array($home,$courses,$messages,$chat,$online,$homepage,$planner,$admin) as $item)
		{
			 if(!is_null($item)){
				 if($item['accesskey']){
					 $accesskey = ++$accesskey % 10;
				 }
				 ?>
				<li>
				<div style="font-size:12px; z-index:2; line-height:90%; padding-bottom:3px">
				<a href="<?=$item['link']?>" <?=($item['accesskey'] ? 'accesskey="' . $accesskey . '"' : '')?>>
				<img <?=tooltip($item['info'] . ($item['accesskey'] ? "  [ALT] + $accesskey" : "") )?> src="<?=$GLOBALS['ASSETS_URL'] . 'images/header_' . $item['image'] . '.gif'?>" border="0">
				<br>
				<?=htmlReady($item['text'])?>
				</a></div>
				</li>
				<?
			}
		}
		if(is_array($plugins))
		{
			foreach ($plugins as $plugin_item)
			{
				?>
				<li>
				<div style="font-size:12px; z-index:2; line-height:90%; padding-bottom:3px">
				<a href="<?=$plugin_item['link']?>">
				<img <?=tooltip($plugin_item['info'])?> src="<?=$plugin_item['image']?>" border="0">
				<br>
				<?=htmlReady($plugin_item['text'])?>
				</a></div>
				</li>
				<?
			}
		}
		?>
		</ul>
	</div>
</div>
<!--Statische Text Links -->
<div id="barTopTools">
	<ul>
		<li>
			<a href="http://blog.studip.de" target="_blank">
			<?=_("Stud.IP Blog")?>
			</a>
		</li>
	</ul>
</div>
<!-- Stud.IP Logo -->
<div id="barTopStudip">
	<a href="http://www.studip.de/" title="Studip Homepage">
		<img src="<?=$GLOBALS['ASSETS_URL']?>images/studipmirror.jpg" alt="Stud.IP Homepage">
	</a>
</div>
<div style="position: relative; margin-top: -34px; margin-right: 42px; float: right; z-index: 99;" align="right">
  <img src="<?=$GLOBALS['ASSETS_URL']?>images/studipdot.gif" alt="Stud.IP Homepage">
</div>
<!-- Leiste unten -->
<div id="barBottomLeft">
	<?=($current_page != "" ? _("Aktuelle Seite:") : "")?>
</div>
<div id="barBottommiddle">&nbsp;
	<?=($current_page != "" ? htmlReady($current_page) : "")?>
	&nbsp;
</div>
<!-- Dynamische Links ohne Icons -->
<div id="barBottomright">
	<ul>
		<? if ($quicksearch) : ?>
		<li>
		<form id="quicksearch" action="<?= URLHelper::getLink('sem_portal.php', array('send' => 'yes', 'group_by' => '0')) ?>" method="post">
		  <input type="hidden" name="search_sem_qs_choose" value="title_lecturer_number">
		  <input type="hidden" name="search_sem_sem" value="<?= $quicksearch['default_semester_nr'] ?>">
		  <input type="hidden" name="search_sem_1508068a50572e5faff81c27f7b3a72f" value="1">
		  <input class="quicksearchbox" type="text" name="search_sem_quick_search" value="" title="<?= sprintf(_('Nach Veranstaltungen suchen (%s)'), htmlready($quicksearch['default_semester_name']))?>">
		  <input class="quicksearchbutton" type="image" src="<?= Assets::url('images/quicksearch_button.png ') ?>" name="search_sem_do_search" value="OK" title="<?= sprintf(_('Nach Veranstaltungen suchen (%s)'), htmlready($quicksearch['default_semester_name'])) ?>">
		  <div id="quicksearch_autocomplete_choices" class="autocomplete"></div>
		</form>
		<script>
			(function () {
				var box = $("quicksearch").down(".quicksearchbox");
				box.value = "<?= _("Veranstaltungen") ?>";
				box.defaultValueActsAsHint();
				new Ajax.Autocompleter(box,
				                       'quicksearch_autocomplete_choices',
				                       'dispatch.php/autocomplete/course',
				                       {
				  minChars: 3,
				  paramName: 'value',
				  method: 'get',
				  callback: function(element, entry) {
				    return entry + '&' + Object.toQueryString({
				      'semester': '<?= $quicksearch['default_semester_nr'] ?>',
				      'what':  'title_lecturer_number',
				      'category': 'all'
				    });
				  },
				  afterUpdateElement: function (input, item) {
				    var seminar_id = encodeURI(item.down('span.seminar_id').firstChild.nodeValue);
				    document.location = "<?= $GLOBALS['ABSOLUTE_URI_STUDIP'] ?>details.php?sem_id=" +
				      seminar_id + "&send_from_search=1&send_from_search_page=<?= urlencode($GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP']) ?>sem_portal.php?keep_result_set=1";
				  }
				});
			})();
		</script>
		</li>
		<? endif ?>
		<? foreach (array($search, $imprint, $help, $caslogin, $shiblogin, $loginlogout) as $item) :
			 if(isset($item)){
 				 if($item['accesskey']){
					 $accesskey = ++$accesskey % 10;
				 }
				 ?>
				 <li>
				 <a <?=tooltip($item['info'] . ($item['accesskey'] ? "  [ALT] + $accesskey" : ""), false)?>
				 	href="<?=$item['link']?>" <?=($item['target'] ? "target=\"{$item['target']}\"" : "")?>
					 <?=($item['accesskey'] ? 'accesskey="' . $accesskey . '"' : '')?>>
				 <?=htmlReady($item['text'])?>
				 </a>
				 </li>
				 <?
			 }
		endforeach ?>
	</ul>
</div>
<br>
<!-- Ende Header -->

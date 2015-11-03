<?
class template {

	const
		SOURCE_VARIABLE = 2,
		SOURCE_DB       = 1,
		SOURCE_FILE     = 0,
		SOURCE_DEFAULT  = 0,

		DB          = 'db',
		TABLE       = 'template',
		FIELD_KEY   = 'alias',
		FIELD_VALUE = 'html'
	;

	protected $tpl;

	function __construct($source='', $mode=self::SOURCE_DEFAULT, $setup=NULL) {
		if ($source) {
			switch ($mode) {
				case self::SOURCE_VARIABLE: // from variable
					$this->openVariable($source);
					break;
				case self::SOURCE_DB: // from DB
					$this->openDB($source, $setup);
					break;
				case self::SOURCE_FILE:
					$this->openFile($source);
					break;
			}
		}
	}

	function openFile($fname) {
		$this->tpl = file_get_contents($fname);
	}

	function openDB($alias, $setup) {
		if (!isset($setup['db']))           $setup['db']          = self::DB;
		if (!isset($setup['table']))        $setup['table']       = self::TABLE;
		if (!isset($setup['field_key']))    $setup['field_key']   = self::FIELD_KEY;
		if (!isset($setup['field_value']))  $setup['field_value'] = self::FIELD_VALUE;

		$db = Registry::getInstance()->get($setup['db']);
		$rs = $db->query("SELECT * FROM `".$db->realEscapeString($setup['table'])."` WHERE `".$db->realEscapeString($setup['field_key'])."`=".$db->escape($alias));
		if ($sa = $db->fetch($rs)) {
			$this->tpl = $sa[$setup['field_value']];
		}
		else echo("template->open_db({$setup['table']}.{$setup['field_key']}='$alias') Alias not found.");
		$db->free($rs);
	}

	function openVariable($text) {
		$this->tpl = $text;
	}

	function setContent($value) {
		$this->tpl = $value;
	}

	function getContent() {
		return $this->tpl;
	}

	function apply($src=NULL) {
		$tpl = preg_replace('/<!---[\s\S]*--->/iU', "", $this->tpl); // remove comments
		if (is_array($src)) {
			foreach($src as $key => $val) {
				preg_match_all('/\{\?'.$key.'\}([\s\S]*)\{\/'.$key.'\}/iU', $tpl, $sources, PREG_SET_ORDER);
				foreach($sources as $source) {
					$args = preg_split('/\{:'.$key.'\}/iU', $source[1]);
					$tpl  = str_replace($source[0], $args[empty($val)], $tpl);
				}
				$tpl = str_replace('{'.$key.'}', $val, $tpl);
			}
		}
		return ($tpl);
	}
}
?>

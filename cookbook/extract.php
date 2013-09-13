<?php if (!defined('PmWiki')) exit();
/* extract.php, an extension for PmWiki 2.2, copyright Hans Bracker 2009. 
   a general regex processor for extracting text from multiple pages 
   using regular expressions and wildcard pagename patterns.
	
   This program is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published
   by the Free Software Foundation; either version 2 of the License, or
   (at your option) any later version.

   Syntax:  {(extract Term1 [Term3] [-Term3] ... [group=GroupName] [name=PageName] [keyword=value] ...)}
   See Cookbook:TextExtract for documentation and instructions.
*/
$RecipeInfo['TextExtract']['Version'] = '2009-10-15a';

// defaults for extractor search form
SDVA($ExtractFormOpt, array(
    'size'   		  => '30', 
    'button'  		=> FmtPageName('&nbsp;$[Search]&nbsp;', $pagename),
    'searchlabel' => FmtPageName('$[Search for]', $pagename),
    'pageslabel' 	=> FmtPageName('$[On pages]', $pagename),
    'wordlabel' 	=> FmtPageName('$[Match whole word]', $pagename),
    'caselabel' 	=> FmtPageName('$[Match case]', $pagename),
    'regexlabel' 	=> FmtPageName('$[Regular expression]', $pagename),
    'header' 		  => 'full',
    'phead' 	  	=> 'link',
));

// defaults array
SDVA($TextExtractOpt, array (
	'markup' 	 => 'cut', //code, text, source, on
	'unit'   	 => 'para', //line, page
	'highlight'=> 'yellow', //background color, 'bold', 'none'
	'linenum-color'  => 'green',
	'matchnum-color' => 'green',
	'pagenum-color'  => 'green',
	'title'     => XL('Text Extract'),
	'linewrap'  => 1,
	'case'   	  => 0,
	'regex'     => 0,
	'error'		  => 1,
#	'textlinks' => 0,
	'linktext'  => 'blue',
#	'shorten'   => 0,
	'lwords'    => 5,
	'rwords'    => 10,	
	'ellipsis'  => '�',
));
//defaults for specific markup modes:
SDV($TEModeDefaults['text']['shorten'], 1);
SDV($TEModeDefaults['text']['textlinks'], 1); 
#SDV($TEModeDefaults['cut']['shorten'], 1);

// main function for text extract processing
function TextExtract($pagename, $list, $opt = NULL) {
	global $TextExtractOpt, $TEModeDefaults, $TextExtract, $TextExtractExclude, 
		 $FmtV, $HTMLStylesFmt, $KeepToken, $KPV, $PageListArgPattern;
##DEBUG echo "<pre>LIST "; print_r($list); echo "</pre>";	
	foreach($opt as $k => $v) {
		if (is_array($v))	
			foreach($v as $kk =>$vv)
				$opt[$k][$kk] = stripmagic($vv);
		else $opt[$k] = stripmagic($v);
	}
	//internal arg array
	$par = array();
	//start time
	StopWatch('TextExtract start');
	if ($opt['stime']) $par['stime'] = $opt['stime'];
	else $par['stime'] = strtok(microtime(), ' ') + strtok('');
	//set default options
	foreach ($TEModeDefaults as $mode => $ar ) { 
		foreach ($ar as $k => $val)
			if ($opt['markup']==$mode && !$opt[$k]) $opt[$k] = $val;
	}
	$opt = array_merge($TextExtractOpt, $opt);
##DEBUG	echo "<pre>OPT "; print_r($opt); echo "</pre>";		
	//input parameter check
	if (!in_array($opt['unit'], array('line','para','page')) 
		OR !in_array($opt['markup'], array('code','cut','source','text','on')))
			return "%red%$[Error: check input parameters!]";
			
	foreach((array)@$opt['+'] as $i) $opt[''][] = $i; 
	if (!isset($opt['']) && !isset($opt['pattern'])) return '%red%$[Error: search term missing!]';

	//term is regular expression
	if ($opt['regex']==1) {
		$pat = $par['pattern'] = $opt[''][0] = $opt['pattern'];
		//exclude various input patterns
		SDVA($TextExtractExclude, array("*","?","+","(",")","[","]","^","$","|","??","\\"));
		foreach($TextExtractExclude as $v)
				if($pat==$v) return '%red%$[Error: disallowed character input!]'; 
	}	
	//no regex: term to be parsed and preg charcters escaped
	else {
		$terms = implode(" + ", $opt['']);
		if ($opt['-'])
			$terms .= " -".implode(" -", $opt['-']);	
		$par['pattern'] = $terms;		
		$pregchars = array('.','?','!','*','|','$','(',')','[',']','{','}',);
		foreach ($pregchars as $v) {
			$opt[''] = str_replace($v,'\\'.$v, $opt['']);	
			$opt['-'] = str_replace($v,'\\'.$v, $opt['-']);
		}
		if ($opt['word']==1)
			foreach ($opt[''] as $i => $pt)
				$opt[''][$i] = '\\b'.$pt.'\\b';
		$pat = implode("|", $opt['']);
	}
	$par['pat'] = $pat; 
##DEBUG echo $pat;	
	//always wrap lines when displaying preformatted 'source' code
	if ($opt['markup']=='source')
		$opt['linewrap'] = 1;
	// wrap lines of preformatted text and code
	//IE may not work with word-wrap, therefore special IE rule
	if($opt['linewrap']==1) { 
	  # whitespace wrap (perhaps copy styles to css stylesheet)
	  $HTMLStylesFmt['prewrap'] = "
      code, div.te-results pre, div.te-results code, code.escaped, pre.escaped {   
	    white-space: pre-wrap;       /* CSS-3                  */
	    white-space: -moz-pre-wrap;  /* Mozilla, since 1999    */
	    white-space: -pre-wrap;      /* Opera 4-6              */
	    white-space: -o-pre-wrap;    /* Opera 7                */
	    word-wrap: break-word;       /* Internet Explorer 5.5+ */
	    _white-space: pre; 
	  }
	  * html pre.escaped, * html code.escaped { white-space: normal; }
	  ";
	} 
	//setting keep values here, and keeptokens directly in TEHighLight()
	//instead of calling Keep again and again
	switch ($opt['highlight']) {
		case 'none':  
			$KPV['01�'] = $KPV['02�'] = "";
			break;		
		case 'bold': 
			$KPV['01�'] = "<strong>";
			$KPV['02�'] = "</strong>";
			break;
		case '1':
		default:
			$KPV['01�'] = "<span class='te-hilight'>";
			$KPV['02�'] = "</span>";
			$HTMLStylesFmt['te-hilight'] = 
				" .te-hilight { background-color: {$opt['highlight']}; } ";		
	}	
	$par['hitoklen'] = 6 + 4 * strlen($KeepToken); // token length * 2
	$KPV['03�'] = "<br />";
	$par['br-tag'] = $KeepToken."03�".$KeepToken;
	$KPV['04�'] = "<div class='spacer'><!-- spacer --></div>";
	$par['vspace'] = $KeepToken."04�".$KeepToken;
	
	//header, footer, pagelink prefix styles
	if ($opt['header']=='full') $opt['footer'] = 1;
	if ($opt['phead']) {
		SDV($HTMLStylesFmt['teprefix'], 
	    " .te-pageheader { margin:.8em 0 .5em 0; padding:.2em .2em 0 .2em;} 
	      .te-pageheader { border-top:1px solid #ccc; border-bottom:1px solid #ccc; background:#f7f7f7;}
		");
	}
	if ($opt['header']) {
		SDV($HTMLStylesFmt['teheader'], 
	    " .te-header  {margin-top:0.5em; padding:0.3em; border-top:1px solid #ccc; border-bottom:1px solid #ccc; background:#f7f7f7;}
		");	
	}
	if ($opt['footer']) {
		SDV($HTMLStylesFmt['tefooter'], 
	    " .te-footer {margin-top:0.5em; padding:0.3em; border-top:1px solid #ccc; border-bottom:1px solid #ccc; background:#f7f7f7;}
		");	
	}
	//number color defaults 
	foreach(array('line','match','page') as $c) {
		if ($opt[$c.'num']==1) $opt[$c.'num'] = $opt[$c.'num-color'];
		if ($opt[$c.'num']) $HTMLStylesFmt[$c.'num'] = " .{$c}num { color: {$opt[$c.'num']} ;} ";
	}	
	SDV($HTMLStylesFmt['telinktext'],
		" .te-linktext {color: {$opt['linktext']} } ");
		
	//case insensitive search
	$qi = $par['qi'] = (@$opt['case']==1) ? '' : 'i';
	
	$par['listcnt'] = ($FmtV['$MatchSearched']) ? $FmtV['$MatchSearched'] : count($list);
	//inits
	$par['sorcnt']=$par['matchnum']=$par['matchcnt']=$par['rowcnt']=0;
	$par['title'] = $opt['title'];
	//process each source page in turn
	$new = array(); $j = 0;
	foreach($list as $i => $pn) { 
		$par['source'] = $pn;
		$par['pname'] = substr(strstr($pn, '.'),1);
		$par['pmatchnum'] = 0;
		$par['prevpmnum'] = 0;				
		//get rows from source page
		$rows = TETextRows($pagename, $pn, $opt, $par);
		if (!$rows) continue;
		$j++;
		$list[$j] = $pn;
		//processing lines (rows)
		foreach ($rows as $k => $row) {
			$par['linenum'] = $k+1;
			//skip pages which don't match
			if ($opt['unit']=='page') if(!preg_match("($pat)".$qi, $row)) continue; 
			//preserve empty rows for 'all including' pattern
			if ($opt['unit']=='line' && $row=="" && $pat==".") { $new[$j]['rows'][] = $row; continue; }
			//skip rows which don't match
			if ($opt['unit']=='line' || $opt['unit']=='para') { if(!preg_match("($pat)".$qi, $row)) continue; }
			//use row 'as is' if markup=on or whole page, no futher row processing
			if ($opt['markup']=='on' && ($pat=="." || $opt['unit']=='page' || $opt['unit']=='para')) { 
					$new[$j]['phead'] = TEPageHeader($pagename, $pn, $opt, $par);
					$new[$j]['rows'][] = $row; 
					$par['rowcnt']++;
					continue; //start with next source row
			}
			//change some markup into code or 'defuse', so it will not get rendered, or cut it 
			$row = TEMarkupCleaner($row, $opt, $par);		
			//exclude lines containing matches with cut pattern
			if ($opt['cut']!='')
					if(preg_match("({$opt['cut']})".$qi, $row)) continue;
			//count matches in row		
			$par['rowmatchcnt'] = preg_match_all("($pat)".$qi, $row, $mr);				
			//check if textrow needs processing
			if($opt['snip']!='') 
				$row = preg_replace("({$opt['snip']})", '', $row);
			$row = ltrim($row);
			//empty row	
			if ($row=='') continue;	
			//highlight matches
			if($opt['highlight'] && $pat!='.')
					$row = TEHighlight($opt, $par, $row);	
			//numbering
			$par['pagenum'] = $par['pagecnt']+1;
			$par['rowcnt']++;
			$new[$j]['rowcnt']++;
			$new[$j]['pmatchcnt'] += $par['rowmatchcnt'];
			$par['prevmnum'] = $par['matchnum'];
			$par['matchcnt'] = $par['matchnum'] += $par['rowmatchcnt'];
			$par['prevpmnum'] = $par['pmatchnum'];
			$par['pmatchnum'] += $par['rowmatchcnt'];
			$rownum = ($opt['linenum'] || $opt['matchnum'] || $opt['pagenum']) ?
					TERowNumbers($opt, $par) : '';
			//add new result row
			$new[$j]['rows'][] = $rownum.$row;
			//add vertical spacing to para
			if ($opt['unit']=='para' && $opt['markup']!='source') 
					$new[$j]['rows'][] = "\n��"; 
		} //end of page rows processing
		if (count($new[$j]['rows'])>0) {
			//add pagelink (prefix) row
			if($opt['phead'])
					$new[$j]['phead'] = TEPageHeader($pagename, $pn, $opt, $par);		
			$par['sorcnt']++;
			if ($opt['pfoot'])
				$new[$j]['pfoot'] = TEPageFooter($pagename, $pn, $opt, $par);
			$new[$j]['name'] = $pn;
		}
	} //end of source pages processing
	//slice list if we got #section
	if (@$opt['section'] && @$opt['count'])	TESliceList($new, $opt);
	$par['pagecnt'] = count($new);
	//sort list by results per page, subsort by name
	if ($opt['order']=='results')	TESort($new);

## DEBUG echo "<pre>NEW "; print_r($new); echo "</pre>";
	//output text from array of rows, adding page prefix header (and footer)
	$out = '';
	foreach ($new as $i => $ar) {
		//markup pageheader
		if($opt['phead'])
			$out .= MarkupToHTML($pagename, $new[$i]['phead']);
		//markup rows	
		$rnew = implode("\n", $new[$i]['rows']);
		$rnew = TEVSpace($rnew, $par, $opt);	
		global $LinkFunctions;
		if ($opt['textlinks']==1) { 
			$lf = $LinkFunctions;
			foreach($LinkFunctions as $k => $v)
				$LinkFunctions[$k] = 'TELinkText';
		}
		$out .= ($opt['markup']=='source') ? "<code class='escaped'>".$rnew."</code>"
					: MarkupToHTML($pagename, $rnew);
		if ($opt['textlinks']==1)	$LinkFunctions = $lf;
		//markup pagefooter
		if ($opt['pfoot'])
			$out .= MarkupToHTML($pagename, $new[$i]['pfoot']);
	}
	//stop timer
	TEStopwatch($par);	
	//make header and footer
	$header = TEHeader($opt, $par);
	$header = MarkupToHTML($pagename, $header); 
	$footer = TEFooter($opt, $par);
	$footer = MarkupToHTML($pagename, $footer);		
	$out = $header.$out.$footer;
	StopWatch('TextExtract end'); 
	return Keep($out);
} //}}}

//make rows array from source page
function TETextRows($pagename, $source, $opt, &$par ) {
	if ($source==$pagename) return '';
	$page = ReadPage($source);
	if (!$page) return '';
  $text = $page['text']; 
	//use pagename#section if present
	if($opt['section'])
		$text = TextSection($text, $source.$opt['section']);
  //skip page if it has an exclude match
  if ($opt['pat']['-']!='')
	  foreach ($opt['-'] as $pat) {
			if (preg_match("($pat)".$par['qi'], $text)) return; } 
	 //skip page if it has no match; all inclusive elements need to match (AND condition)
  foreach ($opt[''] as $pat)  {
  if (!preg_match("($pat)".$par['qi'], $text)) return; }		

	$text = Qualify($source, $text);
	$rows = explode("\n", rtrim($text));
	//use range of lines
	if($opt['lines']!='') {
			$cnt = count($rows);
			if(strstr($opt['lines'],'..')) {
				preg_match_all("/\d*/", $lines, $k);
				$a=$k[0][0];  $b=$k[0][3]; $c=$k[0][2];
				if($a && $b) 
					$rows = array_slice($rows, $a-1, $b-$a+1);
				else if($a)
					$rows = array_slice($rows, $a-1);
				else if($c)
					$rows = array_slice($rows, 0, $c);
			}		
			else if($opt['lines']{0}=='-')
				$rows = array_slice($rows, $opt['lines']);
			else $rows = array_slice($rows, 0, $opt['lines']); 
	}
	//unit=para: combine rows to paragraph rows
	if ($opt['unit']=='para') {
		$paras = array(); $j=0;
		foreach($rows as $i => $row) {
			$row = rtrim($row);
			if ($row=='') { $j++; continue; }
			$paras[$j] .= $row."\n";
		}
		$rows = $paras;
	}
	//unit=page: combine rows to one row
	if ($opt['unit']=='page') {
		$part = implode("\n",$rows);
		$rows[0] = $part;
	}
	return $rows;
} //}}}


//cleanup of markup
function TEMarkupCleaner($row, $opt, $par) {
	global $KeepToken;
	if ($opt['markup']=='source') {
		//clean <>"tag" characters 
		$row = str_replace("<","&lt;", $row);
		$row = str_replace(">","&gt;", $row);
		//that's all for 'source' processing
		return $row;
	}
	$new = array();
	//fix orphaned @],[@,=],[= 
	foreach(array("@","=") as $x) {
		$a = strpos($row,'['.$x); $b = strpos($row,$x.']');
		if ($b!=0 && ($a===false || $a>$b)) $row = '['.$x.$row;
		else if ($a!=0 && ($b===false || $a>$b)) $row .= $x.']';
	}	
	//keep escaped text using tokens
	$keep = array();
	if (preg_match_all("/\\[([=@])(.*?)\\1\\]/s".$par['qi'], $row, $m)) {
		foreach ($m[0] as $i => $v) {
			$keep[$i][0] =  $v;
			$keep[$i][1] = $m[1][$i];
			$row = str_replace( $v, "<__TOK__".$i."__>", $row);
		}
	}
	//directives (: ... :) possibly multi-line
	if ($opt['markup']=='cut' || $opt['markup']=='text') {
			$row = preg_replace("/\\(:(\\w+\\b.*?):\\)/s", "", $row);
	}
	$lines = explode("\n", $row);
	foreach ($lines as $k => $row) {
			//extra spaces
			$row = preg_replace("/\\n\\s+/", "\n", $row);
			//directives (: ... :) encoding
			if ($opt['markup']=='code') {
				$row = preg_replace("/\\(:(comment)\\s+(.*?)\\s*:\\)/", "[@(:$1:@] $2 :)", $row); 
				$row = preg_replace("/\\(:(\\w+\\b.*?):\\)/", "[@(:$1:)@]", $row);
			}
			//fixing double and empty [@ and [=
			$row = preg_replace("/\\[([@=])\\s*\\[\\1/","[\\1",$row);
			$row = preg_replace("/([@=])\\]\\s*\\1\\]/","\\1]",$row);
			$row = preg_replace("/\\[([@=])\\s*\\1\\]/","",$row);
			//whitespace						
			$row = preg_replace("/^\\s+/", "", $row);
			//A: Q: 
			$row = preg_replace("/^[AQ]:\\s+/", "", $row);
			//code and cut treat some markup differently
			if ($opt['textlinks']==1) {
					//variable link
					global $WikiWordPattern;
					$row = preg_replace("/\\$($WikiWordPattern)\\b/", "&#36;$1", $row);			
			}
			switch($opt['markup']) {
				case 'text':
					//strong, emphasis: remove
					$row = preg_replace("/'''(.*?)'''/", "$1", $row);
					$row = preg_replace("/''(.*?)''/", "$1", $row);
					// big, small: remove
					$row = preg_replace("/'''(.*?)'''/", "$1", $row);
					$row = preg_replace("/'\\-(.*?)\\-'/", "$1", $row);
					$row = preg_replace("/\\[(([-+])+)(.*?)\\1\\]/", "$1", $row);	
					//super, sub script: remove
					$row = preg_replace("/'\\^(.*?)\\^/", "$1", $row);
					$row = preg_replace("/'_(.*?)_'/", "$1", $row);
					//ins, del: remove
					$row = preg_replace("/\\{\\+(.*?)\\+\\}/", "$1", $row);
					$row = preg_replace("/\\{-(.*?)-\\}/", "$1", $row);

					//wiki styles %...% : remove
					$row = preg_replace("/(%.*?%)/", "", $row);	
					//indents: remove
					$row = preg_replace("/^-+[<>]\\s*/", "", $row);	
					//unordered list items: remove bullets 
					$row = preg_replace("/^(\\*+)(.*?)$/", "$2 {$par['br-tag']}", $row);
					//ordered list items: remove numerals
					$row = preg_replace("/^(\\#+)(.*?)$/", "$2 {$par['br-tag']}", $row);				
					//definition list items: remove
					$row = preg_replace("/^(:+)(?=(\s*)([^:]+):)/", " ", $row);	
					//carry on with 'cut'						
				case 'cut':
					//divs >>...<< : remove
					$row = preg_replace("/>>(.*?)<</", "", $row);
					//anchors : remove
					$row = preg_replace("/(\\[\\[#[A-Za-z][-.:\\w]*\\]\\])/","",$row);
					break;	
				case 'code':
					//indents: remove
					$row = preg_replace("/^-+[<>]\\s*/", "", $row);				
					//unordered list items: bullets to * 
					$row = preg_replace("/^(\\*+)(.*?)$/", "&#42;$2 {$par['br-tag']}", $row);
					//ordered list items: numerals to #
					$row = preg_replace("/^(\\#+)(.*?)$/", "&#35;$2 {$par['br-tag']}", $row);				
					//definition list items: to :
					$row = preg_replace("/^(:+)(?=(\s*)([^:]+):)/", "&#58; ", $row);
					//divs >>...<< 	: escape			
					$row = preg_replace("/>>(.*?)<</", "[@>>$1<<@]", $row);
					//anchors: escape
					$row = preg_replace("/(\\[\\[#[A-Za-z][-.:\\w]*\\]\\])/","[@$1@]",$row);
					//wiki styles %...% : escape
					$row = preg_replace("/(%.*?%)/", "[@$1@]", $row);
					//tables || || || @ escape
					$row = preg_replace("/^\\|\\|(.*)$/", "[@||$1 @] {$par['br-tag']}", $row);
					break;
			}	

			//change all headings to extra large text
			$row = preg_replace("/^(!{1,6})(.*)/","[++ $2 ++]" , $row);
			//markup expression encoding
			$row = preg_replace("/\\{\\((\\w+\\b.*?)\\)\\}/", "[@{($1)}@]", $row); 
			$row = trim($row);
			if ($row=='') continue;
			//break each line nicely
			$row = $row."��";			
			$new[$k] = $row;
		}
	$row = implode("\n", $new);
	//re-inserting code strings via tokens		
	foreach ($keep as $i => $v)
			$row = str_replace("<__TOK__".$i."__>", $keep[$i][0], $row);
	return $row;
} //}}}

//insert markup for highlighting matches
function TEHighlight($opt, &$par, $row) {
	global $LinkPattern, $UrlExcludeChars, $ImgExtPattern, $KeepToken, $KPV;
	//for source view we don't want whole links highlight:
	if ($opt['markup']=='source') $linkpat = $urlpat = '';
	else {
		//matches in links: highlight entire link, and other matches
		$linkpat = "\\[\\[\\s*(.*?)\\]\\]";
		$urlpat = "($LinkPattern)\\/\\/([^\\s$UrlExcludeChars]*[^\\s.,?!$UrlExcludeChars])";
	}
	if (preg_match_all("(($linkpat)|($urlpat)|({$par['pat']}))".$par['qi'], $row, $m, PREG_OFFSET_CAPTURE)) {
		## DEBUG echo "<pre>OTHER "; print_r($m[0]); echo "</pre>";
		$k = 0; $mpos = array();
		foreach($m[0] as $i => $v) { 
			if (!preg_match("({$par['pat']})".$par['qi'], $v[0])) continue;
			if (preg_match("/$LinkPattern/",$m[4][$i][0])) 
					$item = $v[0]." "; 
			else $item = $v[0];
			$pos = $v[1] + $k * $par['hitoklen'];
			$row = substr_replace($row, $KeepToken."01�".$KeepToken.$item.$KeepToken."02�".$KeepToken, $pos, strlen($item));
			$row = rtrim($row,'% ');
			$k++;
			$mpos[] = $pos;
		}
		if ($opt['shorten'])
			$row = TEShortenRow($row, $par, $opt); 
	}
	return $row;
} //}}}

//shorten row
function TEShortenRow($row, $par, $opt) {
	global $KeepToken;
	//number of words left and right of highlight
	$a = ($opt['shorten']>1) ? $opt['shorten'] : $opt['lwords']; 
	$b = ($opt['shorten']>1) ? 2*$opt['shorten'] : $opt['rwords'];
	$hi = $new = array();
	$words = explode(' ', $row);
	foreach ($words as $i => $wd)
			if (strpos($wd, $KeepToken)!==false) $hi[] = $i;
	for ($i=0; $i < count($words); $i++) {
		foreach ($hi as $k => $n) {
			if (($n-$a) > $i) { 
				if (($n-$a) == $i+1)
					if (!$new[$i]) $new[$i] = $opt['ellipsis'];
				if ($new[$i-1] && $new[$i-1]!=$opt['ellipsis']) $new[$i] = $opt['ellipsis'];
				continue 2; 
			}
			if ($i == end($hi)+$b+1) $new[$i] =  $opt['ellipsis'];
			if ($i > $n+$b || $i==($hi[$k+1]-$a)) continue;
			if ($new[$i]) continue 2;
			$new[$i] = $words[$i]; 
			continue 2;
		}
	}
	$row = implode(' ', $new);
	return $row."��";
} //}}}


//make header
function TEHeader(&$opt, $par) {
	$cnt = $par['matchnum'];
	$out = "(:div000 class='te-results':)\n";
	if ($opt['header']) $out .= "(:div001 class='te-header':)\n";
	switch($opt['header']) {
		default: 
			$out .= TEVarReplace($opt['header'], $par);
			break;
		case 'count':
		case 'counter':
			$out .= "'''$[Results:] $cnt'''";
			break;
		case 'all':
		case 'full':
			$time = ($opt['timer']) ? '('.$par['time'].')' : '';
			$pgs = ($par['listcnt']>1) ? '$[pages]' : '$[page]';
			$from = "$[from] {$par['listcnt']} $pgs $[searched]";
			if ($par['pagecnt']>1)
				$from = "$[on] {$par['pagecnt']} $[pages] ".$from;
			$out .= "[[#extracttop]]%lfloat%[+ '''{$opt['title']}''' +]  %right%'''{$cnt} $[results]''' &nbsp;&nbsp; {$from} &nbsp;&nbsp; '''{$par['pattern']}''' &nbsp;&nbsp; {$time}";
			$opt['footer'] = "%center% '''$[End of] {$opt['title']}'''  &nbsp;&nbsp; [[#extracttop|$[(start)]]]";
			break;
	}
	if ($opt['header']) $out .= "\n(:div001end:)";
	return $out;	
} //}}}

//make footer
function TEFooter($opt, $par) {
	$out = '';
	if ($opt['footer'] && $par['pagecnt']>0) {
		$out .= "\n(:div002 class='te-footer':)".TEVarReplace($opt['footer'], $par)."\n(:div002end:)";
	}
	if($opt['error']==1) {
			if ($par['pagecnt']==0)
				$error = "\n%red%$[Found no matches!]%%";
			if ($par['listcnt']==0)
				$error = "\n%red%$[Error: no pages to be searched!]%%";
			$out .= $error;
	}
	return $out."\n(:div000end:)";	
} //}}}

//make page header
function TEPageHeader($pagename, $source, $opt, &$par) {
	$pnum = ($opt['pagenum']) ? ($par['pagenum']).". " : '';
	$out = "\n>>te-pageheader<<\n";
	if($opt['phead']=='link' ) {
		$out .= "'''%color={$opt['pagenum']}%{$pnum}%%[+ [[$source]] +]'''";
	}
	elseif($opt['phead']=='linkmod' ) {
		$lmod = PageVar($source,'$LastModified');
		$lmby = PageVar($source,'$LastModifiedBy');
		$out .= "%rfloat%''$[last modified by] [[~{$lmby}]] $[on] {$lmod}'' %left%'''%color={$opt['pagenum']}%{$pnum}%%[+ [[$source]] +]'''";
	}
	else {
		$out .=  TEVarReplace($opt['phead'], $par);
	}
	$out .= "\n>><<\n";
	return $out;
} //}}

//make page footer
function TEPageFooter($pagename, $source, $opt, &$par) {
	$out = "\n".$opt['pfoot'];
	return $out;
} //}}

//make results (line) numbers
function TERowNumbers($opt, $par) {
	$new = '';
	if ($opt['linenum']) {
		if ($opt['pagenum']) {
			$new = Keep("<span class='pagenum'>{$par['pagenum']}.</span><span class='linenum'>{$par['linenum']}. </span>",'T');
		} else
			$new = Keep("<span class='linenum'>{$par['linenum']}. </span>",'T');
	} else
	if ($opt['matchnum'] && $par['pat']!=".") {
		if ($opt['pagenum']) {
			if ($par['rowmatchcnt']>1)
				$num = ($par['prevpmnum']+1)."-".$par['pmatchnum'];
			else $num = $par['pmatchnum'];		
			$new = Keep("<span class='pagenum'>{$par['pagenum']}.</span><span class='matchnum'>$num. </span>",'T');
		} else {
		if ($par['rowmatchcnt']>1)
			$num = ($par['prevmnum']+1)."-".$par['matchnum'];
		else $num = $par['matchnum'];		
			$new = Keep("<span class='matchnum'>$num. </span>",'T');
		}			
	}
	return $new; 	
} //}}}

//substitution of pseudo template variables
function TEVarReplace ($text, $par) {
	foreach($par as $k => $v) {
		if (is_array($v)) continue;
		$text = str_replace('{$$'.$k.'}' , $v, $text);
	}
	return $text;
} //}}}

//Link function to suppress links
function TELinkText($pagename,$imap,$path,$title,$txt,$fmt=NULL) {
	return "<span class='te-linktext'>".$txt."</span>"; 
} //}}}

//timer
function TEStopwatch(&$par) {
		$wtime = strtok(microtime(), ' ') + strtok('') - $par['stime'];
		$xtime = sprintf("%04.2f %s", $wtime, ''); //time in secs	
		$par['time'] = $xtime." $[seconds]";
} //}}}

// markup (:extract ....:) search form
Markup('extractform', 'directives','/\\(:extract\\s*(.*?)\\s*:\\)/e',
		"TEFormMarkup(\$pagename, PSS('$1'))");
// extractor search form
function TEFormMarkup($pagename, $arg) {
	global $ExtractFormOpt, $InputValues;
	$opt = ParseArgs($arg);
	$PageUrl = PageVar($pagename, '$PageUrl');
	$opt = array_merge($ExtractFormOpt, $opt);
	$opt['action'] = 'search';
	$opt['fmt'] = 'extract';
	foreach ($opt as $key => $val) {
		if(!is_array($val))
			if (!isset($InputValues[$key])) $InputValues[$key] = $opt[$val];
	}	
	$req = array_merge($_GET, $_POST);
	foreach($req as $k => $v) {
		if (!isset($InputValues[$k])) 
				$InputValues[$k] = htmlspecialchars(stripmagic($v), ENT_NOQUOTES);
	}
	if (!$InputValues['q']) $InputValues['q'] = $opt['pattern'];
	if (!$InputValues['page']) $InputValues['page'] = $opt['defaultpage'];
	$checkword = ($InputValues['word'])? "checked=1" : '';
	$checkcase = ($InputValues['case'])? "checked=1" : '';
	$checkregex = ($InputValues['regex'])? "checked=1" : ''; 
	//form
	$out = "<form class='wikisearch' action='{$PageUrl}' method='post' >";
	$out .= "\n<table>";
	if ($opt['pattern'])
		$out .= "<input type='hidden' name='q' value='{$InputValues['q']}' /> \n";
	else $out .= "<tr><td>{$opt['searchlabel']} </td><td><input type='{$type1}' name='q' value='{$InputValues['q']}' class='inputbox searchbox' size='{$opt['size']}' /> </td></tr> \n";
	
	if ($opt['page']) 
		$out .="<input type='hidden' name='page' value='{$InputValues['page']}' /> \n";
	else $out .= "<tr><td>{$opt['pageslabel']} </td><td><input type='text' name='page' value='{$InputValues['page']}' class='inputbox searchbox' size='{$opt['size']}' /> </td></tr> \n";
	if (!$opt['pattern']) {
		$out .= "<tr><td></td><td><input type='checkbox' name='word' value='1' $checkword/> {$opt['wordlabel']}</td></tr>";
		$out .= "<tr><td></td><td><input type='checkbox' name='case' value='1' $checkcase/> {$opt['caselabel']}</td></tr>";
	}
	if ($opt['regex'])
		$out .= "<tr><td></td><td><input type='checkbox' name='regex' value='1' $checkregex/> {$opt['regexlabel']}</td></tr>";

	$out .= "<tr><td></td><td>&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' class='inputbutton searchbutton' value='{$opt['button']}' /></td></tr></table> \n";
	foreach ($opt as $k => $v) {
		if ($v == '' || is_array($v)) continue;
		if (in_array($k, array('pattern','page','defaultpage','q','label','value','size','searchlabel','pageslabel','wordlabel','caselabel','regexlabel','regex'))) continue;
		$k = str_replace("'", "&#039;", $k);
		$v = str_replace("'", "&#039;", $v);
		$out.= "\n<input type='hidden' name='".$k."' value='".$v."' />";
	}	
	$out .= "</form>";
	return Keep($out);
} //}}}

function TEVSpace($text, $par, $opt) {
	global $HTMLPNewline;
	if ($HTMLPNewline != '' || $opt['markup']=='source') 
			return str_replace('��','',$text);
	else return str_replace('��',$par['vspace'],$text);
} //}}}

## (extract ......) same as PowerTools (pagelist.... fmt=extract) [all pagelist parameters allowed]
$MarkupExpr['extract'] = 'MxTextExtract($pagename, $argp)'; 
function MxTextExtract($pagename, $opt) {
	StopWatch('extract start');
	$opt['fmt'] = 'extract';
	$out = FmtPageList('$MatchList', $pagename, $opt, 0); 
	$out = preg_replace("/[\n]+/s","\n",$out);
	StopWatch('extract end');
	return $out;
} //}}}

//initialisations
if ($_REQUEST['fmt']=='extract') { 
	//add a space, so FmtPageList() wiil not transform 'foo/' to group='foo'
	if ($_REQUEST['group'] || $_REQUEST['name'] || $_REQUEST['page'])
		 $_REQUEST['q'] = " ".$_REQUEST['q']; 
	//leave out the standard Pmwiki searchresult header and footer text
	$SearchResultsFmt = "\$MatchList";
#	$PageSearchForm = '$[{$SiteGroup}/TextExtract]';
}

//fmt=extract for (:pagelist:) and (:searchbox:)
SDV($FPLFormatOpt['extract'], array('fn' =>  'FPLTextExtract'));
function FPLTextExtract($pagename, &$matches, $opt) {
	##DEBUG	echo "<pre>OPT "; print_r($opt); echo "</pre>";	
	global $FmtV, $EnableStopWatch, $KeepToken, $KPV;
	$EnableStopWatch = 1;
	StopWatch('TextExtract pagelist begin');
	$opt['stime'] = strtok(microtime(), ' ') + strtok('');
	$opt['q'] = ltrim($opt['q']);
	foreach ($opt[''] as $k => $v)
	$opt[''][$k] = htmlspecialchars_decode($v);
	//treat single . search term as request for regex 'all characters'
	if ($opt[''][0]=='.' || $opt['pattern']=='.') $opt['regex'] = 1;
	//MakePageList() does not evaluate terms as regular expressions, so we save them for later
	if (@$opt['regex']==1) {
		$opt['pattern'] = implode(' ', $opt['']);
		unset($opt['']);
	}
	if (@$opt['page']) $opt['name'] .= ",".$opt['page'];
	//allow search of anchor sections
	if ($sa=strpos($opt['name'],'#')) {
		$opt['section'] = strstr($opt['name'],'#');
		$opt['name'] = substr($opt['name'],0,$sa);
	}
	$list = MakePageList($pagename, $opt, 0);
	//extract page subset according to 'count=' parameter
	if (@$opt['count'] && !$opt['section'])
		TESliceList($list, $opt);
	return TextExtract($pagename, $list, $opt);
} //}}}

//slice list for count= option
function TESliceList(&$list, $opt) {
		list($r0, $r1) = CalcRange($opt['count'], count($list));
		if ($r1 < $r0) 
			$list = array_reverse(array_slice($list, $r1-1, $r0-$r1+1));
		else 
			$list = array_slice($list, $r0-1, $r1-$r0+1);	
} //}}}

//sort by match count and subsort by name
function TESort(&$new) {
	usort($new,"TESortByMatchCnt");
	$anew = $temp = array();
	$cnt = count($new);
	for ($i=0; $i<$cnt; $i++) {
		$temp[] = $new[$i];
		if (($new[$i]['pmatchcnt'] > $new[$i+1]['pmatchcnt']) || $i+1==$cnt) { 
			if (count($temp)>1)	usort($temp, "TESortByName");
			$anew = array_merge($anew, $temp);
			unset($temp);			
		}
	}
	$new = $anew;
} //}}}
//sort helper functions
function TESortByMatchCnt($a, $b) { return $b['pmatchcnt'] - $a['pmatchcnt']; }
function TESortByName($a, $b) { return strnatcasecmp($a['name'], $b['name']); }
//EOF
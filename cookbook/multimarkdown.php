<?php if (!defined('PmWiki')) exit();

session_start();

/**
 * MarkdownDocument Class
 *
 * Converts Markdown to HTML.
 *
 * @param $text
 * @param $opts = array()
 */

class MultiMarkdownDocument {

  // For non-PHP Markdown implementations, we'll write the text to
  // convert to a temporary file.
  public $tempFilePath;

  // A global ID that each (:markdown:) section on the same page will
  // increase, to avoid anchor conflicts.
  public $mdID;

  public function __construct($text, $opts = array()) {

    $this->myFarmD = & $GLOBALS["FarmD"];
    $this->text = $text;

    if (!isset($_SESSION['mdID'])) {
        $_SESSION['mdID'] = 0;
    } else {
        $_SESSION['mdID']++;
    }
    $this->mdID = $_SESSION['mdID'];

    $defaults = array(
      'cli_options' => '--smart',
    );

    $this->options = array_merge($defaults, $opts);
  }

  public function html() {
    return $this->MultiMarkdown();
  }

  public function MultiMarkdown() {

    // Tidy the input for MMD
    $text = $this->text;
    $s = array( '&gt;', '&lt;' );
    $r = array( '>', '<' );
    $text = str_replace($s, $r, $text);
    $astr = array(
      "<:vspace>" => "\n\n",
      "(:nl:)" => "\n",
    );
    $text = str_replace(array_keys($astr), $astr, $text);

    // Apply markup that we can't do with MMD

    // visble linebreaks \\, convert to MMD double-space linebreak
    $text = preg_replace('/\s*\\\\\\\\\s*\n/', "  \n", $text);
    // parse wiki links [[...]]
    $text = preg_replace('/(\[\[\s*(.*?)\]\])/e', '$this->onlylink(MarkupToHTML($pagename, "$1"))', $text);

    $this->writeTempFile($text);

    $bin = $this->myFarmD."/pub/cgi/multimarkdown";
    exec($bin." ".$this->options['cli_options']." --output=\"$this->tempFilePath.html\" \"$this->tempFilePath\"", $output, $retval);
    if ($retval != 0) return "<p>Error! MultiMarkdown conversion returned $retval :</p><pre>".implode("\n", $output)."</pre>";

    $html = file_get_contents("$this->tempFilePath.html");

    // Post-MMD tidy-up
    $pstr = array( "/&amp;(.*?);/" => "&\\1;", );
    $text = preg_replace(array_keys($pstr), $pstr, $text);

    // Append the mdID to footnote anchors
    $matches = array();
    $c = preg_match_all('/([\'"]*#*fn[a-z]*:[0-9]+)[\'"]/i', $html, $matches, PREG_SET_ORDER);
    if ($c !== false && $c > 0) {
      foreach ($matches as $m) {
        $html = str_replace($m[1], $m[1]."-md$this->mdID", $html);
      }
    }

    return Keep($html);
  }

  public function writeTempFile($text = "") {
    if ($text == "") $text = $this->text;

    $n = 0;
    $f = $this->myFarmD."/temp_md/text-".time()."-$n.md";
    if (is_writable(dirname($f))) {
      while (file_exists($f)) {
        $n++;
        $f = $this->myFarmD."/temp_md/text-".time()."-$n.md";
      }
      $this->tempFilePath = $f;
    } else {
      return "<p>Error! not writeable: $f</p>";
    }

    file_put_contents($this->tempFilePath, $text);
  }

  // Removes everything from $str before first <a ... > and after last </a> HTML
  public function onlylink($str) {
    $s = array('<a ', '/a>');
    $r = array('ßa ', '/aß');
    $str = str_replace( $s, $r, $str );
    $str = preg_replace('/^[^ß]*(ßa [^>]+>)/si', '$1', $str, 1);
    $str = preg_replace('/<\/aß[^ß]*$/si', '</aß', $str, 1);
    $str = str_replace( $r, $s, $str );
    return $str;
  }

  public function __destruct() {
    unlink($this->tempFilePath);
    unlink("$this->tempFilePath.html");
  }

  public function __toString() {
    return "Multi Markdown";
  }
}

function MultiMarkdownToHTML($text) {
  $doc = new MultiMarkdownDocument($text);
  $html = $doc->html();
  return $html;
}

Markup("multimarkdown", '<fulltext', "/\(:multimarkdown:\)(.*?)[\n]?\(:multimarkdownend:\)/se", "MultiMarkdownToHTML(PSS('$1'))");


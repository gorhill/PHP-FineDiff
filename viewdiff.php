<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<style type="text/css">
body {margin:0;border:0;padding:0;font:11pt sans-serif}
body > h1 {margin:0 0 0.5em 0;font:2em sans-serif;background-color:#def}
body > div {padding:2px}
p {margin-top:0}
ins {color:green;background:#dfd;text-decoration:none}
del {color:red;background:#fdd;text-decoration:none}
#params {margin:1em 0;font: 14px sans-serif}
.panecontainer > p {margin:0;border:1px solid #bcd;border-bottom:none;padding:1px 3px;background:#def;font:14px sans-serif}
.panecontainer > p + div {margin:0;padding:2px 0 2px 2px;border:1px solid #bcd;border-top:none}
.pane {margin:0;padding:0;border:0;width:100%;min-height:30em;overflow:auto;font:12px monospace}
.diff {color:gray}
</style>
<title>PHP Fine Diff: Online Diff Viewer</title>
<script type="text/javascript">
var _gaq = _gaq || [];
_gaq.push(['_setAccount', 'UA-5586753-2']);
_gaq.push(['_trackPageview']);
(function() {
	var ga = document.createElement('script');
	ga.type = 'text/javascript';
	ga.async = true;
	ga.src = 'http://www.google-analytics.com/ga.js';
	var s = document.getElementsByTagName('script')[0];
	s.parentNode.insertBefore(ga, s);
	})();
</script>
</head>
<body>
<a href="https://github.com/gorhill/PHP-FineDiff"><img style="position:absolute;top:0;right:0;border:0;" src="https://d3nwyuy0nl342s.cloudfront.net/img/7afbc8b248c68eb468279e8c17986ad46549fb71/687474703a2f2f73332e616d617a6f6e6177732e636f6d2f6769746875622f726962626f6e732f666f726b6d655f72696768745f6461726b626c75655f3132313632312e706e67" alt="Fork me on GitHub"></a>
<h1>PHP Fine Diff: Online Diff Viewer</h1>
<div>
<?php
// http://www.php.net/manual/en/function.get-magic-quotes-gpc.php#82524
function stripslashes_deep(&$value) {
	$value = is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
	return $value;
	}
if ( (function_exists("get_magic_quotes_gpc") && get_magic_quotes_gpc()) || (ini_get('magic_quotes_sybase') && strtolower(ini_get('magic_quotes_sybase'))!="off") ) {
	stripslashes_deep($_GET);
	stripslashes_deep($_POST);
	stripslashes_deep($_REQUEST);
	}

include 'finediff.php';

$granularity = 2;
if ( isset($_REQUEST['granularity']) && ctype_digit($_REQUEST['granularity']) ) {
	$granularity = max(min(intval($_REQUEST['granularity']),3),0);
	}

$from_text = '';
$to_text = '';
if ( !empty($_REQUEST['from']) || !empty($_REQUEST['to'])) {
	if ( !empty($_REQUEST['from']) ) {
		$from_text = $_REQUEST['from'];
		}
	if ( !empty($_REQUEST['to']) ) {
		$to_text = $_REQUEST['to'];
		}
	}

$diff = '';

$granularityStacks = array(
	FineDiff::$paragraphGranularity,
	FineDiff::$sentenceGranularity,
	FineDiff::$wordGranularity,
	FineDiff::$characterGranularity
	);

$from_len = strlen($from_text);
$to_len = strlen($to_text);
$start_time = gettimeofday(true);
$diff = new FineDiff($from_text, $to_text, $granularityStacks[$granularity]);
$edits = $diff->getOps();
$exec_time = gettimeofday(true) - $start_time;
$rendered_diff = $diff->renderDiffToHTML();
$rendering_time = gettimeofday(true) - $start_time;
$diff_len = strlen($diff->getOpcodes());
?>
<form action="viewdiff.php" method="post">
<div class="panecontainer" style="display:inline-block;width:49.5%"><p>From</p><div><textarea name="from" class="pane"><?php echo htmlentities($from_text); ?></textarea></div></div>
<div class="panecontainer" style="display:inline-block;width:49.5%"><p>To</p><div><textarea name="to" class="pane"><?php echo htmlentities($to_text); ?></textarea></div></div>
<p id="params">Granularity:<input name="granularity" type="radio" value="0"<?php if ( $granularity === 0 ) { echo ' checked="checked"'; } ?>>&thinsp;Paragraph/lines&ensp;<input name="granularity" type="radio" value="1"<?php if ( $granularity === 1 ) { echo ' checked="checked"'; } ?>>&thinsp;Sentence&ensp;<input name="granularity" type="radio" value="2"<?php if ( $granularity === 2 ) { echo ' checked="checked"'; } ?>>&thinsp;Word&ensp;<input name="granularity" type="radio" value="3"<?php if ( $granularity === 3 ) { echo ' checked="checked"'; } ?>>&thinsp;Character&emsp;<input type="submit" value="View diff">&emsp;<a href="viewdiff.php"><button>Clear all</button></a></p>
</form>
<div class="panecontainer" style="width:99%"><p>Diff <span style="color:gray">(diff: <?php printf('%.3f', $exec_time); ?> sec, rendering: <?php printf('%.3f', $rendering_time); ?> sec, diff len: <?php echo $diff_len; ?> chars)</span></p><div><div class="pane diff" style="white-space:pre-line"><?php
echo $rendered_diff; ?></div></div>
</div>
<p style="margin-top:1em"><a href="viewdiff-ex.php">Go to main page</a></p>
</body>
</html>

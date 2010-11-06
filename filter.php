<?php
/**
 * Timeline Widget filter.
 *   A Moodle filter to embed an MIT SIMILE Timeline Javascript widget.
 *
 * Uses: MIT SIMILE; also, parse_ini_string function (compat.php).
 *
 * @category  Moodle4-9
 * @author    N.D.Freear, 23 October 2010.
 * @copyright Copyright (c) 2010 Nicholas Freear.
 * @license   http://gnu.org/copyleft/gpl.html
 * @link      http://freear.org.uk/moodle/
 *
 * @copyright Copyright (c) Massachusetts Institute of Technology and Contributors 2006-2009 ~ Some rights reserved.
 * @license   http://opensource.org/licenses/bsd-license.php
 * @link      http://simile.mit.edu/
 */
/**
 Usage.
 Type the following in Moodle's rich-editor:

[Timeline]
; A comment.
title  = Important inventions timeline
dataUrl= /moodle/file.php/2/simile-invent.xml
; The date on which to centre the timeline initially. This can
; be just a year, or a full date, eg. 20 January 1870.
date   = 1870
; UPPER-CASE! minute,hour,day,week,month,year,decade,century,millenium.
intervalUnit  = CENTURY
; How wide should the unit defined above be? In pixels.
intervalPixels= 75
[/Timeline]


NOTE. Why the square bracket/INI-file syntax above?
  A good question! I am initially writing this filter for use by teachers who may be fairly non-technical. So, after some deliberation, I chose something that was as 'clean' and readable as possible, could not be confused with HTML and can easily be entered in a WYSIWYG editor in Moodle. The downsides are that line-breaks (which can be <br>, <br />) are required, and if you disable the filter, you're left with 'weird' square brackets.
*/

//  This filter will replace any [Timeline] OPTIONS [/Timeline] with
//  a SIMILE timeline widget.
//
//  To activate this filter, add a line like this to your
//  list of filters in your Filter configuration:
//
//  filter/timelinewidget/filter.php
//
//////////////////////////////////////////////////////////////

/// This is the filtering function itself.  It accepts the
/// courseid and the text to be filtered (in HTML form).

function timelinewidget_filter($courseid, $text) {
    static $filter_count = 0;

    if (!is_string($text) || $filter_count > 3) { #0
        // non string data can not be filtered anyway
        return $text;
    }
    // Copy the input text. Fullclone is slow and not needed here
    $newtext = $text;

    $filter_count++;

    $search  = "#\[Timeline\](.*?)\[\/?Timeline\]#ims";
    $newtext = preg_replace_callback($search, '_timeline_filter_callback', $newtext);

    if (is_null($newtext) or $newtext === $text) {
        // error or not filtered
        return $text;
    }

    return $newtext;
}


function _timeline_filter_callback($matches_ini) {
    global $CFG;

    $intervals = 'minute,hour,day,week,month,year,decade,century,millenium';
    $intervals = strtoupper(str_replace(',', '|', $intervals));

    $defaults = array('id'=>'tl', 'title'=>'My timeline',);

    // Tidy up after WYSIWYG editors - line breaks matter.
    $config = trim(str_ireplace(array('<br>', '<br />'), "\n", $matches_ini[1]));

    // For PHP < 5.3, do late loading of this compatibility library.
    if (!function_exists('parse_ini_string')) {
        require_once($CFG->libdir.'/../filter/timelinewidget/compat.php');
    }

    $config = parse_ini_string($config);
    $config = (object) array_merge($defaults, $config);

    // We probably should check types here too.

    if (!isset($config->date)) {
        echo "Error, missing 'date'";
    }

    // Problems with require_js and caching :( - hard-code YUI scripts below.
    //require_js(array('yui_yahoo', 'yui_event'));

    $yui_root= "$CFG->wwwroot/lib/yui";
    $tl_root = "$CFG->wwwroot/filter/timelinewidget";

    $js_load = $alt_link = NULL;
    if (isset($config->dataUrl)) { //XML.
        $js_load = <<<EOS
    tl.loadXML("$config->dataUrl?"+ (new Date().getTime()),
            function(xml, url) { eventSource.loadXML(xml, url); });
EOS;
        $alt_link = <<<EOS
    <p class="tl-widget-alt xml" style="background:url($tl_root/small-orange-xml.gif)no-repeat; padding-left:38px;"><a href="$config->dataUrl" type="application/xml" title="XML timeline data">$config->title<abbr class="accesshide"> XML</abbr></a></p>
EOS;
    }
    elseif (isset($config->dataId)) { //JSON.
        $js_load = <<<EOS
    tl.loadJSON("$tl_root/json.php?mid=$config->dataId&r="+ (new Date().getTime()),
            function(json, url) { eventSource.loadJSON(json, url); });
EOS;
        $alt_link = <<<EOS
    <p class="tl-widget-alt mod-data" style="background:url($CFG->wwwroot/mod/data/icon.gif)no-repeat; padding-left:28px;"><a href=
    "$CFG->wwwroot/mod/data/view.php?d=$config->dataId"  title="Data source">$config->title</a></p>
EOS;
    } else {
        echo "Error, either 'dataUrl' (XML) or 'dataId' (JSON) is required.";
    }

    // For now, we embed the Javascript inline. 
/*<style>
 .simileAjax-bubble-container{width:350px !important;}
 .simileAjax-bubble-container h3{font-size:.95em; font-weight:normal;}
 .simileAjax-bubble-contentContainer, .timeline-event-bubble-body{width:350px !important;}
 .timeline-event-bubble-image{position:relative; left:80px !important;}
</style>*/
    $newtext = <<<EOF

<script type="text/javascript">
var Timeline_ajax_url ="$tl_root/timeline_ajax/simile-ajax-api.js";
var Timeline_urlPrefix="$tl_root/timeline_js/";
var Timeline_parameters="bundle=true";
</script>
<script src="$tl_root/timeline_js/timeline-api.js" type="text/javascript"></script>
<script src="$yui_root/yahoo/yahoo-min.js" type="text/javascript"></script>
<script src="$yui_root/event/event-min.js" type="text/javascript"></script>
<script type="text/javascript">
var tl;
function onLoad() {
   var eventSource = new Timeline.DefaultEventSource();
   var d = Timeline.DateTime.parseGregorianDateTime("$config->date");
   var bandInfos = [
     Timeline.createBandInfo({
         eventSource:    eventSource,
         date:           d,
         width:          "100%", //"70%", 
         intervalUnit:   Timeline.DateTime.$config->intervalUnit, 
         intervalPixels: $config->intervalPixels
     }),
   ];

    tl = Timeline.create(document.getElementById("$config->id"), bandInfos);
$js_load

}
var resizeTimerID = null;
function onResize() {
  //alert('resize');
    if (resizeTimerID == null) {
        resizeTimerID = window.setTimeout(function() {

        resizeTimerID = null;
        tl.layout();
        }, 500); //milliseconds.
    }
}

YAHOO.util.Event.onDOMReady(window.setTimeout(onLoad, 500));
//window.onload = onLoad;
window.onresize = onResize;
</script>

<div id="$config->id" class="timeline-default" style="height:250px; border:1px solid #ccc"></div>

$alt_link

EOF;
    return $newtext;
}

#End.

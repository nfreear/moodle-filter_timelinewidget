<?php
/**
 * Timeline Widget filter.
 *   A Moodle filter to embed a MIT SIMILE Timeline Javascript widget.
 *
 * (NOTE, there is a problem with the require_js(yahoo_yui,..) call below - caching :(.)
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
  Usage:

Type the following in Moodle's rich-editor (note, line-breaks, which can be represented by <br /> are required):

[Timeline]
; A comment.
title  = Important inventions timeline
dataUrl= ../similie-invent.xml
; The date on which to centre the timeline initially. This can
; be just a year, or a full date, eg. 20 January 1870.
date   = 1870
; UPPER-CASE! minute,hour,day,week,month,year,decade,century,millenium.
intervalUnit  = CENTURY
; How wide should the unit defined above be? In pixels.
intervalPixels= 75
[/Timeline]

OR

[Timeline]
; Alternatively, get data from Moodle.
dataSrc= mod/data
dataId = 4
wikiUrl= mod/forum/discuss.php?d=2
date   = 1870
intervalUnit  = CENTURY
intervalPixels= 75
[/Timeline]

Tested with Moodle 1.9.7.

NOTE 1. This creates a very simple timeline, with only one band!

NOTE 2. Why the square bracket/INI-file syntax above?
  A good question! I am initially writing this filter for use by teachers who may be fairly non-technical. So, after some deliberation, I chose something that was as 'clean' and readable as possible, could not be confused with HTML and can easily be entered in a WYSIWYG editor in Moodle. The downsides are that line-breaks (which can be <br>, <br />) are required, and if you disable the filter, you're left with 'weird' square brackets.
*/

//  This filter will replace any [Timeline] OPTIONS [/Timeline] with
//  a SIMILE timeline widget.
//
//  To activate this filter, add a line like this to your
//  list of filters in your Filter configuration:
//
//  filter/simile/filter.php
//
//////////////////////////////////////////////////////////////

#require_once($CFG->libdir.'/weblib.php');

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
    
    if (!isset($config->dataUrl)) {
        echo "Error, missing 'dataUrl'";
    }
    if (!isset($config->date)) {
        echo "Error, missing 'date'";
    }
    if (!isset($config->date)) {
        echo "Error, missing 'date'";
    }

    //
    // Oh dear! Big problems with caching :((
    //
    require_js(array('yui_yahoo', 'yui_event'));

    $root = "$CFG->wwwroot/filter/timelinewidget";

    // For now, we embed the Javascript inline. 
    $newtext = <<<EOF

<script type="text/javascript">
var Timeline_ajax_url ="$root/timeline_ajax/simile-ajax-api.js";
var Timeline_urlPrefix="$root/timeline_js/";       
var Timeline_parameters="bundle=true";
</script>
<script src="$root/timeline_js/timeline-api.js" type="text/javascript"></script>
<script>
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
   Timeline.loadXML("$config->dataUrl", function(xml, url) { eventSource.loadXML(xml, url); });
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

<a href="$config->dataUrl" type="application/xml" title="XML">$config->title timeline<span> XML</span></a>

<div id="$config->id" class="timeline-default" style="height:250px; border:1px solid #ccc"></div>

EOF;
    return $newtext;
}


#End.

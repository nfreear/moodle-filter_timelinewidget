<!-- -*- markdown -*- -->

Timeline Widget filter
======================

A Moodle filter to embed an MIT SIMILE Timeline Javascript interactive widget.
You and your class can use it to visualize temporal/ historical data. The data
source can be a static XML file, or a dynamic Moodle Database activity.

Requirements: tested with Moodle 1.9.7 (all Moodle 1.9.x should work, Moodle 2.x may work- let me know).

Uses:  MIT SIMILE (v2.3.0); Javascript; also, `parse_ini_string` function (see compat.php).

Installation
------------ 
1. Download and uncompress the code files. Copy the `timelinewidget` directory to the `filter` directory on the server,
   eg. `/var/www/moodle/filter/timelinewidget/`
2. Log in to Moodle as admin, visit Site Administration | Modules | Filters |
 Manage Filters. Scroll down and click on the icon for Timelinewidget to enable it.
3. Try one of the Usage examples below.

Usage
-----
Try one of the two modes:

1. **XML**: Upload a timeline XML file to your course (see the `example` directory).
Then, type the following in a resource using Moodle's rich-editor (note,
line-breaks, which can be represented by `<br />` are required). Substitute an
appropriate integer in place of `COURSE_ID` and adjust the other parameters as needed. Minimal syntax:

        [Timeline]
        dateUrl = COURSE_ID/simile-invent.xml
        [/Timeline]

You will probably wish to replace the defaults for `title`, `date` and so on:

        [Timeline]
        ; A comment.
        title  = Important inventions
        dataUrl= COURSE_ID/simile-invent.xml
        ; The date on which to centre the timeline initially. This can
        ; be just a year, or a full date, eg. 20 January 1870.
        date   = 1870
        ; UPPER-CASE! minute,hour,day,week,month,year,decade,century,millenium.
        intervalUnit  = CENTURY
        ; How wide should the unit defined above be? In pixels.
        intervalPixels= 75
        [/Timeline]

2. **mod/data**: Create a Moodle Database activity, with fields named 'start'
(date), 'end' (date), 'title', 'description', 'image' and 'link', and add a record.
Then, type the following in a Moodle resource, substituting an appropriate
integer in place of `DATA_ID` (`mod/data/view.php?d=N`):

        [Timeline]
        ;title = Important inventions
        dataSrc= mod/data
        dataId = DATA_ID
        date   = 1870
        intervalUnit  = CENTURY
        intervalPixels= 75
        [/Timeline]

Notes
-----
* Todo: test that the filter works with Moodle 2.0.
* mod/data: If the start and end dates for a timeline will be less than ~2000
 (or > ?), then they need to be text, not date fields in the Database activity.
* mod/data: If PHP < 5.2, then dates less than ~1970 should be written as 
 ISO 8601 dates (2010-11-06T14:11..). If PHP >= 5.2 then date_parse handles
 '6 November 2010...'.
* The filter currently creates a very simple timeline, with only one band!
* There is a skip-link for accessibility.
* The filter is internationalized and (mostly) Moodle 2 ready.

Credits
-------
Filter. Copyright (c) 2010-2011 Nicholas Freear.

*  License <http://gnu.org/copyleft/gpl.html>
*  <http://freear.org.uk/#moodle>

SIMILE. Copyright (c) Massachusetts Institute of Technology and Contributors 2006-2009 ~ Some rights reserved.

*  License <http://opensource.org/licenses/bsd-license.php>
*  <http://simile.mit.edu/>

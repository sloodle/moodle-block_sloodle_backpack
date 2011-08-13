<?php

/**
* Defines the Sloodle menu block class.
* 
* @package sloodle
* @contributor Paul Preibisch
* @contributor Edmund Edgar
*/

/** Include the current Sloodle configuration, if possible. */
@include_once($CFG->dirroot .'/mod/sloodle/sl_config.php');
if (defined('SLOODLE_LIBROOT')) {
    /** Inlcude the current general Sloodle functionality. */
    require_once(SLOODLE_LIBROOT.'/general.php');
    /** Include the Sloodle course functionality. */
    require_once(SLOODLE_LIBROOT.'/course.php');
}


/** Define the Sloodle Menu Block version. */
define('BACKPACKS_VERSION', 1.0);

/**
* Defines the block class.
* @package sloodle
*/
class block_sloodle_backpack extends block_base {

    /**
    * Perform block initialisation.
    * @return void
    */
    function init() {
        global $CFG;
        
        $this->title = get_string('blockname', 'block_sloodle_backpack');
        $this->content_type = BLOCK_TYPE_TEXT;
        $this->version = 2011070900;
    }
    
    /**
    * Indicates whether or not this module has a global configuration page.
    * @return bool True if there is a global configuration page, or false otherwise.
    */
    function has_config() {
        return false;
    }
    
    /**
    * Indicates whether or not to hide the header of this block.
    * @return bool True to hide the header, or false to show it.
    */
    function hide_header() {
        return false;
    }

    /**
    * Defines *and* returns the content of this block.
    * @return object
    */
    function get_content() {
        global $CFG, $COURSE, $USER;
        
        // Construct the content
        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';
        
        
        
        // If the user is not logged in or if they are using guest access, then we can't show anything
        if (!isloggedin() || isguest()) {
            return $this->content;
        }
        
        // Get the context instance for this course
        $course_context = get_context_instance(CONTEXT_COURSE, $COURSE->id);
        
       
        // Get the Sloodle course data
        $sloodle_course = new SloodleCourse();
        if (!$sloodle_course->load((int)$COURSE->id)) {
            $this->content->text = get_string('failedloadcourse', 'block_sloodle_backpack');
            return $this->content;
        }
        
        
        // Attempt to find a Sloodle user for the Moodle user
        $dbquery = "    SELECT * FROM {$CFG->prefix}sloodle_users
                        WHERE userid = ? AND NOT (avname = '' AND uuid = '')
                    ";
        $dbresult = sloodle_get_records_sql_params($dbquery,array($USER->id));
        $sl_avatar_name = "";
        if (!is_array($dbresult) || count($dbresult) == 0) $userresult = FALSE;
        else if (count($dbresult) > 1) $userresult = "Multiple avatars associated with your Moodle account.";
        else {
            $userresult = TRUE;
            reset($dbresult);
            $cur = current($dbresult);
            $sl_avatar_name = $cur->avname;
        }
        
        if ($userresult === TRUE) {
            // Success
            // Make sure there was a name
            if (empty($sl_avatar_name)) $sl_avatar_name = '('.get_string('backpacks:nameunknown', 'sloodle').')';
            
            
            
        } else if (!is_string($userresult)) {
            // No avatar linked yet
            $this->content->text .= '<center><span style="font-style:italic;">('.get_string('backpacks:noavatar', 'sloodle').')</span></center>';
        }
        
        //retreive all currencies and user info
        $this->content->text .= '<div>';
        $sql = "select p.currencyid as currencyid, sum(p.amount) as balance, c.name, c.imageurl as imageurl from {$CFG->prefix}sloodle_award_points p inner join {$CFG->prefix}sloodle_currency_types c on p.currencyid=c.id  inner join {$CFG->prefix}sloodle_award_rounds r on p.roundid=r.id  where p.userid=? AND r.courseid=?  group by p.currencyid ORDER BY c.displayorder DESC";
        $items = sloodle_get_records_sql_params($sql, array($USER->id, $COURSE->id));
        foreach ($items as $item){
            $this->content->text .= "<div>";
            if ($item->imageurl===null)  {
                $this->content->text .= "<img src=\"".SLOODLE_WWWROOT."/lib/media/blank16.png\"  width=\"16\" height=\"16\" >";
            }else {
                $this->content->text .= "<img src=\"{$item->imageurl}\" width=\"16\" height=\"16\" >";    
            }
            $this->content->text .= "&nbsp{$item->name}";
            $this->content->text .= '<span style="float:right">';
            $this->content->text .= "{$item->balance}";
            $this->content->text .= '</span>';
            $this->content->text .= "</div>";
            
        }
        
        $this->content->text .= '</div>';
          $this->content->text .= "<br>";
        return $this->content;
    }
}

?>

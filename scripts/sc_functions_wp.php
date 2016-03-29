<?php
/**
 * WordPress Shell Functions for Softcatalà project
 * Important: this script has to be placed in the WordPress base directory (where index.php is)
 */

require( dirname( __FILE__ ) . '/wp-blog-header.php' );

/**
 * WordPress Shell Functions for Softcatalà project
 *
 * @package     wp-softcatala
 * @author      Softcatalà Team <web@softcatala.org>
 */
class WordPress_Shell_SC_Functions
{
    /**
     * Input arguments
     *
     * @var array
     */
    protected $_args        = array();
    
    /**
     * Initialize application and parse input parameters
     *
     */
    public function init()
    {
        $this->_parseArgs();
    }
    
    /**
     * Run main function
     *
     * @return WordPress_Shell_SC_Functions
     */
    public function run()
    {
        $this->init();
        if ($action = $this->getArg('action')) {
            switch ($action) {
                case 'remove_orphan_images':
                    $this->remove_orphan_images();
                    break;
                default:
                    echo $this->usageHelp();
                    break;
            }
        } else {
            echo $this->usageHelp();
        }
    }

    /**
     * Removes images with a parent post id which doesn't exist anymore
     */
    protected function remove_orphan_images()
    {
        global $wpdb;

        $imagesquery = "SELECT * FROM $wpdb->posts
                WHERE $wpdb->posts.post_type = 'attachment'
                AND $wpdb->posts.post_mime_type LIKE 'image%'
                ";

        $result = $wpdb->get_results($imagesquery);
        foreach ($result as $post) {
            setup_postdata($post);
            $attachmentid = $post->ID;
            $parentid = $post->post_parent;

            $idquery = "SELECT ID FROM $wpdb->posts WHERE ID = $parentid";
            $result2 = $wpdb->get_results($idquery);

            if( ! isset( $result2[0]->ID ) && $parentid == '0') {
                $delete_result = wp_delete_attachment( $attachmentid, true );

                if(! is_wp_error($delete_result)) {
                    echo 'Removed Attachment ID: '. $attachmentid. ' || Post ID: ' . $parentid . " || Existeix: " . $result2[0]->ID ."\n";
                } else {
                    echo 'Couldn\'t removed attachment ID: '. $attachmentid."\n";
                }
            }
        }
    }
    
    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php sc_functions.php -- [options]

  --action <action>            Executes one of the predefined actions

  <action>     Possible actions: export_fields (throws a xml), import_fields (requires --file argument with xml path)

USAGE;
    }
    
    /**
     * Retrieve argument value by name or false
     *
     * @param string $name the argument name
     * @return mixed
     */
    public function getArg($name)
    {
        if (isset($this->_args[$name])) {
            return $this->_args[$name];
        }
        return false;
    }
    
    /**
     * Parse input arguments
     *
     * @return Mage_Shell_Abstract
     */
    protected function _parseArgs()
    {
        $current = null;
        foreach ($_SERVER['argv'] as $arg) {
            $match = array();
            if (preg_match('#^--([\w\d_-]{1,})$#', $arg, $match) || preg_match('#^-([\w\d_]{1,})$#', $arg, $match)) {
                $current = $match[1];
                $this->_args[$current] = true;
            } else {
                if ($current) {
                    $this->_args[$current] = $arg;
                } else if (preg_match('#^([\w\d_]{1,})$#', $arg, $match)) {
                    $this->_args[$match[1]] = true;
                }
            }
        }
        return $this;
    }
}

$shell = new WordPress_Shell_SC_Functions();
$shell->run();

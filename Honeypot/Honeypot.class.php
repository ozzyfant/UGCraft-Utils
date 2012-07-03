<?php

/**
 * @author Janek Ostendorf (ozzy) <ozzy2345de@gmail.com>
 * @copyright Copyright (c) Janek Ostendorf
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3
 * @package UGCraft-Utils
 */

class Honeypot {
    
    /**
     * Handler for the log file
     * @var array
     */
    private $file;
    
    /**
     * Output buffer
     * @var string
     */
    private $output;
    
    /**
     * Configuration
     * @var HPConfig
     */
    private $config;

    /**
     * Reads the logfile and prepares the output
     * @param string $logfile Path to the Honeypot logfile
     * @param HPConfig $config Configuration
     */
    public function __construct(HPConfig $config) {
        
        $this->config = $config;
        
        $this->file = file($this->config->logfile, FILE_SKIP_EMPTY_LINES & FILE_IGNORE_NEW_LINES);
        
        if($this->file === false) {
            
            die('Error reading the logfile!');
            
        }
        
        $this->generateTable();
        
    }
    
    /**
     * Generates the output 
     */
    private function generateTable() {
        
        // Table head
        $this->output = '
<html>
    <head>
        <title>'.$this->config->page_name.'</title>
    </head>
    <body>
        <table style="width: 100%;">
            <thead>
                <tr class="header">
                    <th>Time</th>
                    <th>Player</th>
                    <th>Action</th>
                    <th>Coordinates</th>
                    <th>Count</th>
                </tr>
            </thead>
            <tbody>';
        
        //[2012-07-02 17:39:51] player Forgotten_Blade broke HoneyPot block at {world=levana, x=18, y=26, z=-6}, break count/points: 3 (blockId 57: DIAMOND_BLOCK)
        //        0         1     2         3            4        5     6    7     8           9     10    11      12    13          14 15      16     17
        
        foreach($this->file as $line) {
            
            $tmp = array();
            $tmp = explode(' ', $line);
            
            // Time stuff
            $time = substr($tmp[0].' '.$tmp[1], 1, strlen($tmp[0].' '.$tmp[1]) - 2);
            
            // Player name
            $player = $tmp[3];
            
            // Counting, no ban
            if($tmp[4] == 'broke' && $tmp[5] == 'HoneyPot') {
                
                $action = 'Breaking HoneyPot';
                
                // Coords
                $world = substr($tmp[8], 7, strlen($tmp[8]) - 8);
                $x = substr($tmp[9], 2, strlen($tmp[9] - 3));
                $y = substr($tmp[10], 2, strlen($tmp[10] - 3));
                $z = substr($tmp[11], 2, strlen($tmp[11] - 4));
                
                // Count
                $count = $tmp[14];
                
            }
            else {
                
                $action = 'Banned';
                $world = $x = $y = $z = false;
                $count = false;
                
            }
            
            $this->output .= "<tr><td>".$time."</td><td>".$player."</td><td>".$action."</td><td>".$world !== false ? $world.' ('.$x.', '.$y.','.$z.')' : '&ndash;'."</td><td>".$count !== false ? $count : '&ndash;'."</td></tr>";
            
            
        }
        
        // Table end
        $this->output .= '
            </tbody>
        </table>
    </body>
</html>';
        
    }
    
    /**
     * Prints out table
     * @return type 
     */
    public function printTable() {
        
        echo $this->output;
        
    }
    
}

class HPConfig {
    
    /**
     * Name of the page
     * @var string
     */
    public $page_name;
    
    /**
     * Path to the HP logfile
     * @var string
     */
    public $logfile;
    
}

?>

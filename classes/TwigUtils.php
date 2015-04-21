<?php namespace CosmicRadioTV\Podcast\Classes;

class TwigUtils {
	   
	/**
	 * Turns seconds into a time display, removes trailing 0's
	 * @param  integer $seconds Time in seconds
	 * @return string          Time display
	 */
	public static function sectotime($seconds) {
		$s = [];
        if ($seconds >= 3600) {
                $s[] = floor($seconds / 3600);
                $seconds %= 3600;
                $s[] = sprintf('%02d',floor($seconds / 60));
        		$s[] = sprintf('%02d',floor($seconds % 60));
        } else {
        	    $s[] = floor($seconds / 60);
        		$s[] = sprintf('%02d',floor($seconds % 60));
        }
        //return print_r($s,true);

        return join(':', $s);
	}
}
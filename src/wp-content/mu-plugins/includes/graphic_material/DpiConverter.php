<?php

/**
 * Class to simulate the dpi conversion
 * for images in pixels.
 */
class DpiConverter {
    /**
     * The param $convert should be true for images used in graphics that will be 
     * printed (flyers) and it should be false for images used only inside the site
     * like the header. 
     *
     * @param bool $convert if set to false skip the converstion
     */
    public function __construct($convert = true) {
        $this->convert = $convert;
    }
    
    /**
     * Receives a value in pixels assuming it is 300 dpi and convert it
     * to the corresponding pixel value in 75 dpi.
     * 
     * @param int $value
     * @return int
     */
    public function maybeConvertTo75Dpi($value)
    {
        if ($this->convert) {
            return $value / 4;
        } else {
            return $value;
        }
    }
    
    /**
     * Receives a value in pixels assuming it is 75 dpi and convert it
     * to the corresponding pixel value in 300 dpi.
     * 
     * @param int $value
     * @return int
     */
    public function maybeConvertTo300Dpi($value)
    {
        if ($this->convert) {
            return $value * 4;
        } else {
            return $value;
        }
    }
}

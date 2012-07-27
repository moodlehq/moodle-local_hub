<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    moodle-dot-org
 * @subpackage   stats
 * @copyright 2009 Sam Hemelryk <sam@hemelryk.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/** Displays the chart legends horizontally at the bottom */
define('GOOGLE_CHARTS_LEGEND_HBOTTOM',0);
/** Displays the chart legends horizontally at the top */
define('GOOGLE_CHARTS_LEGEND_HTOP',1);
/** Displays the chart legends vertically at the bottom */
define('GOOGLE_CHARTS_LEGEND_VBOTTOM',2);
/** Displays the chart legends vertically at the top */
define('GOOGLE_CHARTS_LEGEND_VTOP',3);
/** Displays the chart legends vertically to the right */
define('GOOGLE_CHARTS_LEGEND_RIGHT',4);
/** Displays the chart legends vertically to the left */
define('GOOGLE_CHARTS_LEGEND_LEFT',5);

/** Stacked horizontal bar graph */
define('GOOGLE_CHARTS_BAR_HORIZONTAL_STACKED',0);
/** Stacked vertical bar graph */
define('GOOGLE_CHARTS_BAR_VERTICAL_STACKED',1);
/** Grouped horizontal bar graph */
define('GOOGLE_CHARTS_BAR_HORIZONTAL_GROUPED',2);
/** Grouped vertical bar graph */
define('GOOGLE_CHARTS_BAR_VERTICAL_GROUPED',3);

/** Top value is combined primary and secondary */
define('GOOGLE_CHARTS_COMBINED_TOP_VALUE', 0);
/** Top value for primary range */
define('GOOGLE_CHARTS_PRIMARY_TOP_VALUE', 1);
/** Top value for secondary range */
define('GOOGLE_CHARTS_SECONDARY_TOP_VALUE', 2);

/**
 * Google Charts Map Graph class
 *
 * @package   moodle-dot-org
 * @subpackage   stats
 * @copyright 2009 Sam Hemelryk <sam@hemelryk.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class google_charts_map_graph extends graph {
    /** @var array An array of country codes ISO3166 */
    private $countries = Array();
    /** @var array An array of percentages to match the countries */
    private $percentages = Array();
    /** @var int The width of the map, 440 is the max */
    protected $imagewidth = 440;
    /** @var int The height of the map, 220 is the max */
    protected $imageheight = 220;
    /** @var string Sets the continent to focus on */
    protected $chartstyle = 'world';
    /** @var string Sets the default colour for the map */
    protected $defaultcolor = 'FFFFFF';

    public function set_continent($continent) {
        $possibilities = Array('africa','asia','europe','middle_east','south_america','usa','world');
        if (in_array($continent, $possibilities)) {
                $this->chartstyle = $continent;
                return true;
        }
        return false;
    }

    public function set_default_colour($colour) {
        if (preg_match('/^[a-fA-F0-9]{6}$/', $colour)) {
            $this->defaultcolor = $colour;
            return true;
        }
        return false;
    }

    /**
     * Expects two parameters!
     * @deprecated
     */
    public function add_value($country /*, $value*/) {
        return $this->add_country_value($country, func_get_arg(1));
    }

    /**
     * Expects two parameters!
     * @deprecated
     */
    public function add_values(array $xvalues /*, $countrykey, $countkey*/) {
        return $this->add_country_values($xvalues, func_get_arg(1), func_get_arg(2))
    }

    /**
     * Add a country and value to the map
     *
     * @param string $country A two letter country code ISO3166
     * @param string $value The number to associated with this country
     * @return bool
     */
    public function add_country_value($country, $value) {
        if (strlen($country)===2) {
            $this->countries[] =strtoupper($country);
            $this->percentages[] = $value;
            return true;
        }
        return false;
    }

    /**
     * Adds an array of countries and values to the map
     *
     * @param array $xvalues An array of country/value arrays to add
     * @param string|int $countrykey The country code key for the data array
     * @param string|int $countkey The count code key for the data array
     * @return int A count of the successfully added country/value pairs
     */
    public function add_country_values(array $xvalues, $countrykey, $countkey) {
        $count = 0;
        foreach ($xvalues as $xvalue) {
            $outcome = $this->add_country_value($xvalue[$countrykey], $xvalue[$countkey]);
            if ($outcome) $count++;
        }
        return $count;
    }
    /**
     * Converts the country values into percentages to display correctly
     */
    private function calculate_percentages() {
        $top = 0;
        foreach ($this->percentages as $value) {
            if ($value>$top) {
                $top = $value;
            }
        }
        foreach ($this->percentages as $key=>$value) {
            $percentage = round(($value/$top)*100,0);
            $this->percentages[$key] = $percentage;
        }
    }
    /**
     * Generates the URL required to request the chart from google
     * 
     * @return bool
     */
    public function generate_url() {
        $this->calculate_percentages();
        $urlbits = Array();
        $urlbits[] = 'cht=t';
        $urlbits[] = 'chtm='.$this->chartstyle;
        $urlbits[] = 'chco='.$this->defaultcolor.',FFE2BB,F68E00';
        $urlbits[] = 'chf=bg,s,FFFFFF';
        if ($this->usecharttitle) {
            $urlbits[] = 'chtt='.$this->charttitle;
            $urlbits[] = 'chts='.$this->charttitlecolour.','.$this->charttitlefontsize;
        }
        $urlbits[] = 'chs='.$this->imagewidth.'x'.$this->imageheight;
        $urlbits[] = 'chld='.join('',$this->countries);
        $urlbits[] = 'chd=t:'.join(',',$this->percentages);
        $url  = 'http://chart.apis.google.com/chart?'.join('&', $urlbits);
        $this->url = $url;
        return true;
    }
    /**
     * Overload the toString function so that we can display the graph
     *
     * @return string The webpath for the graph
     */
    public function __toString() {
        global $CFG;
        if (!$this->forcegeneration && file_exists($this->filepath.$this->filename)) {
            return $CFG->wwwroot.$this->webpath.$this->filename;
        }
        $this->generate_url();
        $cacheoutcome = cache_google_charts_image($this->url, $this->filepath.$this->filename);
        if ($cacheoutcome===false) {
            $this->filename = parent::find_latest_cached_graph();
        }
        return $CFG->wwwroot.$this->webpath.$this->filename;
    }

}

/**
 * Google Charts Scatter Graph class
 *
 * @package   moodle-dot-org
 * @subpackage   stats
 * @copyright 2009 Sam Hemelryk <sam@hemelryk.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class google_scatter_plot_graph extends scatter_plot_graph {
    /** @var string The style for the graph in this case only s is possible */
    private $style = 's';
    /** @var string The url to call to generate the graph */
    private $url = null;
    /** @var array The possible legend positions */
    private $legendpositions = Array(
        'b', // Bottom Horizontal
        't', // Top Horizontal
        'bv', // Bottom Veritcal
        'tv', // Top Vertical
        'r', // Right Veritcal
        'l'); // Left Vertical
    /** @var string the legend position to use */
    private $legendposition = 'b';
    /**
     * Sets the position of the legend on the chart
     *
     * @uses GOOGLE_CHARTS_LEGEND_HBOTTOM
     * @uses GOOGLE_CHARTS_LEGEND_HTOP
     * @uses GOOGLE_CHARTS_LEGEND_VBOTTOM
     * @uses GOOGLE_CHARTS_LEGEND_VTOP
     * @uses GOOGLE_CHARTS_LEGEND_RIGHT
     * @uses GOOGLE_CHARTS_LEGEND_LEFT
     * @param int $position
     * @return bool
     */
    public function set_legend_position($position) {
        if (array_key_exists($position, $this->legendpositions)) {
            $this->legendposition = $this->legendpositions[$position];
            return true;
        }
        return false;
    }
    /**
     * Generates the URL required to request the chart from google
     *
     * @return bool
     */
    public function generate_url() {
        $topvalue = $this->get_top_value();
        $topvalue = logical_top_point($topvalue, $this->logicaltopdefinition);

        $urlbits = Array();
        $urlbits[] = 'cht='.$this->style;
        if ($this->usecharttitle) {
            $urlbits[] = 'chtt='.$this->charttitle;
            $urlbits[] = 'chts='.$this->charttitlecolour.','.$this->charttitlefontsize;
        }
        $urlbits[] = 'chs='.$this->imagewidth.'x'.$this->imageheight;
        $urlbits[] = 'chbh=a';
        $urlbits[] = 'chxr=1,'.$this->ybottom.','.$topvalue;
        $urlbits[] = 'chco='.$this->pointcolour;
        if (count($this->legends)>0) {
            $urlbits[] = 'chdlp='.$this->legendposition;
            $urlbits[] = 'chdl='.join('|', $this->legends);
        }
        $valuestring = 'chd=t:'.join(',', $this->x_point_array()).'|'.join(',', $this->y_point_array());
        $urlbits[] = $valuestring;
        $urlbits[] = $labels;
        $url  = 'http://chart.apis.google.com/chart?'.join('&', $urlbits);
        $this->url = $url;
        return true;
    }
    /**
     * Overload the toString function so that we can display the graph
     *
     * @return string The webpath for the graph
     */
    public function __toString() {
        global $CFG;
        if (!$this->forcegeneration && file_exists($this->filepath.$this->filename)) {
            return $CFG->wwwroot.$this->webpath.$this->filename;
        }
        $this->generate_url();
        $cacheoutcome = cache_google_charts_image($this->url, $this->filepath.$this->filename);
        if ($cacheoutcome===false) {
            $this->filename = parent::find_latest_cached_graph();
        }
        return $CFG->wwwroot.$this->webpath.$this->filename;
    }
}

/**
 * Google charts pie graph class
 *
 * @package   moodle-dot-org
 * @subpackage   stats
 * @copyright 2009 Sam Hemelryk <sam@hemelryk.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class google_charts_pie_graph extends pie_graph {
    /** @var array Possible styles for the pie graph */
    private $possiblestyles = Array(
        'p',   // Standard Pie chart
        'p3',  // Three Dimensional Pie chart
        'pc'); // Concentric pie charts
    /** @var string The style to use for the pie graph */
    private $style = 'p';
    /** @var string The url to curl to get the chart */
    private $url = null;
    /** @var int|null The orientation (if any) to use for the chart */
    private $orientation = null;
    /** @var int The width of the image */
    protected $imagewidth = 400;
    /** @var int The height of the image */
    protected $imageheight = 300;
    /**
     * Sets the style for the pie graph
     *
     * @param int $style The style [p|p3|pc]
     * @return bool For success
     */
    public function set_style($style) {
        if (in_array($style, $this->possiblestyles)) {
            $this->style = $style;
            return true;
        } else {
            return false;
        }
    }
    /**
     * Sets the orientation for the poe graph
     *
     * @param float $float The orientation for the graph
     * @return bool For success
     */
    public function set_orientation($float) {
        if (is_float($float)) {
            $this->orientation = $float;
            return true;
        }
        return false;
    }
    /**
     * Generates the URL required to request the chart from google
     *
     * @return bool
     */
    public function generate_url() {
        $this->set_percentages();
        $valuesets = Array(1=>Array());
        $coloursets = $this->coloursets;
        $labelsets = Array(1=>Array());
        $multipledatasets = false;
        if (count($this->datasets)>1) {
            $multipledatasets = true;
        }
        foreach ($this->datasets as $key=>$dataset) {
            if (!array_key_exists($key, $valuesets)) {
                $valuesets[$key] = Array();
            }
            foreach ($dataset as $data) {
                $valuesets[$key][] = $data['value'];
                $labelsets[$key][] = $data['label'];
                if ($data['colour']!==null) $coloursets[$key][] = $data['colour'];
            }
        }
        $urlbits = Array();
        $urlbits[] = 'cht='.$this->style;
        $urlbits[] = 'chs='.$this->imagewidth.'x'.$this->imageheight;
        if ($this->orientation!==null) {
            $urlbits[] = 'chp='.(string)$this->orientation;
        }
        if ($this->usecharttitle) {
            $urlbits[] = 'chtt='.$this->charttitle;
            $urlbits[] = 'chts='.$this->charttitlecolour.','.$this->charttitlefontsize;
        }
        $colours = array();
        foreach ($coloursets as $colourset) {
            $colours[] = join(',', $colourset);
        }
        $urlbits[] = 'chco='.join('|', $colours);
        if ($multipledatasets) {
            $values = Array();
            $labels = Array();
            $count = 0;
            foreach ($labelsets as $labelarray) {
                $labels[] = join('|', $labelarray);
                $count++;
            }
            foreach ($valuesets as $valuearray) {
                $values[] = join(',', $valuearray);
            }
            $urlbits[] = 'chl='.join('|', $labels);
            $urlbits[] = 'chd=t:'.join('|', $values);
        } else {
            $urlbits[] = 'chl='.join('|', $labelsets[1]);
            $urlbits[] = 'chd=t:'.join(',', $valuesets[1]);
        }
        $url  = 'http://chart.apis.google.com/chart?'.join('&', $urlbits);
        $this->url = $url;
        return true;
    }
    /**
     * Overload the toString function so that we can display the graph
     *
     * @return string The webpath for the graph
     */
    public function __toString() {
        global $CFG;
        if (!$this->forcegeneration && file_exists($this->filepath.$this->filename)) {
            return $CFG->wwwroot.$this->webpath.$this->filename;
        }
        $this->generate_url();
        $cacheoutcome = cache_google_charts_image($this->url, $this->filepath.$this->filename);
        if ($cacheoutcome===false) {
            $this->filename = parent::find_latest_cached_graph();
        }
        return $CFG->wwwroot.$this->webpath.$this->filename;
    }
}

/**
 * Google charts line graph class
 *
 * @package   moodle-dot-org
 * @subpackage   stats
 * @copyright 2009 Sam Hemelryk <sam@hemelryk.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class google_charts_line_graph extends line_graph {
    /** @var array The possible styles for the line graph */
    private $possiblestyles = Array(
        'lc',  // Standard Sparkline graph
        'ls',  // No Axis lines are drawn
        'lxy');  // Two sets of lines
    /** @var string The style to use for the line graph */
    private $style = 'lc';
    /** @var array The possible legend positions */
    private $legendpositions = Array(
        'b', // Bottom Horizontal
        't', // Top Horizontal
        'bv', // Bottom Veritcal
        'tv', // Top Vertical
        'r', // Right Veritcal
        'l'); // Left Vertical
    /** @var string The legend position to use for the chart */
    private $legendposition = 't';
    /** @var int The number of points to limit the graph to */
    private $pointlimit = 12;
    /** @var bool The direction to limit, right to left if true else left to right */
    private $limitrtl = true;
    /** @var string The url to curl to get the chart */
    private $url = null;
    /** @var int The definition to use when calculating the top value for the chart */
    public $logicaltopdefinition = STATS_LOGICALTOP_FIRSTTWODIGIT;
    
    /**
     * Sets the limit on the number of points to display
     *
     * @param int $limit The limit
     * @return bool
     */
    public function set_point_limit($limit) {
        if (is_int($limit) && $limit > 0) {
            $this->pointlimit=$limit;
            return true;
        }
        return false;
    }
    /**
     * Sets the style for the google charts line graph
     *
     * @param int $style
     * @return bool
     */
    public function set_style($style) {
        if (in_array($style, $this->possiblestyles)) {
            $this->style = $style;
            return true;
        } else {
            return false;
        }
    }
    /**
     * Sets the position of any legends for the chart
     *
     * @param int $position
     * @return bool
     */
    public function set_legend_position($position) {
        if (array_key_exists($position, $this->legendpositions)) {
            $this->legendposition = $this->legendpositions[$position];
            return true;
        }
        return false;
    }
    /**
     * Returns the top value of the chart
     *
     * @uses GOOGLE_CHARTS_COMBINED_TOP_VALUE
     * @uses GOOGLE_CHARTS_PRIMARY_TOP_VALUE
     * @uses GOOGLE_CHARTS_SECONDARY_TOP_VALUE
     * @param int $setting Can be one of GOOGLE_CHARTS_COMBINED_TOP_VALUE,
     *                      GOOGLE_CHARTS_PRIMARY_TOP_VALUE, or GOOGLE_CHARTS_SECONDARY_TOP_VALUE
     * @return int The top value to use
     */
    public function get_top_value($setting=0) {
        $top = 0;
        $secondarytop = 0;
        foreach ($this->xvalues as $value) {
            if ($value->Value>$top) {
                $top = $value->Value;
            }
            if ($this->usesecondxvalue) {
                if ($value->secondvalue>$secondarytop) {
                    $secondarytop = $value->secondvalue;
                }
            }
        }
        if ($this->style=='lxy') {
            switch ($setting) {
                case GOOGLE_CHARTS_COMBINED_TOP_VALUE: return ($top>$secondarytop)?$top:$secondarytop;
                case GOOGLE_CHARTS_PRIMARY_TOP_VALUE: return $top;
                case GOOGLE_CHARTS_SECONDARY_TOP_VALUE: return $secondarytop;
            }
            return ($top>$secondarytop)?$top:$secondarytop;
        } else {
            return ($top+$secondarytop);
        }
    }
    /**
     * Generates the URL required to request the chart from google
     *
     * @return bool
     */
    public function generate_url() {
        $labels = array();
        $secondlabels = array();
        $values = array();
        $secondvalues = array();

        $topvalue = $this->get_top_value();
        $topvalue = logical_top_point($topvalue, $this->logicaltopdefinition);
        $divider = $topvalue;

        if ($this->pointlimit!==0) {
           if ($this->limitrtl) {
                $valuecount = count($this->xvalues);
                if ($valuecount<$this->pointlimit) $this->pointlimit = $valuecount;
                $lastsecondlabel = '';
                for ($i=$valuecount-$this->pointlimit;$i<$valuecount;$i++) {
                    if ($i%$this->xinterval===0) {
                        $labels[] = $this->xvalues[$i]->Label;
                    }
                    $secondlabel = $this->xvalues[$i]->SecondLabel;
                    if ($secondlabel===$lastsecondlabel) {
                        $secondlabels[] = '';
                    } else {
                        $lastsecondlabel = $secondlabel;
                        $secondlabels[] = $secondlabel;
                    }
                    $values[] = ($this->xvalues[$i]->Value/$divider) * 100;
                    if ($this->usesecondxvalue) {
                        $secondvalues[] = ($this->xvalues[$i]->SecondValue/$divider) * 100;
                    }
                }
           } else {

           }
        } else {
           foreach ($this->xvalues as $value) {
               $labels[] = $value->Label;
               $secondlabels[] = $value->SecondLabel;
               $values[] = $value->Value;
           }
        }
        $urlbits = Array();
        $urlbits[] = 'cht='.$this->style;
        if ($this->usecharttitle) {
            $urlbits[] = 'chtt='.$this->charttitle;
            $urlbits[] = 'chts='.$this->charttitlecolour.','.$this->charttitlefontsize;
        }
        $urlbits[] = 'chs='.$this->imagewidth.'x'.$this->imageheight;
        $urlbits[] = ($this->usesecondxlabel)?'chxt=x,y,x':'chxt=x,y';
        $urlbits[] = 'chbh=a';
        $urlbits[] = 'chxr=1,'.$this->ybottom.','.$topvalue.','.ceil($topvalue/$this->ylabelsteps);
        $urlbits[] = 'chco='.$this->barcolour.','.$this->secondbarcolour;
        if (count($this->legends)>0) {
            $urlbits[] = 'chdlp='.$this->legendposition;
            $urlbits[] = 'chdl='.join('|', $this->legends);
        }
        $valuestring = 'chd=t:'.join(',', $values);
        if ($this->usesecondxvalue) {
            $valuestring .= '|'.join(',', $secondvalues);
        }
        $urlbits[] = $valuestring;
        $labels = 'chxl=0:|'.join('|', $labels).'|';
        if ($this->usesecondxlabel) {
            $labels .= '2:|'.join('|', $secondlabels).'|';
        }
        $urlbits[] = $labels;
        $url  = 'http://chart.apis.google.com/chart?'.join('&', $urlbits);
        $this->url = $url;
        return true;
    }
    /**
     * Overload the toString function so that we can display the graph
     *
     * @return string The webpath for the graph
     */
    public function __toString() {
        global $CFG;
        if (!$this->forcegeneration && file_exists($this->filepath.$this->filename)) {
            return $CFG->wwwroot.$this->webpath.$this->filename;
        }
        $this->generate_url();
        $cacheoutcome = cache_google_charts_image($this->url, $this->filepath.$this->filename);
        if ($cacheoutcome===false) {
            $this->filename = parent::find_latest_cached_graph();
        }
        return $CFG->wwwroot.$this->webpath.$this->filename;
    }
}

/**
 * Google charts bar graph class
 *
 * @package   moodle-dot-org
 * @subpackage   stats
 * @copyright 2009 Sam Hemelryk <sam@hemelryk.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class google_charts_bar_graph extends bar_graph {
    /** @var array Possible styles for the bar graph */
    private $possiblestyles = Array(
        'bhs',  // Horizontal, possibly stacked
        'bvs',  // Vertical possibly stacked
        'bhg',  // Horizontal, grouped
        'bvg'); // Vertical, grouped
    /** @var string The style to use for the bar graph */
    private $style = 'bvs';
    /** @var array The possible legend positions */
    private $legendpositions = Array(
        'b', // Bottom Horizontal
        't', // Top Horizontal
        'bv', // Bottom Veritcal
        'tv', // Top Vertical
        'r', // Right Veritcal
        'l'); // Left Vertical
    /** @var string The legend position to use for the bar graph */
    private $legendposition = 't';
    /** @var int The number of bars to limit the graph to, 0 for unlimited */
    private $barlimit = 12;
    /** @var bool Limit the graph right to left or left to right */
    private $limitrtl = true;
    /** @var string The url to request to curl the chart */
    private $url = null;
    /** @var int The bar spacing to apply to the graph */
    private $barspacing = 0;
    /** @var int The group spacing to apply to the chart */
    private $groupspacing = 0;
    /** @var bool Display labels above the bars [Only if not a stacked graph type] */
    private $labeldatapoints = true;
    /** @var int The definition to use when working out the top value of the chart */
    public $logicaltopdefinition = STATS_LOGICALTOP_FIRSTTWODIGIT;

    public $overridelabelposition = array();

    /**
     * Toogle display of label datapoints
     *
     * @param bool $settings If true data is shown above bars
     */
    public function set_label_datapoints($setting=false) {
        if ($setting) {
            $this->labeldatapoints = true;
        } else {
            $this->labeldatapoints = false;
        }
    }
    /**
     * Sets the bar spacing property for the chart
     *
     * @param int|float $spacing The spacing in pixels to apply
     * @return bool For success
     */
    public function set_bar_spacing($spacing=0) {
        if (is_int($spacing) && $spacing > 0) {
            $this->barspacing=$spacing;
            return true;
        } else if ($spacing===0) {
            $this->barspacing = 0;
        }
        return false;
    }
    /**
     * Sets the group spacing property for the chart
     *
     * @param int|float $spacing The spacing in pixels to apply to the chart
     * @return bool
     */
    public function set_group_spacing($spacing) {
       if (is_int($spacing) && $spacing > 0) {
            $this->groupspacing=$spacing;
            return true;
        }
        return false;
    }
    /**
     * Sets the limit for the number of bars to display on the graph
     *
     * @param int $limit The number of bars to limit to
     * @return bool
     */
    public function set_bar_limit($limit=0) {
        if (is_int($limit) && $limit > 0) {
            $this->barlimit=$limit;
            return true;
        } else if ($limit===0) {
            $this->barlimit = 0;
            return true;
        }
        return false;
    }
    /**
     * Sets the display style for the chart, must be one of $this->possiblestyles
     *
     * @param int $style Must be one of GOOGLE_CHARTS_BAR_HORIZONTAL_STACKED,
     *                                   GOOGLE_CHARTS_BAR_VERTICAL_STACKED,
     *                                   GOOGLE_CHARTS_BAR_HORIZONTAL_GROUPED,or
     *                                   GOOGLE_CHARTS_BAR_VERTICAL_GROUPED
     * @return bool
     */
    public function set_style($style) {
        if (array_key_exists($style, $this->possiblestyles)) {
            $this->style = $this->possiblestyles[$style];
            return true;
        } else {
            return false;
        }
    }
    /**
     * Sets the legend position for the chart
     *
     * @param string $position Must be in $this->legendpositions
     * @return bool
     */
    public function set_legend_position($position) {
        if (array_key_exists($position, $this->legendpositions)) {
            $this->legendposition = $this->legendpositions[$position];
            return true;
        }
        return false;
    }

    /**
     * Gets the top value to use for the chart
     *
     * @uses GOOGLE_CHARTS_COMBINED_TOP_VALUE
     * @uses GOOGLE_CHARTS_PRIMARY_TOP_VALUE
     * @uses GOOGLE_CHARTS_SECONDARY_TOP_VALUE
     */
    public function get_top_value($setting=0) {
        $top = 0;
        $secondarytop = 0;
        foreach ($this->xvalues as $value) {
            if ($value->Value>$top) {
                $top = $value->Value;
            }
            if ($this->usesecondxvalue) {
                if ($value->secondvalue>$secondarytop) {
                    $secondarytop = $value->secondvalue;
                }
            }
        }
        if ($this->style=='bhg'||$this->style=='bvg') {
            switch ($setting) {
                case GOOGLE_CHARTS_COMBINED_TOP_VALUE: return ($top>$secondarytop)?$top:$secondarytop;
                case GOOGLE_CHARTS_PRIMARY_TOP_VALUE: return $top;
                case GOOGLE_CHARTS_SECONDARY_TOP_VALUE: return $secondarytop;
            }
            return ($top>$secondarytop)?$top:$secondarytop;
        } else {
            return ($top+$secondarytop);
        }
    }

    /**
     * Generates the URL to curl to get the chart
     *
     * @uses GOOGLE_CHARTS_PRIMARY_TOP_VALUE
     * @uses GOOGLE_CHARTS_SECONDARY_TOP_VALUE
     * @return bool
     */
    public function generate_url() {
        if ($this->url!==null) return true;
        $applyscaling = !preg_match('/b(h|v)g/', $this->style);
        $labels = array();
        $secondxlabels = array();
        $secondylabels = array();
        $values = array();
        $secondvalues = array();

        $topvalue = $this->get_top_value();
        $topvalue = logical_top_point($topvalue, $this->logicaltopdefinition);
        $divider = 100;
        if ($applyscaling) {
            $divider = $topvalue;
        }

        if ($this->barlimit!==0) {
           if ($this->limitrtl) {
                $valuecount = count($this->xvalues);
                if ($valuecount<$this->barlimit) $this->barlimit = $valuecount;
                $lastsecondlabel = '';
                for ($i=$valuecount-$this->barlimit;$i<$valuecount;$i++) {
                    if ($i%$this->xinterval===0) {
                        if (is_array($this->xvalues[$i]->Label)) {
                            $labels = array_merge($labels, $this->xvalues[$i]->Label);
                        } else {
                            $labels[] = $this->xvalues[$i]->Label;
                        }
                    } else {
                        $labels[] = '';
                    }
                    $secondlabel = $this->xvalues[$i]->SecondLabel;
                    if ($secondlabel===$lastsecondlabel) {
                        $secondxlabels[] = '';
                    } else {
                        $lastsecondlabel = $secondlabel;
                        $secondxlabels[] = $secondlabel;
                    }
                    if ($applyscaling) {
                        $values[] = round(($this->xvalues[$i]->Value/$divider)*100,2);
                    } else {
                        $values[] = $this->xvalues[$i]->Value;
                    }
                    if ($this->usesecondxvalue) {
                        if ($applyscaling) {
                            $secondvalues[] = round(($this->xvalues[$i]->SecondValue/$divider)*100,2);
                        } else {
                            $secondvalues[] = $this->xvalues[$i]->SecondValue;
                        }
                    }
                    if ($this->usesecondylabel) {
                        
                    }
                }
           } else {
               
           }
        } else {
            $lastsecondlabel = '';
            $count = 0;
            foreach ($this->xvalues as $value) {
                if ($count%$this->xinterval===0) {
                    if (is_array($value->Label)) {
                        $labels = array_merge($labels,$value->Label);
                    } else {
                        $labels[] = $value->Label;
                    }
                } else {
                    $labels[] = '';
                }
                $count++;
                $secondlabel = $value->SecondLabel;
                if ($secondlabel===$lastsecondlabel) {
                    $secondxlabels[] = '';
                } else {
                    $lastsecondlabel = $secondlabel;
                    $secondxlabels[] = $secondlabel;
                }
                if ($applyscaling) {
                    $values[] = round(($value->Value/$divider)*100,2);
                } else {
                    $values[] = $value->Value;
                }
                if ($this->usesecondxvalue) {
                    if ($applyscaling) {
                        $secondvalues[] = round(($value->SecondValue/$divider)*100,2);
                    } else {
                        $secondvalues[] = $value->SecondValue;
                    }
                }
            }
        }
        $urlbits = Array();
        
        // The type of chart, of $this->possiblestyles
        $urlbits[] = 'cht='.$this->style;

        if ($this->usecharttitle) {
            // The title to display at the top of the chart
            $urlbits[] = 'chtt='.$this->charttitle;

            // The style to use for the chart
            $urlbits[] = 'chts='.$this->charttitlecolour.','.$this->charttitlefontsize;
        }

        // The dimensions of the chart
        $urlbits[] = 'chs='.$this->imagewidth.'x'.$this->imageheight;

        // Which axis labels to display
        $labelcount = 1;
        $axislabel = 'chxt=x,y';
        if ($this->usesecondxlabel) {
            $labelcount++;
            $axislabel .= ',x';
        }
        if ($this->usesecondylabel) {
            $labelcount++;
            $axislabel .= ',y';
        }
        $urlbits[] = $axislabel;

        if (count($this->overridelabelposition)>0) {
            // The axis scale to use
            $urlbits[] = 'chxp='.join(',', $this->overridelabelposition);
        }

        // The Y Axis scale
        $urlbits[] = 'chxr=1,'.$this->ybottom.','.$topvalue.','.ceil($topvalue/$this->ylabelsteps);

        // The colours to use of the bars
        $barcolour = 'chco='.$this->barcolour;
        if ($this->usesecondxvalue) {
            $barcolour .= ','.$this->secondbarcolour;
        }
        $urlbits[] = $barcolour;

        // Check if any legends have been defined
        if (count($this->legends)>0) {
            // Set the legends position
            $urlbits[] = 'chdlp='.$this->legendposition;
            // Add all set legends
            $urlbits[] = 'chdl='.join('|', $this->legends);
        }

        // Check if a groupspacing and/or barspacing have been set
        if ($this->groupspacing!==0 || $this->barspacing!==0) {
            // Set the groupspacing and barspacing values manually
            $urlbits[] = 'chbh=a,'.$this->barspacing.','.$this->groupspacing;
        } else {
            // Leave it to the chart API to decide on
            $urlbits[] = 'chbh=a';
        }

        if (!$applyscaling && $this->labeldatapoints) {
            // If we are not scaling the value then tell the API to display
            // the values above each bar
            // chm=<format>,<colour>,<dataset>,<datapoint>,<fontsize>
            $urlbits[] = 'chm=N,000000,0,-1,8|N,000000,1,-1,8';
        }

        // Check if we are displaying a grouped bar graph
        if (preg_match('/b(h|v)g/', $this->style)) {
            // Work out the logical top point for the primary values
            $primarytop = logical_top_point($this->get_top_value(GOOGLE_CHARTS_PRIMARY_TOP_VALUE), $this->logicaltopdefinition);
            // Define the scaling of the Y axis
            $scaling = "chds=".$this->ybottom.','.$primarytop;
            // Check if we are using second values
            if ($this->usesecondxvalue) {
                // Work out the logical top point for the secondary values
                $secondarytop = logical_top_point($this->get_top_value(GOOGLE_CHARTS_SECONDARY_TOP_VALUE), $this->logicaltopdefinition);
                // Define the scaling of the Y axis for the secondary values
                $scaling .= ','.$this->ybottomsecondary.','.$secondarytop;
            }
            $urlbits[] = $scaling;
        }

        // Create the primary x axis label string
        $pos = 1;
        $labels = 'chxl='.'0:|'.join('|', $labels).'|';
        
        // Check if we have a secondary x label set
        if ($this->usesecondxlabel) {
            // Create the secondary x axis label string
            $pos ++;
            $labels .= '2:|'.join('|', $secondxlabels).'|';
        }
        if ($this->usesecondylabel) {
            // Create the secondary x axis label string
            $labels .= '3:|'.join('|', $secondylabels).'|';
        }
        $urlbits[] = $labels;

        // Create the value string for the primary values
        $valuestring = 'chd=t:'.join(',', $values);
        // Check if we are using secondary values
        if ($this->usesecondxvalue) {
            // Create the value string for the secondary values
            $valuestring .= '|'.join(',', $secondvalues);
        }
        $urlbits[] = $valuestring;

        // Create the entire URL and set it to the object;
        $url  = 'http://chart.apis.google.com/chart?'.join('&', $urlbits);
        $this->url = $url;

        return true;
    }
    /**
     * Overload the toString function so that we can display the graph
     *
     * @return string The webpath for the graph
     */
    public function __toString() {
        global $CFG;
        if (!$this->forcegeneration && file_exists($this->filepath.$this->filename)) {
            return $CFG->wwwroot.$this->webpath.$this->filename;
        }
        $this->generate_url();
        $cacheoutcome = cache_google_charts_image($this->url, $this->filepath.$this->filename);

        if ($cacheoutcome===false) {
            $this->filename = parent::find_latest_cached_graph();
        }
        return $CFG->wwwroot.$this->webpath.$this->filename;
    }
}

/**
 * Uses curl to fetch a google charts image and saves it to disk for display
 *
 * @param string $url The url to curl
 * @param string $filepath The full path of the file to save
 * @return bool True for success false otherwise
 */
function cache_google_charts_image($url, $filepath) {

    //extract the params from the url to call the function by POST
    list($url, $params) = explode('?', $url, 2);

    $error = false;
    $handle = curl_init();
    curl_setopt($handle, CURLOPT_URL, trim($url));
    curl_setopt($handle, CURLOPT_POST, 1);
    curl_setopt($handle, CURLOPT_POSTFIELDS, $params);
    curl_setopt($handle, CURLOPT_HEADER, false);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    $data = curl_exec($handle);
    if(curl_errno($handle)) {
        $error = true;
    }
    
    $status = curl_getinfo($handle, CURLINFO_HTTP_CODE);
    $contenttype = curl_getinfo($handle, CURLINFO_CONTENT_TYPE);

    if ($status!=200 || strpos($contenttype,'png')===false) {
        $error = true;
    }

    // Can't return until we close the handle, or who ever is administrating
    // the apache server will blame you for lots of unclosed sockets
    curl_close($handle);

    // Something went wrong... bail out
    if ($error) {
        return false;
    }

    // Save the image now
    $outcome = file_put_contents($filepath, $data, LOCK_EX);
    // If it didn't work return false and we'll serve a old cached version
    if ($outcome===false) {
        return false;
    }
    // It all worked hurrah
    return true;
}


?>
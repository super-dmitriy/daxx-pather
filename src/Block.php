<?php

/**
 * TODO: clarify requirements
 *
 * Can we go through visited points?
 * Like this:
 *
 *   ........................
 *   ...........1............
 *   ...........*............
 *   ...........*............
 *   ...........*............
 *   ...........*............
 *   .....3*****2*******4....
 *   ........................
 *   ........................
 *
 * Currently assumed we can
 *
 */

require_once "Point.php";

class Block {

    private $block;
    private $width;
    private $height;

    function __construct($block = []) {
        $this->block = $block;
        $this->calculateBlockSize();
    }

    /**
     * Load block data from input file
     * And convert it to 2d array
     *
     * @param $filePath string
     */
    function loadFromFile($filePath) {
        $file  = file_get_contents($filePath);
        $block = explode("\n", trim($file));
        array_walk($block, function(&$line){
            $line = str_split($line);
        });
        $this->block = $block;

        $this->calculateBlockSize();
    }

    private function calculateBlockSize() {
        $this->height = count($this->block);
        $this->width  = $this->height ? count($this->block[0]) : 0;
    }

    function solvePath() {
        $startPoint = $this->findHashPoint();
        if (!$startPoint) return; // no points to connect

        while ($this->hasUnvisitedPoints()) {

            $pathPoints = $this->getNextHashPath($startPoint);
            $pathBegin  = $pathPoints[0];
            $pathEnd    = $pathPoints[count($pathPoints) - 1];
            $startPoint = $pathEnd;

            // connect path points
            $p1 = array_shift($pathPoints);
            do {
                $p2 = array_shift($pathPoints);
                $this->drawLine($p1, $p2);
                $p1 = $p2;
            } while ($pathPoints);

            // mark path end points as visited. to prevent from visiting 2+ times
            $p1 = $pathBegin;
            $p2 = $pathEnd;
            $this->block[$p1->getY()][$p1->getX()] = '@';
            $this->block[$p2->getY()][$p2->getX()] = '@';

        };

        // reset all visited points
        foreach ($this->block as $y => $line) {
            foreach ($line as $x => $p) {
                if ($p == '@') $this->block[$y][$x] = '#';
            }
        }
    }

    function saveToFile($outputFilePath) {
        $output = '';
        foreach ($this->block as $line) {
            $output .= implode('', $line) . "\n";
        }
        file_put_contents($outputFilePath, $output);
    }

    private function hasUnvisitedPoints() {
        foreach ($this->block as $line) {
            foreach ($line as $c) {
                if ($c == '#') return true;
            }
        }
        return false;
    }

    /**
     * @param $point1 Point
     * @param $point2 Point
     */
    private function drawLine($point1, $point2) {
        $x1 = $point1->getX();
        $y1 = $point1->getY();

        $x2 = $point2->getX();
        $y2 = $point2->getY();

        $dx = $x1 == $x2 ? 0 : ($x1 > $x2 ? -1 : 1);
        $dy = $y1 == $y2 ? 0 : ($y1 > $y2 ? -1 : 1);

        while ($x1 != $x2 || $y1 != $y2) {
            $x1 += $dx;
            $y1 += $dy;
            if ($this->block[$y1][$x1] == '.') { // in case we are moving through visited (@) point
                $this->block[$y1][$x1] = '*';
            }
        }
    }

    /**
     * find Point with # symbol
     * return null if symbol not fond
     *
     * @return Point|null
     */
    private function findHashPoint() {
        foreach ($this->block as $y => $line) {
            foreach ($line as $x => $char) {
                if ($char == '#') return new Point($x, $y);
            }
        }
        return null;
    }

    /**
     * @param $startPoint Point
     * @return Point[]|null
     */
    private function getNextHashPath($startPoint) {
        $y  = $startPoint->getY();
        $dy = +1;
        $point = null;

        /*
         * Search for next hash point in each horizontal line at Y position
         * If not found - move Y one step down until not found or reached bottom
         * If still not found - move up until not found or reached top
         */
        do {
            if ($point = $this->scanForHash($startPoint, +1, $y)) break;
            if ($point = $this->scanForHash($startPoint, -1, $y)) break;
            $y += $dy;

            if ($y >= $this->height) {
                $y  = $startPoint->getY();
                $dy = -1;
            }

            if ($y < 0) return null;

        } while (true);

        // prepare result path points array
        $res = [$startPoint];
        if ($y != $startPoint->getY()) $res[] = new Point($startPoint->getX(), $y);
        $res[] = $point;

        return $res;
    }

    /**
     * Scan horizontal line for # symbol
     *
     * @param $startPoint Point
     * @param $xIncrement int scan line left (-1) or right (+1)
     * @param $y int
     * @return Point|null
     */
    private function scanForHash($startPoint, $xIncrement, $y) {
        $x = $startPoint->getX();

        do {
            $x += $xIncrement;
            if (!isset($this->block[$y][$x])) return null;
            $symbol = $this->block[$y][$x];
            if ($symbol == '#') return new Point($x, $y);
        } while (true);

        return null; // not required. added to not be highlighted by IDE
    }

}
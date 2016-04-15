<?php

function simplify($points, $tolerance = 1, $highestQuality = false) {
	if (count($points) < 2) return $points;
	$sqTolerance = $tolerance * $tolerance;
	if (!$highestQuality) {
		$points = simplifyRadialDistance($points, $sqTolerance);
	}
	$points = simplifyDouglasPeucker($points, $sqTolerance);
	return $points;
}
function getSquareDistance($p1, $p2) {
	$dx = $p1['x'] - $p2['x'];
	$dy = $p1['y'] - $p2['y'];
	$dz = $p1['z'] - $p2['z'];
	return $dx * $dx + $dy * $dy + $dz * $dz;
}
function getSquareSegmentDistance($p, $p1, $p2) {
	$x = $p1['x'];
	$y = $p1['y'];
	$z = $p1['z'];
	$dx = $p2['x'] - $x;
	$dy = $p2['y'] - $y;
	$dz = $p2['z'] - $z;
	if ($dx !== 0 || $dy !== 0 || $dz !== 0) {
		$t = (($p['x'] - $x) * $dx + ($p['y'] - $y) * $dy + ($p['z'] - $z) *$dz) / ($dx * $dx + $dy * $dy + $dz * $dz);
		if ($t > 1) {
			$x = $p2['x'];
			$y = $p2['y'];
			$z = $p2['z'];
		} else if ($t > 0) {
			$x += $dx * $t;
			$y += $dy * $t;
			$z += $dz * $t;
		}
	}
	$dx = $p['x'] - $x;
	$dy = $p['y'] - $y;
	$dz = $p['z'] - $z;
	return $dx * $dx + $dy * $dy + $dz * $dz;
}
function simplifyRadialDistance($points, $sqTolerance) { // distance-based simplification
	
	$len = count($points);
	$prevPoint = $points[0];
	$newPoints = array($prevPoint);
	$point = null;
	
	for ($i = 1; $i < $len; $i++) {
		$point = $points[$i];
		if (getSquareDistance($point, $prevPoint) > $sqTolerance) {
			array_push($newPoints, $point);
			$prevPoint = $point;
		}
	}
	if ($prevPoint !== $point) {
		array_push($newPoints, $point);
	}
	return $newPoints;
}


// simplification using optimized Douglas-Peucker algorithm with recursion elimination
function simplifyDouglasPeucker($points, $sqTolerance) {
	$len = count($points);
	$markers = array_fill ( 0 , $len - 1, null);
	$first = 0;
	$last = $len - 1;
	$stack = array();
	$newPoints  = array();
    
	$markers[$first] = $markers[$last] = 1;
	while ($last) {
		$maxSqDist = 0;
		for ($i = $first + 1; $i < $last; $i++) {
			$sqDist = getSquareSegmentDistance($points[$i], $points[$first], $points[$last]);
			if ($sqDist > $maxSqDist) {
				$index = $i;
				$maxSqDist = $sqDist;
			}
		}
		if ($maxSqDist > $sqTolerance) {
			$markers[$index] = 1;
			array_push($stack, $first);
			array_push($stack, $index);
			array_push($stack, $index);
			array_push($stack, $last);
		}
		$last = array_pop($stack);
		$first = array_pop($stack);
	}
	for ($i = 0; $i < $len; $i++) {
		if ($markers[$i]) {
			array_push($newPoints, $points[$i]);
		}
	}
	return $newPoints;
}

?>

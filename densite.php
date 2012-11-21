<?php

if (!defined('IN_SPYOGAME'))
{
	exit('Hacking attempt');
}

if (!$db->sql_numrows($db->sql_query('SELECT active FROM '. TABLE_MOD .' WHERE action = \'densite\' AND active = \'1\' LIMIT 1')))
{
  exit('Module non activé');
}

require_once 'views/page_header.php';

/* ** Functions ** ********************************************************** */

// nearest-neighbor interpolation
//
function nni ($array, $width, $factor)
{
  $factorWidth = $factor * $width;
  $factor2Width = $factor * $factorWidth;
  
  foreach ($array as $k => $v)
  {
    $index = floor($k / $width) * $factor2Width + ($k % $width) * $factor;
  
    for ($i = 0; $i < $factor; $i++)
    {
      for ($j = 0; $j < $factor; $j++)
      {
        $out[$i * $factorWidth + $index + $j] = $v;
      }
    }
  }
  
  ksort($out);
  
  return array_values($out);
}

/* ** Begin Mod ** ********************************************************** */

// empty: 0, white
// occupied: 1, black
// unknown: 2, orange (#FF A5 00)

// Universe constants
$galaxyCount =   9;
$systemCount = 499;
$rowCount    =  15;

// Canvas dimensions
$padding        = 10;
$galaxyMargin   =  5;
$pixelPerPlanet =  2;

$width  = 2 * $padding + $pixelPerPlanet * $systemCount;
$height = 2 * $padding + ($galaxyCount - 1) * $galaxyMargin + $galaxyCount * $rowCount * $pixelPerPlanet;

// generating uni-dimensional 'universe' array
$length = $systemCount * $rowCount;
$universe = array();

for ($i = 0; $i < $galaxyCount; $i++)
{
  for ($j = 0; $j < $length; $j++)
  {
    $universe[$i][$j] = 2;
  }
}

// getting data from db
$result = $db->sql_query('SELECT galaxy, system, row, player FROM '. TABLE_UNIVERSE);

while (list($galaxy, $system, $row, $player) = $db->sql_fetch_row($result))
{
  $universe[$galaxy - 1][($system - 1) + ($row - 1) * $systemCount] = empty($player) ? 0 : 1;
}

// scaling array by a factor of $pixelPerPlanet
foreach ($universe as $k => $galaxy)
{
  $universe[$k] = nni($galaxy, $systemCount, $pixelPerPlanet);
}

?>

<canvas id="canvas" height="<?php echo $height; ?>" width="<?php echo $width; ?>"></canvas>
<p><input type="button" id="download-canvas" value="Sauver l'image (PNG)" /></p>

<script type="text/javascript">

  var rowCount = parseInt(<?php echo $rowCount; ?>),
      padding = parseInt(<?php echo $padding; ?>),
      galaxyMargin = parseInt(<?php echo $galaxyMargin; ?>),
      pixelPerPlanet = parseInt(<?php echo $pixelPerPlanet; ?>),
      universe = <?php echo json_encode($universe); ?>,
      
      canvas = document.getElementById('canvas'),
      canvasHeight = canvas.height,
      canvasWidth = canvas.width,
      ctx = canvas.getContext('2d'),
      imageData, data, y, i;
  
  // canvas background
  ctx.fillStyle = '#252525';
  ctx.fillRect(0, 0, canvasWidth, canvasHeight);
  
  // galaxies
  universe.forEach(function (galaxy, galaxyIndex) {

    y = padding + galaxyIndex * (rowCount * pixelPerPlanet + galaxyMargin);
    imageData = ctx.getImageData(padding, y, canvasWidth - 2 * padding, rowCount * pixelPerPlanet);
    data = imageData.data;
    
    galaxy.forEach(function (row, rowIndex) {
      
      i = rowIndex << 2; // => * 4
      
      if (row == 2) {
        data[i]     = 0xFF;
        data[i + 1] = 0xA5;
        data[i + 2] = 0x00;
      }
      else if (row == 1) {
        data[i]     = 0x00;
        data[i + 1] = 0x00;
        data[i + 2] = 0x00;
      }
      else {
        data[i]     = 0xFF;
        data[i + 1] = 0xFF;
        data[i + 2] = 0xFF;
      }

      data[i + 3]   = 0xFF; // alpha
    });
    
    ctx.putImageData(imageData, padding, y);
  });
  
  document.getElementById('download-canvas').onclick = function () {
    window.location = canvas.toDataURL('image/png').replace('image/png', 'image/octet-stream');
  }
      
</script>

<?php

/* ** End Mod ** ************************************************************ */

require_once 'views/page_tail.php';

?>

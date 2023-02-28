<?php
/**
 * Display the game plays diagram
 */

class MyArcade_Admin_Widget_Plays {

  public static function output() {
   $days = 30;
   $data = "";

   // Collect data for the graph
   for( $i = $days; $i >= 0; $i-- ) {
     $data .= "['" . MyArcade_Stats::get_date( '-' . $i ) . "'," . MyArcade_Stats::get_plays( '-' . $i ) . "],";
   }

   // Generate the graph
   ?>
   <div id="play-stats" style="height:300px;"></div>

   <script type="text/javascript">
      var plays_chart;

      jQuery(document).ready(function($) {
        var plays_data_line = [<?php echo $data; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped ?>];

        plays_chart = $.jqplot( 'play-stats', [plays_data_line], {
          animate: true,
          animateReplot: true,
          title: {
            text: <?php echo wp_json_encode( sprintf( __( 'Plays in the last %s days', 'myarcadeplugin' ), $days ) ); ?>,
            fontSize: '12px',
          },
          grid: {
            drawBorder: false,
            shadow: false,
            background: '#ffffff',
          },
          seriesColors: ["#00ACFF"],
          series:[
            {
              rendererOptions: {
                smooth: true,
                animation: {
                  speed: 1000
                }
              },
              fill: true,
              fillColor: '#F0FAFF',
              fillAndStroke: true,
            }
          ],
          axes: {
            xaxis: {
              min: '<?php echo MyArcade_Stats::get_date( '-' . $days ); ?>',
              max: '<?php echo MyArcade_Stats::get_date(); ?>',
              tickInterval: '1 day',
              renderer: $.jqplot.DateAxisRenderer,
              tickRenderer: $.jqplot.CanvasAxisTickRenderer,
              tickOptions: {
                fontSize: '10px',
                angle: -45,
                formatString: '%b %#d',
                showGridline: false,
              },
            },
            yaxis: {
              min: 0,
              padMin: 1.0,
              label: <?php echo wp_json_encode( __( 'Number of plays', 'myarcadeplugin' ) ); ?>,
              labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
              labelOptions: {
                angle: -90,
                fontSize: '10px',
              },
            }
          },
          highlighter: {
            show: true,
            showMarker: true,
            formatString: '%s:&nbsp;<b>%d</b>&nbsp;',
            tooltipOffset: 3,
            sizeAdjust: 5,
          },
        });

        $(window).resize(function () {
          plays_chart.replot({resetAxes: ['xaxis', 'yaxis']});
        });
     });
   </script>
   <?php
  }
}

MyArcade_Admin_Widget_Plays::output();
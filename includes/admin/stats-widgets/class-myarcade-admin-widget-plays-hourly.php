<?php
/**
 * Display yesterdays game plays per hour
 */

class MyArcade_Admin_Widget_Plays_Hourly {

  public static function output() {

    $hourly_plays = MyArcade_Stats::get_houry_plays( '-1' );

    $data = implode(",", $hourly_plays );
    $hours = "'" . implode( "','", array_keys( $hourly_plays ) ) . "'";

    // Generate the graph
    ?>
    <div id="hourly-stats" style="height:300px;"></div>

    <script type="text/javascript">
      var hourly_plays_chart;

      jQuery(document).ready(function($) {
        var data = [<?php echo $data; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped ?>];
        var ticks = [<?php echo $hours; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped ?>];

        hourly_plays_chart = $.jqplot( 'hourly-stats', [data], {
          animate: true,
          animateReplot: true,
          title: {
            text: <?php echo wp_json_encode( __( "Yesterday's hourly plays", 'myarcadeplugin' ) ); ?>,
            fontSize: '12px',
          },
          highlighter: {
            show: true,
            showTooltip: true,
            tooltipAxes: 'y',
            tooltipOffset: 3,
            sizeAdjust: 5,
          },
          grid: {
            drawBorder: false,
            shadow: false,
            background: '#ffffff',
          },
          seriesColors: ["#00ACFF"],
          seriesDefaults:{
            rendererOptions: {
              smooth: true,
              animation: {
                speed: 1000
              },
            },
            fill: true,
            fillColor: '#F0FAFF',
            fillAndStroke: true,
          },
          axesDefaults: {
            pad: 0,
          },
          axes: {
            xaxis: {
              renderer: $.jqplot.CategoryAxisRenderer,
              ticks: ticks,
            },
          },
        });

        $(window).resize(function () {
          hourly_plays_chart.replot({resetAxes: ['xaxis', 'yaxis']});
        });
      });
    </script>
    <?php
  }
}

MyArcade_Admin_Widget_Plays_Hourly::output();
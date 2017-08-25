<?php
//  Copyright (c) 2009 Facebook
//
//  Licensed under the Apache License, Version 2.0 (the "License");
//  you may not use this file except in compliance with the License.
//  You may obtain a copy of the License at
//
//      http://www.apache.org/licenses/LICENSE-2.0
//
//  Unless required by applicable law or agreed to in writing, software
//  distributed under the License is distributed on an "AS IS" BASIS,
//  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
//  See the License for the specific language governing permissions and
//  limitations under the License.
//

$content = file_get_contents('php://input');
$dir = '../traces/';
$tracesMax = 35;
if ($content) {
    $url = parse_url($_GET['url']);
    $project = array_key_exists('project', $_GET) ? $_GET['project'] : str_replace('.', '-', $url['host']);
    $path = str_replace('/', '-', $url['path']);
    $existedTraces = glob("{$dir}*{$path}*");
    $tracesCount = count($existedTraces);
    $currentPath = null;
    if ($tracesCount != $tracesMax) {
        $currentPath =
            $dir .
            ($tracesCount + 1) .
            str_replace('/', '-', $url['path']) .
            '.' .
            $project .
            ".xhprof"
        ;
    } else {
        usort($existedTraces, create_function('$a,$b', 'return filemtime($b) - filemtime($a);'));
        $oldestTrace = array_pop($existedTraces);
        if (time() - filemtime($oldestTrace) > 1 * 3600) {
            $currentPath = $oldestTrace;
        }
    }

    if ($currentPath) {
        file_put_contents($currentPath, bzcompress($content));
    }
    exit;
}

$GLOBALS['XHPROF_LIB_ROOT'] = dirname(__FILE__) . '/../lib';

require_once $GLOBALS['XHPROF_LIB_ROOT'].'/display/xhprof.php';

// param name, its type, and default value
$params = array('run'        => array(XHPROF_STRING_PARAM, ''),
                'wts'        => array(XHPROF_STRING_PARAM, ''),
                'symbol'     => array(XHPROF_STRING_PARAM, ''),
                'sort'       => array(XHPROF_STRING_PARAM, 'wt'), // wall time
                'run1'       => array(XHPROF_STRING_PARAM, ''),
                'run2'       => array(XHPROF_STRING_PARAM, ''),
                'source'     => array(XHPROF_STRING_PARAM, 'xhprof'),
                'all'        => array(XHPROF_UINT_PARAM, 0),
                );

// pull values of these params, and create named globals for each param

xhprof_param_init($params);
/* reset params to be a array of variable names to values
   by the end of this page, param should only contain values that need
   to be preserved for the next page. unset all unwanted keys in $params.
 */

foreach ($params as $k => $v) {
  $params[$k] = $$k;

  // unset key from params that are using default values. So URLs aren't
  // ridiculously long.
  if ($params[$k] == $v[1]) {
    unset($params[$k]);
  }
}

echo "<html>";

echo "<head><title>XHProf: Hierarchical Profiler Report</title>";
xhprof_include_js_css();
echo "</head>";

echo "<body>";

$vbar  = ' class="vbar"';
$vwbar = ' class="vwbar"';
$vwlbar = ' class="vwlbar"';
$vbbar = ' class="vbbar"';
$vrbar = ' class="vrbar"';
$vgbar = ' class="vgbar"';

$xhprof_runs_impl = new XHProfRuns_Default();

displayXHProfReport($xhprof_runs_impl, $params, $source, $run, $wts,
                    $symbol, $sort, $run1, $run2);


echo "</body>";
echo "</html>";

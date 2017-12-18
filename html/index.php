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

$domains = [
    'account' => ['/api/v1/account'],
    'competitions' => ['/api/v1/competitions'],
    'sekabet' => ['/api/v1/sekabet'],
    'partner' => ['/api/v1/partner'],
    'manager' => ['/api/v1/manager'],
    'matchbook' => ['/api/v1/matchbook'],
    'payment' => ['/api/v1/withdraw', '/api/v1/deposit', '/api/v1/payment'],
    'fundist' => ['/api/v1/fundist'],
    'softswiss' => ['/api/v1/softswiss'],
    'microgaming' => ['/api/v1/microgaming'],
    'achievements' => ['/api/v1/achievements'],
    'casino' => ['/api/v1/casino'],
    'agent' => ['/api/v1/agentsystem'],
    'exchange' => ['/api/v1/exchange'],
    'betfair' => ['/api/v1/betfair'],
    'sportbook' => ['/api/v1/sportbook'],
    'player' => ['/api/v1/player'],
    'workers' => ['betfair-worker', 'sportbook-worker', 'competition-worker', 'exchange-worker', 'matchbook-worker']

];

$content = file_get_contents('php://input');
$dir = '../traces/';
if ($content) {
    $runTime = (int)(unserialize($content)['main()']['wt'] / 1000);
    if ($runTime < 500) {
        exit;
    }
    $id = $_GET['id'];
    if (array_key_exists('url', $_GET)) {
        $url = parse_url($_GET['url']);
        $path = $url['path'];
        $project = $url['host'];
    } else {
        $project = $_GET['project'];
        $path = strtok($_GET['action'], '?');
    }
    $subFolder = 'other';
    foreach ($domains as $key => $domain) {
        foreach ($domain as $relativeUrl) {
            if (strpos($path, $relativeUrl) === 0) {
                $subFolder = $key;
            }
        }
    }
    $path = str_replace('/api/v1', '', $path);
    $path = str_replace('/', '-', $path);
    $path = preg_replace('/[0-9]+/', '', $path);
    $path = str_replace('-.-', '-', $path);
    $path = str_replace('.jpeg', '-jpeg', $path);

    $currentPath =
        $dir .
        $subFolder.'/' .
        $path . '||' .
        str_replace('.', '*', $id) .
        '.' .
        $project .
        ".xhprof"
    ;

    if ($currentPath) {
        file_put_contents($currentPath, gzencode($content));
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
$domain = array_key_exists('domain', $_GET) ? $_GET['domain'] : '';
$xhprof_runs_impl = new XHProfRuns_Default(null, $domain);

displayXHProfReport($xhprof_runs_impl, $params, $source, $run, $wts,
                    $symbol, $sort, $run1, $run2);


echo "</body>";
echo "</html>";

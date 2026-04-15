<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Konine Installation</title>
    <meta name="title" content="Konine Installation">
    <meta name="description" content="The following tests have been run to determine if Konine will work in your environment.">
    <meta name="author" content="Konine Team">
    <style>
        body {
            margin: 0;
            background-color: #efefef;
            font-family: 'Arial', sans-serif;
        }

        .container {
            max-width: 800px;
            margin: 10px auto;
            background: #fff;
            padding: 20px 15px;
            border-radius: 12px;
        }

        table {
            margin: 20px 0;
        }

        h1,
        th {
            margin: 5px 0;
            color: #2f2f2f;
        }

        p,
        td {
            margin: 10px 0;
            color: #383838;
        }

        a,
        a:visited {
            color: #3195f0;
            text-decoration: none;
        }

        th {
            text-align: left;
            width: 30%;
        }

        td,
        th {
            padding: 5px 0;
        }

        .logo {
            background-image: url(data:image/png;base64,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);
            width: 80px;
            height: 80px;
            float: right;
            margin-top: -20px;
        }

        .pass {
            color: #155724;
        }

        .fail {
            color: #721c24;
        }

        code {
            font-family: monospace;
            font-size: 15px;
            background: #f1f0f0;
        }

        .alert {
            padding: 20px;
            border-radius: 12px;
            border: 1px solid;
            margin-bottom: 20px;
        }

        .alert code {
            background: none;
        }

        .danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }

        .success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        .icon {
            display: inline-block;
            width: 24px;
            height: 24px;
            margin-bottom: -6px;
            margin-right: 10px;
        }

        .icon-success {
            background-image: url(data:image/svg+xml;base64,PHN2ZyBmaWxsPSIjMTU1NzI0IiBoZWlnaHQ9IjI0IiB2aWV3Qm94PSIwIDAgMjQgMjQiIHdpZHRoPSIyNCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4gICAgPHBhdGggZD0iTTAgMGgyNHYyNEgweiIgZmlsbD0ibm9uZSIvPiAgICA8cGF0aCBkPSJNOSAxNi4yTDQuOCAxMmwtMS40IDEuNEw5IDE5IDIxIDdsLTEuNC0xLjRMOSAxNi4yeiIvPjwvc3ZnPg==);
        }

        .icon-danger {
            background-image: url(data:image/svg+xml;base64,PHN2ZyBmaWxsPSIjNzIxYzI0IiBoZWlnaHQ9IjI0IiB2aWV3Qm94PSIwIDAgMjQgMjQiIHdpZHRoPSIyNCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4gICAgPHBhdGggZD0iTTE5IDYuNDFMMTcuNTkgNSAxMiAxMC41OSA2LjQxIDUgNSA2LjQxIDEwLjU5IDEyIDUgMTcuNTkgNi40MSAxOSAxMiAxMy40MSAxNy41OSAxOSAxOSAxNy41OSAxMy40MSAxMnoiLz4gICAgPHBhdGggZD0iTTAgMGgyNHYyNEgweiIgZmlsbD0ibm9uZSIvPjwvc3ZnPg==);
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="logo"></div>

        <h1>Environment Tests</h1>
        <p>
            The following tests have been run to determine if Konine will work in your environment. If any of the tests have failed, consult the <a href="https://github.com/hospicedev/konine/tree/master/system/guide/kohana/install.md" target="_blank">documentation</a> for more information on how to correct the problem.
        </p>

        <?php $failed = FALSE ?>

        <table>
            <tbody>
                <tr>
                    <th>PHP Version</th>
                    <?php if (version_compare(PHP_VERSION, '8.4', '>=')): ?>
                    <td class="pass">
                        <?php echo PHP_VERSION ?>
                    </td>
                    <?php else: $failed = TRUE ?>
                    <td class="fail">Konine requires PHP 8.4 or newer, this version is
                        <?php echo PHP_VERSION ?>.</td>
                    <?php endif ?>
                </tr>
                <tr>
                    <th>System Directory</th>
                    <?php if (is_dir(SYSPATH) AND is_file(SYSPATH.'classes/Kohana'.EXT)): ?>
                    <td class="pass">
                        <?php echo SYSPATH ?>
                    </td>
                    <?php else: $failed = TRUE ?>
                    <td class="fail">The configured <code>system</code> directory does not exist or does not contain required files.</td>
                    <?php endif ?>
                </tr>
                <tr>
                    <th>Application Directory</th>
                    <?php if (is_dir(APPPATH) AND is_file(APPPATH.'bootstrap'.EXT)): ?>
                    <td class="pass">
                        <?php echo APPPATH ?>
                    </td>
                    <?php else: $failed = TRUE ?>
                    <td class="fail">The configured <code>application</code> directory does not exist or does not contain required files.</td>
                    <?php endif ?>
                </tr>
                <tr>
                    <th>Cache Directory</th>
                    <?php if (is_dir(APPPATH) AND is_dir(APPPATH.'cache') AND is_writable(APPPATH.'cache')): ?>
                    <td class="pass">
                        <?php echo APPPATH.'cache/' ?>
                    </td>
                    <?php else: $failed = TRUE ?>
                    <td class="fail">The <code><?php echo APPPATH.'cache/' ?></code> directory is not writable.</td>
                    <?php endif ?>
                </tr>
                <tr>
                    <th>Logs Directory</th>
                    <?php if (is_dir(APPPATH) AND is_dir(APPPATH.'logs') AND is_writable(APPPATH.'logs')): ?>
                    <td class="pass">
                        <?php echo APPPATH.'logs/' ?>
                    </td>
                    <?php else: $failed = TRUE ?>
                    <td class="fail">The <code><?php echo APPPATH.'logs/' ?></code> directory is not writable.</td>
                    <?php endif ?>
                </tr>
                <tr>
                    <th>PCRE UTF-8</th>
                    <?php if ( ! @preg_match('/^.$/u', 'ñ')): $failed = TRUE ?>
                    <td class="fail"><a href="http://php.net/pcre">PCRE</a> has not been compiled with UTF-8 support.</td>
                    <?php elseif ( ! @preg_match('/^\pL$/u', 'ñ')): $failed = TRUE ?>
                    <td class="fail"><a href="http://php.net/pcre">PCRE</a> has not been compiled with Unicode property support.</td>
                    <?php else: ?>
                    <td class="pass">Pass</td>
                    <?php endif ?>
                </tr>
                <tr>
                    <th>SPL Enabled</th>
                    <?php if (function_exists('spl_autoload_register')): ?>
                    <td class="pass">Pass</td>
                    <?php else: $failed = TRUE ?>
                    <td class="fail">PHP <a href="http://www.php.net/spl">SPL</a> is either not loaded or not compiled in.</td>
                    <?php endif ?>
                </tr>
                <tr>
                    <th>Reflection Enabled</th>
                    <?php if (class_exists('ReflectionClass')): ?>
                    <td class="pass">Pass</td>
                    <?php else: $failed = TRUE ?>
                    <td class="fail">PHP <a href="http://www.php.net/reflection">reflection</a> is either not loaded or not compiled in.</td>
                    <?php endif ?>
                </tr>
                <tr>
                    <th>Filters Enabled</th>
                    <?php if (function_exists('filter_list')): ?>
                    <td class="pass">Pass</td>
                    <?php else: $failed = TRUE ?>
                    <td class="fail">The <a href="http://www.php.net/filter">filter</a> extension is either not loaded or not compiled in.</td>
                    <?php endif ?>
                </tr>
                <tr>
                    <th>Iconv Extension Loaded</th>
                    <?php if (extension_loaded('iconv')): ?>
                    <td class="pass">Pass</td>
                    <?php else: $failed = TRUE ?>
                    <td class="fail">The <a href="http://php.net/iconv">iconv</a> extension is not loaded.</td>
                    <?php endif ?>
                </tr>
                <?php if (extension_loaded('mbstring')): ?>
                <tr>
                    <th>Mbstring Not Overloaded</th>
                    <?php if (ini_get('mbstring.func_overload') ): $failed = TRUE ?>
                    <td class="fail">The <a href="http://php.net/mbstring">mbstring</a> extension is overloading PHP's native string functions.</td>
                    <?php else: ?>
                    <td class="pass">Pass</td>
                    <?php endif ?>
                </tr>
                <?php endif ?>
                <tr>
                    <th>Character Type (CTYPE) Extension</th>
                    <?php if ( ! function_exists('ctype_digit')): $failed = TRUE ?>
                    <td class="fail">The <a href="http://php.net/ctype">ctype</a> extension is not enabled.</td>
                    <?php else: ?>
                    <td class="pass">Pass</td>
                    <?php endif ?>
                </tr>
                <tr>
                    <th>URI Determination</th>
                    <?php if (isset($_SERVER['REQUEST_URI']) OR isset($_SERVER['PHP_SELF']) OR isset($_SERVER['PATH_INFO'])): ?>
                    <td class="pass">Pass</td>
                    <?php else: $failed = TRUE ?>
                    <td class="fail">Neither <code>$_SERVER['REQUEST_URI']</code>, <code>$_SERVER['PHP_SELF']</code>, or <code>$_SERVER['PATH_INFO']</code> is available.</td>
                    <?php endif ?>
                </tr>
            </tbody>
        </table>

        <?php if ($failed === TRUE): ?>
        <div id="results" class="alert danger">
            <span class="icon icon-danger"></span> Konine may not work correctly with your environment.
        </div>
        <?php else: ?>
        <div id="results" class="alert success">
            <span class="icon icon-success"></span> Your environment passed all requirements. Remove or rename the <code>install<?php echo EXT ?></code> file now.
        </div>
        <?php endif ?>

        <h1>Optional Tests</h1>

        <p>
            The following extensions are not required to run the Konine core, but if enabled can provide access to additional classes.
        </p>

        <table>
            <tbody>
                <tr>
                    <th>PECL HTTP Enabled</th>
                    <?php if (extension_loaded('http')): ?>
                    <td class="pass">Pass</td>
                    <?php else: ?>
                    <td class="fail">Konine can use the <a href="http://php.net/http">http</a> extension for the Request_Client_External class.</td>
                    <?php endif ?>
                </tr>
                <tr>
                    <th>cURL Enabled</th>
                    <?php if (extension_loaded('curl')): ?>
                    <td class="pass">Pass</td>
                    <?php else: ?>
                    <td class="fail">Konine can use the <a href="http://php.net/curl">cURL</a> extension for the Request_Client_External class.</td>
                    <?php endif ?>
                </tr>
                <tr>
                    <th>GD Enabled</th>
                    <?php if (function_exists('gd_info')): ?>
                    <td class="pass">Pass</td>
                    <?php else: ?>
                    <td class="fail">Konine requires <a href="http://php.net/gd">GD</a> v2 for the Image class.</td>
                    <?php endif ?>
                </tr>
                <tr>
                    <th>MySQLi Enabled</th>
                    <?php if (function_exists('mysqli_connect')): ?>
                    <td class="pass">Pass</td>
                    <?php else: ?>
                    <td class="fail">Konine can use the <a href="http://php.net/mysqli">MySQLi</a> extension to support MySQL databases.</td>
                    <?php endif ?>
                </tr>
                <tr>
                    <th>PDO Enabled</th>
                    <?php if (class_exists('PDO')): ?>
                    <td class="pass">Pass</td>
                    <?php else: ?>
                    <td class="fail">Konine can use <a href="http://php.net/pdo">PDO</a> to support additional databases.</td>
                    <?php endif ?>
                </tr>
            </tbody>
        </table>

    </div>
</body>

</html>

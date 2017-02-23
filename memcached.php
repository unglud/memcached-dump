<?php
/**
 * @author Aleksandr Matrosov <unglud@gmail.com>
 */

$server = '127.0.0.1';
$port = 11211;

set_error_handler(function ($errno, $errstr) {
    if ($errno !== 2 && $errno !== 8) {
        echo $errstr . '!!!!' . $errno;
    }
}, E_WARNING | E_NOTICE);

$m = new Memcached('persistent');
$m->addServer($server, $port);

if (isset($_GET['del'])) {
    $m->delete($_GET['del']);
}

if (isset($_GET['flush'])) {
    $m->flush();
}

if (isset($_GET['set'])) {
    $m->set($_GET['set'], $_GET['value']);
}

$keys = $m->getAllKeys();
$list = [];
foreach ($keys as $eName) {
    $content = $m->get($eName);
    if ($m->getResultCode() === Memcached::OPT_COMPRESSION) {
        $m->delete($eName);
        continue;
    }

    $list[$eName] = [
        'key'   => $eName,
        'value' => unserialize($content) ? unserialize($content) : $content
    ];
}
restore_error_handler();
ksort($list);

$table = '';
foreach ($list as $i) {
    $table .= '<tr><td width="1%"><a href="memcached.php?del=' . $i['key'] . '">X</a></td>' .
        '<td>' . $i['key'] . '</td><td>';
    if (is_array($i['value'])) {
        $table .= '<pre>' . print_r($i['value'], true) . '</pre>';
    } else {
        $table .= $i['value'];
    }
    $table .= '</td></tr>';
}

?>

<head>
    <link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.2.1/css/bootstrap-combined.min.css" rel="stylesheet">
</head>
<body>

<div class="container">
    <h3>memcached</h3>
    <table cellpadding="0" cellspacing="0" class="table table-bordered table-hover table-striped">
        <thead>
        <tr>
            <th></th>
            <th>key</th>
            <th>value</th>
        </tr>
        </thead>
        <tbody>
        <?php echo $table ?>

        </tbody>
    </table>
    <div style="text-align: center">
        <a href="memcached.php?flush=1">FLUSH</a> <br/>
        <br/>
        <a href="#" onclick="memcachedSet()">SET</a>
    </div>

    <script type="text/javascript">
        function memcachedSet () {
            var key = prompt("Key: ");
            var value = prompt("Value: ");
            window.location.href = "memcached.php?set=" + key + "&value=" + value;
        }
    </script>
</body>

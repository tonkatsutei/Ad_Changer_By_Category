<?php

declare(strict_types=1);
include_once("../../../wp-load.php");


if (isset($_GET['n'])) {
    $n = $_GET['n'];
} else {
    print "failure(0)";
    return;
}
if (isset($_GET['l'])) {
    $l = $_GET['l'];
} else {
    print "failure(1)";
    return;
}

$lisence_id = get_option('Ad_Changer_By_Category_lisense_id');
if ($l !== $lisence_id) {
    print "failure(2)";
    return;
} elseif ($l == '') {
    print "failure(3)";
    return;
}

$data = get_option('Ad_Changer_By_Category_data');

$data = json_decode($data, true);
foreach ($data as $g => $val) {
    if (isset($val[$n])) {
        $group = $g;
        $flug = true;
        break;
    };
    $flug = false;
}

if ($flug) {
    // カウントアップ
    $cnt = (int)$data[$group][$n]['count'];
    $data[$group][$n]['count'] = $cnt + 1;

    // 上書き保存
    $data = json_encode($data, JSON_UNESCAPED_UNICODE);
    update_option('Ad_Changer_By_Category_data', $data);
    print "success($cnt)";
    return;
}

print "failure(4)";
return;

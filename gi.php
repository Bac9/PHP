<?php
// Создатель системы Инкогнито_о 
// Помог в советах и добавил несколько фиксов Alex_Bond за что ему большое спасибо)
// CONFIG

$config = array(
    'mainurl' => 'http://example.com', // Без слеша в конце
    'title' => 'lolcraft.ru =)',
    'bg' => './bg.png', // Путь к файлу-подложке
    'db' => array(
        'host' => 'localhost',
        'port' => 3306,
        'user' => 'root',
        'password' => '',
        'database' => 'minecraft',
        'tables' => array(
            'users' => array(
                'enable' => true, // Ули false то проверка существования пользователя идет по iConomy таблице
                'name' => 'users',
                'field' => 'username'
            ),
            'permissions' => array(
                'name' => 'permissions_inheritance',
                'field' => 'child'
            ),
            'jobs' => array(
                'name' => 'jobs',
                'field' => 'username'
            ),
            'iconomy' => array(
                'name' => 'iconomy',
                'field' => 'username'
            ),
        )
    ),
    'skins' => array(
        'official' => true, // Если true то скины грузятся с оф сервера майнкрафт. При этом следующий параметр игнорируется.
        'path' => './skins', // Без слеша в конце
        'size' => 50 // В пикселях
    ),
    'cache' => array(
        'path' => './cache', // Без слеша в конце
        'lifetime' => 300 // в секундах
    ),
    'iConomy' => array(
        'defaultBalance' => 30
    ),
    'jobs' => array(
        'Builder' => 'Builder',
        'Woodcutter' => 'Woodcutter',
        'Miner' => 'Miner',
        'Digger' => 'Digger',
        'Farmer' => 'Farmer',
        'Hunter' => 'Hunter',
        'Fisherman' => 'Fisherman',
        'Weaponsmith' => 'Weaponsmith',
        'default' => 'unemployed',
    ),
    'groups' => array(
        'admin' => 'Admin',
        'vip' => 'VIP',
        'default' => 'Player'
    ),
    'language' => array(
        'Job' => 'Job',
        'iConomy' => 'iConomy',
        'Group' => 'Group'
    )
);

// DO NOT EDIT UNDER THIS LINE!!!!!!

header('Content-type: image/png');

$username = isset($_GET['u']) ? $_GET['u'] : 'char';

function get_avatar(array &$config, $user = 'char')
{
    $output = false;
    if ($config['skins']['official']) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://s3.amazonaws.com/MinecraftSkins/' . $user . '.png');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $output = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    } else {
        if (file_exists($config['skins']['path'] . '/' . $user . '.png'))
            $output = file_get_contents($config['skins']['path'] . '/' . $user . '.png');
    }
    if (!$output) {
        // Default Avatar: http://www.minecraft.net/skin/char.png
        $output = 'R0lGODlhMAAQAPUuALV7Z6p9ZkUiDkEhDIpMPSgcC2pAMFI9ibSEbZxpTP///7uJciodDTMkEYNVO7eCcpZfQJBeQ5xjRkIdCsaWgL2OdL';
        $output .= '6IbL2OcqJqRyweDj8qFXpOMy8fDyQYCC8gDUIqEiYaCraJbL2Lco9ePoBTNG1DKpxyXK2AbbN7Yqx2WjQlEoFTOW9FLCseDQAAAAAAAAA';
        $output .= 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH/C1hNUCBEYXRhWE1QRD94cDIzRThDRkQwQzcyIiB4';
        $output .= 'bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOkU2RTVBQzAwMDFwYWNrZXQgZW5kPSJyIj8+ACH5BAUAAC4ALAAAAAAwABAAQAZkQJdwSCwaj';
        $output .= '8ik0uVpcQodUIuxrFqv2OwRoTgAFgdFQEsum8/ocit0oYgqKVVaG4EMCATBaDXv+/+AgYKDVS2GDR8aGQWESAEIAScmCwkJjUcSKA8GBh';
        $output .= 'YYJJdGLCUDEwICDhuEQQA7';
        $output = base64_decode($output);
    }
    return $output;
}

if (preg_match("/[0-9a-z_]/i", $username)) {
    if (!file_exists($config['cache']['path'] . '/' . $user . '.png') || time() - filectime($config['cache']['path'] . '/' . $user . '.png') > $config['cache']['lifetime']) {
        mysql_connect($config['db']['host'], $config['db']['user'], $config['db']['password']) OR DIE("Не могу создать соединение с mysql! ");
        mysql_select_db($config['db']['database']) or die(mysql_error());
        /* ЗАЩИТА фэйсконТРОЛЬ АКТИВЕЙТЕД >_<*/
        if ($config['db']['tables']['users']['enable']) {
            $users = mysql_query("SELECT * FROM " . $config['db']['tables']['users']['name'] . " WHERE " . $config['db']['tables']['users']['field'] . " = '$username'") or die(mysql_error());
        } else {
            $users = mysql_query("SELECT * FROM " . $config['db']['tables']['iconomy']['name'] . " WHERE " . $config['db']['tables']['iconomy']['field'] . "='$username'") or die(mysql_error());
        }
        if (mysql_num_rows($users) == 1) {
            // iConomy
            if ($config['db']['tables']['users']['enable']) {
                $array = mysql_fetch_array(mysql_query("SELECT * FROM " . $config['db']['tables']['iconomy']['name'] . " WHERE " . $config['db']['tables']['iconomy']['field'] . "='$username'"));
            } else {
                $array = mysql_fetch_array($users);
            }
            $balance = ($array['balance']) ? $array['balance'] : $config['iConomy']['defaultBalance'];
            // Пермишены
            $array = mysql_fetch_array(mysql_query("SELECT * FROM " . $config['db']['tables']['permissions']['name'] . " WHERE " . $config['db']['tables']['permissions']['field'] . "='$username'"));
            $group = ($array['parent'] && isset($config['groups'][$array['parent']])) ? $config['groups'][$array['parent']] : $config['groups']['default'];
            // Профессии
            $array = mysql_fetch_array(mysql_query("SELECT * FROM " . $config['db']['tables']['jobs']['name'] . " WHERE " . $config['db']['tables']['jobs']['field'] . "='$username'"));
            $job = ($array['job'] && isset($config['jobs'][$array['job']])) ? $config['jobs'][$array['job']] : $config['jobs']['default'];
            // Аватарки
            $skin = get_avatar($config, $username);

            // Генерируем изображение
            $im = imagecreatefromstring($skin);
            $av = imagecreatefrompng($config['bg']);
            $blue = ImageColorAllocate($av, 0, 0, 64);
            ImageFill($av, 0, 0, $blue);
            $white = ImageColorAllocate($av, 255, 255, 255);
            $green = imagecolorallocate($av, 0, 255, 0);
            ImageString($av, 20, 60, 16, $username, $white); //
            ImageString($av, 100, 310, 1, $config['language']['Job'] . ': ' . $job, $white);
            ImageString($av, 100, 310, 32, $config['language']['iConomy'] . ': ' . $balance, $white);
            ImageString($av, 100, 310, 16, $config['language']['Group'] . ': ' . $group, $white);
            ImageString($av, 100, 135, 17, $config['title'], $green);
            imagecopyresized($av, $im, 0, 0, 8, 8, $config['skins']['size'], $config['skins']['size'], 8, 8); // Face
            imagecopyresized($av, $im, 0, 0, 40, 8, $config['skins']['size'], $config['skins']['size'], 8, 8); // Accessories
            $img1 = $config['cache']['path'] . '/' . $username . ".png";
            ImageJpeg($av, $img1, 100);
            echo imagepng($av);
            imagedestroy($im);
            imagedestroy($av);
        } else {
            echo "Вас нету в списке:C фэйсконТРОЛЬ !";
        }
    } else {
        echo file_get_contents($config['cache']['path'] . '/' . $user . '.png');
    }
} else {
    echo "Логин введен не верно!";
}

?>
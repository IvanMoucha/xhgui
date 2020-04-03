<?php
if (!defined('XHGUI_ROOT_DIR')) {
    require dirname(__DIR__) . '/src/bootstrap.php';
}

$options = getopt('f:');

if (!isset($options['f'])) {
    throw new InvalidArgumentException('You should define a file to be loaded');
} else {
    $file = $options['f'];
}

if (!is_readable($file)) {
    throw new InvalidArgumentException($file.' isn\'t readable');
}

$container = Xhgui_ServiceContainer::instance();
$saver = $container['saver.mongo'];

$file_get_contents = file_get_contents($file, true);
$data['profile'] = unserialize($file_get_contents);

/**
 * Encodes a profile to avoid mongodb key errors.
 * @param array $profile
 *
 * @return array
 */
function encodeProfile($profile)
{
    if (!is_array($profile)) {
        return $profile;
    }
    $target = array(
        '__encoded' => true,
    );
    foreach ($profile as $k => $v) {
        if (is_array($v)) {
            $v = encodeProfile($v);
        }
        $replacementKey = strtr($k, array(
            '.' => '．',
        ));
        $target[$replacementKey] = $v;
    }
    return $target;
}

$saver->save(encodeProfile($data));

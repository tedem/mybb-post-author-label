<?php

/**
 * POST AUTHOR LABEL
 *
 * @author      tedem <tedemdev@gmail.com>
 * @copyright   2020 [@author]
 */

if (! defined('IN_MYBB')) {
    die('(-_*) This file cannot be accessed directly.');
}

if (! defined('THIS_SCRIPT')) {
    define('THIS_SCRIPT', null);
}

// constants
define('POST_AUTHOR_LABEL_ID', 'post_author_label');
define('POST_AUTHOR_LABEL_NAME', 'Post Author Label');
define('POST_AUTHOR_LABEL_VERSION', '1.0.0');

define('POST_AUTHOR_LABEL_ROOT', str_replace('\\', '/', MYBB_ROOT));
define('POST_AUTHOR_LABEL_PLUG_ROOT', POST_AUTHOR_LABEL_ROOT . 'inc/plugins/');
define(
    'POST_AUTHOR_LABEL_INC_ROOT',
    POST_AUTHOR_LABEL_PLUG_ROOT . POST_AUTHOR_LABEL_ID . '/');

// templates
global $templatelist;

if (isset($templatelist)) {
    if (substr($templatelist, -1) != ',') {
        $templatelist .= ',';
    }
}

if (THIS_SCRIPT == 'showthread.php') {
    $templatelist .= 'post_author_label';
}

// hooks
if (!defined('IN_ADMINCP')) {
    if (THIS_SCRIPT == 'showthread.php') {
        $plugins->add_hook('postbit', 'post_author_label_run');
    }
}

function post_author_label_info()
{
    global $lang;

    $lang->load('post_author_label');

    return [
        'name'          => $lang->post_author_label_name,
        'description'   => $lang->post_author_label_desc,
        'website'       => 'https://mybbcode.com/',
        'author'        => 'tedem',
        'authorsite'    => 'https://wa.me/905300641197',
        'version'       => POST_AUTHOR_LABEL_VERSION,
        'codename'      => 'md_' . POST_AUTHOR_LABEL_ID,
        'compatibility' => '18*'
    ];
}

function post_author_label_install()
{
    global $db, $cache;

    $info = post_author_label_info();

    // add cache
    $md = $cache->read('md');

    $md[$info['codename']] = [
        'name'      => $info['name'],
        'author'    => $info['author'],
        'version'   => $info['version'],
    ];

    $cache->update('md', $md);

    // add templates
    $postAuthorLabel = post_author_label_load_template('post_author_label');

    $db->insert_query('templates', [
        'title'     => 'post_author_label',
        'template'  => $db->escape_string($postAuthorLabel),
        'sid'       => '-1',
        'version'   => '',
        'dateline'  => time(),
    ]);
}

function post_author_label_is_installed()
{
    global $cache;

    $info = post_author_label_info();

    $md = $cache->read('md');

    if ($md[$info['codename']]) {
        return true;
    }
}

function post_author_label_uninstall()
{
    global $db, $cache;

    $info = post_author_label_info();

    // remove cache
    $md = $cache->read('md');

    unset($md[$info['codename']]);

    $cache->update('md', $md);

    if (count($md) == 0) {
        $db->delete_query('datacache', "title='md'");
    }

    // remove templates
    $db->delete_query('templates', "title='post_author_label'");
}

function post_author_label_activate()
{
    post_author_label_replace_template(
        'postbit',
        '{$post[\'groupimage\']}',
        '{$post[\'groupimage\']}{$post[\'post_author_label\']}'
    );

    post_author_label_replace_template(
        'postbit_classic',
        '{$post[\'groupimage\']}',
        '{$post[\'groupimage\']}{$post[\'post_author_label\']}'
    );
}

function post_author_label_deactivate()
{
    post_author_label_replace_template(
        'postbit', '{$post[\'post_author_label\']}');

    post_author_label_replace_template(
        'postbit_classic', '{$post[\'post_author_label\']}');
}

function post_author_label_run(&$post)
{
    global $templates, $lang, $thread, $postcounter;

    $lang->load('post_author_label');

    // no error please
    if (empty($post)) {
        $post = [];
    }

    $post['post_author_label'] = null;

    if ($post['uid'] == $thread['uid'] && $postcounter > 1) {
        $post['post_author_label'] = eval(
            $templates->render('post_author_label'));
    }
}

function post_author_label_load_template(string $filename, string $ex = 'tpl')
{
    $filename = trim(strtolower($filename));

    $file   = post_author_label_plugin_directory('templates')
            . $filename
            . '.'
            . $ex;

    if (file_exists($file)) {
        return file_get_contents($file);
    }

    return null;
}

function post_author_label_plugin_directory(?string $path = null)
{
    if (! is_null($path)) {
        $path = trim(strtolower($path), '/');

        return POST_AUTHOR_LABEL_INC_ROOT . $path . '/';
    }

    return POST_AUTHOR_LABEL_INC_ROOT;
}

function post_author_label_replace_template(
    string $title,
    string $find,
    ?string $replace = null
) {
    require_once POST_AUTHOR_LABEL_ROOT . 'inc/adminfunctions_templates.php';

    return find_replace_templatesets(
        $title, '#' . preg_quote($find) . '#', $replace);
}

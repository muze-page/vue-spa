<?php
/*
Plugin Name: Vue - SPA 
Plugin URI: https://www.npc.ink/277241.html
Description: 将vue构建的页面嵌入WordPress 中并产生交互
Author: Muze
Author URI: https://www.npc.ink
Version: 1.0.0
*/


//接口
require_once plugin_dir_path(__FILE__) . 'interface.php';

//创建一个菜单
function vuespa_create_menu_page()
{
    add_menu_page(
        'VueSpa选项',                   // 此菜单对应页面上显示的标题
        'VueSpa',                      // 要为此实际菜单项显示的文本
        'administrator',               // 哪种类型的用户可以看到此菜单
        'vuespa_id',                   //  此菜单项的唯一ID（即段塞）
        'vuespa_menu_page_display',    // 呈现此页面的菜单时要调用的函数的名称 'vuespa_menu_page_display'
        'dashicons-admin-customizer',  //图标 - 默认图标
        '500.1',                       //位置
    );
} // end vuespa_create_menu_page 
add_action('admin_menu', 'vuespa_create_menu_page');

//菜单回调 - 展示的内容
function vuespa_menu_page_display()
{
?>

    <!--在默认WordPress“包装”容器中创建标题-->
    <div class="wrap">
        <!--标题-->
        <h2><?php echo esc_html(get_admin_page_title()); ?></h2>
        <!--提供Vue挂载点-->
        <div id="vuespa">此内容将在挂载Vue后被替换{{data}}</div>
    </div>



<?php

    //展示准备的数据
    echo "<pre>";
    print_r(vuespa_data());
    echo "</pre>";

    echo "<h3>调用选项值</h3>";
    echo get_option('dataOne');
    echo "<br/>";
    echo get_option('dataTwo');
    echo "<br/>";
    print_r(get_option('dataName'));
    echo "<br/>";
    echo get_option('dataImage');
    echo "<br/>";
    echo get_option('dataSelectedImage');
} // vuespa_menu_page_display



//载入所需 JS 和 CSS 资源 并传递数据
function vuespa_load_vues($hook)
{
    //判断当前页面是否是指定页面，是则继续加载
    if ('toplevel_page_vuespa_id' != $hook) {
        return;
    }
    //版本号
    $ver = '53';
    //加载到页面顶部
    wp_enqueue_style('vite', plugin_dir_url(__FILE__) . 'vites/dist/index.css', array(), $ver, false);
    //加载到页面底部
    wp_enqueue_script('vite', plugin_dir_url(__FILE__) . 'vites/dist/index.js', array(), $ver, true);

    $pf_api_translation_array = array(
        'route' => esc_url_raw(rest_url()),     //路由
        'nonce' => wp_create_nonce('wp_rest'), //验证标记
        'data' => vuespa_data(),               //自定义数据
    );
    wp_localize_script('vite', 'dataLocal', $pf_api_translation_array); //传给vite项目
}
//样式加载到后台
add_action('admin_enqueue_scripts', 'vuespa_load_vues');


//准备待传输的数据
function vuespa_data()
{
    $person = [
        "str" => "Hello, world! - Npcink",
        "num" => 25,
        "city" => [1, 2, 3, 4, 5],
        "user" => vuespa_get_user_meat(),
    ];
    return $person;
}


//整理并提供用户信息
function vuespa_get_user_meat()
{
    //获取所有角色
    $editable_roles = wp_roles()->roles;
    $roles = array_keys($editable_roles);
    //获取除了'subscriber'(订阅者)角色之外的所有角色的用户数据
    $subscriber_key = array_search('subscriber', $roles, true);
    if (false !== $subscriber_key) {
        $roles = array_slice($roles, 0, $subscriber_key);
    }

    $users = get_users(array('role__in' => $roles));

    //转为关联数组
    $user_data = array_map(function ($user) {
        return [
            'id'   => $user->ID,
            'name' => $user->display_name,
        ];
    }, $users);

    return $user_data;
}

//模块导入
function add_type_attribute_to_script_vue($tag, $handle)
{
    // 在这里判断需要添加 type 属性的 JS 文件，比如文件名包含 xxx.js
    if (strpos($tag, 'index.js') !== false) {
        // 在 script 标签中添加 type 属性
        $tag = str_replace('<script', '<script type="module"', $tag);
    }
    return $tag;
}
add_filter('script_loader_tag', 'add_type_attribute_to_script_vue', 10, 2);


//设置按钮
add_filter('plugin_action_links_' . plugin_basename(__FILE__), function ($links) {
    $links[] = '<a href="' . get_admin_url(null, '?page=vuespa_id') . '">' . __('设置', 'n') . '</a>';
    return $links;
});

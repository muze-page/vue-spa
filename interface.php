<?php
//interface.php
//接口文件
function vuespa_create_api()
{
    register_rest_route('pf/v1', '/get_option/', array( // 完整命名空间为：/wp-json/pf/v1/
        'methods' => 'POST',
        'callback' => 'get_option_by_RestAPI',
    ));
    register_rest_route('pf/v1', '/update_option/', array( // 完整命名空间为：/wp-json/pf/v1/
        'methods' => 'POST',
        'callback' => 'update_option_by_RestAPI',
        'permission_callback' => function () {
            return current_user_can('manage_options'); // 只有管理员才有权限修改
        },
    ));
}
add_action('rest_api_init', 'vuespa_create_api');


//读取Option
//仅支持一对一的数据请求
function get_option_by_RestAPI($data)
{
    //将传递数据转成数组类型
    $dataArray = json_decode($data->get_body(), true);
    //新建数组
    $return = array();
    //循环获取对应选项ID的值，并将其存储在对应关联数组中，若拿不到值，则为空
    foreach ($dataArray as $option_name => $value) {
        $return[$option_name] = get_option($option_name) ? get_option($option_name) : "";
    }
    return $return;
}


//保存Option
//一对一保存
function update_option_by_RestAPI($data)
{
    //判断是否是管理员
    if (current_user_can('manage_options')) {
        //将传递数据转成数组类型
        $dataArray = json_decode($data->get_body(), true);
        //循环保存选项
        foreach ($dataArray as $option_name => $value) {
            update_option($option_name, $value);
        }
        //返回成功信息
        return new WP_REST_Response(array(
            'success' => true,
            'message' => "已保存！"
        ), 200);
    } else {
        //返回失败信息
        return new WP_Error('save_error', '保存失败！', array('status' => 500));
    }
}

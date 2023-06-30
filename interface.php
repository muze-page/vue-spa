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
//支持数组类数据请求
function get_option_by_RestAPI($data)
{
    // 将输入数据转换成数组类型 
    $dataArray = json_decode($data->get_body(), true);
    $return = array();
    // 遍历数组，检查每个元素是否为对象
    foreach ($dataArray as $option_name => $value) {
        // 初始化当前选项的值数组
        $option_value = array();
        // 如果当前元素是一个非空数组，则遍历其中的每个字段
        if (is_array($value) && !empty($value)) {
            foreach ($value as $field_name => $field_value) {
                // 获取指定选项的值，如果不存在，则使用空字符串代替
                $option_value[$field_name] = get_option($field_name, '');
            }
            // 将当前选项及其值添加到返回数组中
            $return[$option_name] = $option_value;
        } else {
            // 如果当前元素非数组或数组为空，获取指定选项的值
            $return[$option_name] = get_option($option_name, '');
        }
    }
    return $return; // 返回所有选项的键值对
}


//保存Option
function update_option_by_RestAPI($data)
{

    //判断是否是管理员
    if (current_user_can('manage_options')) {
        //转为JSON对象 - 重点，这里没有true，是转为对象
        $dataArray = json_decode($data->get_body());
        //存储结果
        $result = new stdClass();

        //循环保存选项
        foreach ($dataArray as $option_name => $value) {

            //判断，是否为对象
            if (is_object($value)) {
                //是非空数组，循环保存值
                foreach ($value as $arr => $data) {
                    //更新值    
                    update_option($arr, $data);
                }
            } else {
                //不是对象，则表示只有一个选项需要保存。
                update_option($option_name, $value);
            }
            $result->$option_name = $value;
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

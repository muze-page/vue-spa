<script setup>
import { reactive, onMounted, computed } from "vue";
import axios from "axios";

//const dataLocal = {
//  route: "http://localhost:5173/wp-json/",
//  nonce: "asdf",
//  data: {
//    user: [
//      { id: 1, name: "111" },
//      { id: 2, name: "222" },
//    ],
//  },
//};

const siteData = dataLocal.data;

//存储获取的值
const getData = reactive({
  //存储获取的媒体库值
  mediaList: [],
});

//存储选项值
const datas = reactive({
  dataOne: "",
  dataTwo: "",
  dataName: [],
  dataImage: "",
  dataSelectedImage: "",
  check: {
    name: "",
    phone: "",
  },
});

//获取数据
const get_option = () => {
  axios
    .post(dataLocal.route + "pf/v1/get_option", datas, {
      headers: {
        "X-WP-Nonce": dataLocal.nonce,
        "Content-Type": "application/json",
      },
    })
    .then((response) => {
      const data = response.data;
      datas.dataOne = data.dataOne;
      datas.dataTwo = data.dataTwo;
      datas.dataName = data.dataName;
      datas.dataImage = data.dataImage;
      datas.dataSelectedImage = data.dataSelectedImage;
      datas.check.name = data.check.name;
      datas.check.phone = data.check.phone;
    })
    .catch((error) => {
      window.alert("连接服务器失败或后台读取出错！数据读取失败");
      console.log(error);
    });
};

//保存数据
const update_option = () => {
  console.log(datas);
  axios
    .post(dataLocal.route + "pf/v1/update_option", datas, {
      headers: {
        "X-WP-Nonce": dataLocal.nonce,
      },
    })
    .then((response) => {
      alert("保存成功");
    })
    .catch((error) => {
      alert("保存失败");
      console.log(error);
    });
};

//上传图片
const upload_img = (file) => {
  const formData = new FormData();
  formData.append("file", file);
  return axios
    .post(dataLocal.route + "wp/v2/media", formData, {
      headers: {
        "X-WP-Nonce": dataLocal.nonce,
        "Content-Type": "multipart/form-data",
      },
    })
    .then((response) => {
      // 图片上传成功后的处理逻辑
      const data = response.data;
      //返回图片URL
      return data.source_url;
    })
    .catch((error) => {
      console.error(error);
      // 图片上传失败后的处理逻辑
    });
};

//处理图片上传事件
const update_img = (event) => {
  const file = event.target.files[0];
  upload_img(file).then((url) => {
    //将拿到的图片URL传给图片变量
    datas.dataImage = url;
  });
};

//清空选择图片
const clear_img = () => {
  datas.dataImage = "";
};

//获取媒体库图片
const getMediaList = () => {
  axios
    .get(dataLocal.route + "wp/v2/media")
    .then((response) => {
      getData.mediaList = response.data;
    })
    .catch((error) => {
      console.error(error);
    });
};

//从媒体库选中图片
const selectImage = (imageUrl) => {
  datas.dataSelectedImage = imageUrl;
};

//验证名称
const isName = computed(() => {
  // 正则表达式验证名字，2到6个中文字符
  const reg = /^[\u4e00-\u9fa5]{2,6}$/;
  return reg.test(datas.check.name);
});
//验证电话号码
const isPhone = computed(() => {
  // 正则表达式验证电话号码
  const reg = /^1[3456789]\d{9}$/;
  return reg.test(datas.check.phone);
});

//页面初始加载
onMounted(() => {
  //获取选项值
  get_option();
});
</script>

<template>
  <!--两个输入框-->
  文本框1：<input type="text" v-model="datas.dataOne" /><br />
  文本框2：<input type="text" v-model="datas.dataTwo" />
  <hr />
  <!--用户选择-->
  用户选择：<select v-model="datas.dataName" multiple>
    <option v-for="option in siteData.user" :key="option.id" :value="option.id">
      {{ option.name }}
    </option>
  </select>
  <p>你选择了：{{ datas.dataName }}</p>
  <br />
  按住command(control)按键即可进行多选
  <hr />
  <!--图片上传-->
  <input type="file" @change.native="update_img" /><br />
  <button type="button" @click="clear_img">清理</button><br />
  <img :src="datas.dataImage" v-if="datas.dataImage" />
  <hr />
  <!--获取媒体库图片-->
  <button @click="getMediaList">获取媒体库图片</button>
  <div class="box">
    <div v-for="media in getData.mediaList" :key="media.id" style="float: left">
      <img :src="media.source_url" />
      <button @click="selectImage(media.source_url)">选择</button>
    </div>
  </div>
  <h2>{{ datas.dataSelectedImage ? "已" : "未" }}选择图片</h2>
  <img :src="datas.dataSelectedImage" v-if="datas.dataSelectedImage" />
  <hr />

  <h3>数据校验</h3>
  姓名：<input type="text" v-model="datas.check.name" />
  <p v-if="!isName" class="check">格式错误 - 必须为两字到六字中文</p>
  <br />
  手机号：<input type="text" v-model="datas.check.phone" />
  <p v-if="!isPhone" class="check">格式错误 - 必须是1开头的11位手机号</p>
  <hr />

  <button class="button button-primary" @click="update_option">保存</button>
</template>

<style scoped>
input {
  display: block;
}
img {
  max-width: 150px;
  height: auto;
  vertical-align: top;
}
.box {
  max-width: 800px;
  display: flex;
  margin: 1em 0;
}

.check {
  font-size: 12px;
  color: #ff3c3c;
  margin: 0px 1em;
}
</style>
